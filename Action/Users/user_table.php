<?php
// ---------------------------------------------------------------
// $Id: user_table.php,v 1.5 2003/08/11 15:41:37 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Users/user_table.php,v $
// ---------------------------------------------------------------
//  O   Anakeen - 2000
// O*O  Anakeen development team
//  O   dev@anakeen.com
// ---------------------------------------------------------------
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or (at
//  your option) any later version.
//
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
// or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License
// for more details.
//
// You should have received a copy of the GNU General Public License along
// with this program; if not, write to the Free Software Foundation, Inc.,
// 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
// ---------------------------------------------------------------

include_once("Class.TableLayout.php");
include_once("Class.QueryDb.php");
include_once("Class.SubForm.php");
include_once("Class.MailAccount.php");
include_once("Class.Domain.php");
include_once("Class.SubForm.php");
include_once("Class.QueryGen.php");

// -----------------------------------
function user_table(&$action, $group=false) {
// -----------------------------------

  // Set the globals elements

  $baseurl=$action->GetParam("CORE_BASEURL");
  $standurl=$action->GetParam("CORE_STANDURL");

  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");

  // Set the edit form element
  if ($group) {
    $paramedit="&group=yes";
    $paction = "GROUP_TABLE";
    $isgroup = "yes";
    $title = $action->text("titlecreateg");
  } else {
    $paramedit="&group=no";
    $paction = "USER_TABLE";
    $isgroup = "no";
    $title = $action->text("titlecreateu");
  }
    
  $action->lay->set("createuser",$title);

//   $form = new SubForm("edit",350,330,$standurl."app=USERS&action=USER_MOD$paramedit",
//                                      $standurl."app=USERS&action=USER_EDIT$paramedit");
//   $form->SetParam("id","-1");
//   $form->SetParam("firstname","");
//   $form->SetParam("lastname","");
//   $form->SetParam("login","");
//   $form->SetParam("passwd","");
//   $form->SetParam("domainid");
//   $form->SetParam("groupselect[]");

//   $form->SetKey("id");

//   $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
//   $action->parent->AddJsCode($form->GetMainJs());
//   $action->lay->set("MAINFORM",$form->GetMainForm());



  // Set the search elements
  $query = new QueryGen($action->GetParam("CORE_USERDB"),"User",$action);
  $query->slice=20;
  $action->lay->set("slice9",$query->slice+9);
  if ($group) {
    $query-> AddQuery("isgroup = 'Y'");
    $action->lay->set("title",$action->text("titlegroup"));
  } else {
    $query-> AddQuery("(isgroup != 'Y') OR (isgroup isnull)");
    $action->lay->set("title",$action->text("titleuser"));
  }

  // The content depends on Access Permission
  if (!isset ($action->user)) {
    $action->log->info("Attempt to use {$action->parent->name}/{$action->name} without permission");
    Redirect($action,"CORE","");
  }
  if (!$action->HasPermission("ADMIN")) {
    if ($action->HasPermission("DOMAIN_MASTER")) {
      $query->AddQuery("iddomain={$action->user->iddomain}");
    } elseif ($action->HasPermission("USER")) {
      $query->AddQuery("id={$action->user->id}");
    }
  }

  // Give some global elements for the table layout
  $query->table->fields= array("domain", "id","edit","lastname","delete","fullname","login",  "group");

  $query->table->headsortfields = array ( "head_lastname" => "lastname",
                                         "head_login" => "login");
  if ($group) {
    $query->table->headcontent = array (
                                    "head_lastname" => $action->text("groupdesc"),
                                    "head_domain" => $action->text("domain"),
                                    "head_login" => $action->text("group"));
  } else {
      $query->table->headcontent = array (
                                    "head_lastname" => $action->text("fullname"),
                                    "head_domain" => $action->text("domain"),
                                    "head_login" => $action->text("login"));
  }


  // Perform the query
  $query->Query();


  // Affect the modif icons and the fullname field
  //  $jsscript=$form->GetLinkJsMainCall();
  reset ($query->table->array);
  while(list($k,$v) = each($query->table->array)) {
    $query->table->array[$k]["group"] = $isgroup;

    if (!$group) {
      $query->table->array[$k]["fullname"] = 
        ucfirst((isset($query->table->array[$k]["firstname"])?$query->table->array[$k]["firstname"]:"(?)"))." "
        .ucfirst((isset($query->table->array[$k]["lastname"])?$query->table->array[$k]["lastname"]:"(?)"));
    } else {
      $query->table->array[$k]["fullname"] = ucfirst(isset($query->table->array[$k]["lastname"])?$query->table->array[$k]["lastname"]:"(?)");
    }
    // $query->table->array[$k]["edit"] = str_replace("[id]",$v["id"],$jsscript);
    if (($query->table->array[$k]["id"] != 1) &&
        ($query->table->array[$k]["lastname"] != "Postmaster") &&
        ($query->table->array[$k]["login"] != "all") &&
        ($action->HasPermission("DOMAIN_MASTER"))) {
      $query->table->array[$k]["delete"] = $action->GetIcon("delete.gif","delete");
    }
    if ($v["iddomain"] == 1) {
      $query->table->array[$k]["domain"] = $action->text("nomail");
    } else { 
      $dom = new Domain($action->GetParam("CORE_USERDB"), $v["iddomain"]);
      $query->table->array[$k]["domain"] = $dom->name;
    }
  }
    

  // Out
  $action->lay->Set("TABLE", $query->table->Set());


}
?>
