<?php
// ---------------------------------------------------------------
// $Id: user_table.php,v 1.3 2003/04/14 18:47:10 marc Exp $
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
// $Log: user_table.php,v $
// Revision 1.3  2003/04/14 18:47:10  marc
// Groupe : suppression du prénom
//
// Revision 1.2  2002/07/29 11:15:18  marc
// Release 0.1.1, see ChangeLog
//
// Revision 1.1  2002/01/08 12:41:34  eric
// first
//
// Revision 1.18  2001/08/29 14:01:04  eric
// choix du groupe du domaine
//
// Revision 1.17  2001/08/28 10:14:07  eric
// modification pour la prise en comptes des groupes d'utilisateurs
//
// Revision 1.16  2001/03/21 20:12:59  marc
// Bug... 1 replacé sans testé
//
// Revision 1.15  2001/02/26 12:28:22  marc
// Filtre Postmaster pour ne pas proposer sa suppression
//
// Revision 1.14  2001/02/07 16:40:41  yannick
// Gestion des header et tris
//
// Revision 1.13  2001/02/06 16:34:29  yannick
// Add a QueryGen call
//
// Revision 1.12  2000/10/26 16:25:06  yannick
// dimmensions de la fenêtre d'édition
//
// Revision 1.11  2000/10/26 15:18:18  yannick
// Gestion des erreurs
//
// Revision 1.10  2000/10/26 10:41:03  yannick
// Edition par l'utilisateur
//
// Revision 1.9  2000/10/26 08:09:11  yannick
// Utilisateurs classés par login
//
// Revision 1.8  2000/10/26 07:54:27  yannick
// Gestion du domaine sur les utilisateur
//
// Revision 1.7  2000/10/25 12:46:26  yannick
// Plus de debug
//
// Revision 1.6  2000/10/24 21:11:05  marc
// Bug : les comptes mails supprimes apparaissaient toutjours
//
// Revision 1.5  2000/10/24 08:09:26  yannick
// Gestion du tourne page
//
// Revision 1.4  2000/10/23 10:40:07  marc
// Stable release with mail account creation
//
// Revision 1.3  2000/10/22 16:27:04  marc
// Connexion avec la messagerie
//
// Revision 1.2  2000/10/19 16:47:23  marc
// Evo TableLayout
//
// Revision 1.1.1.1  2000/10/19 10:35:49  yannick
// Import initial
//
//
//
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
    
  $form = new SubForm("edit",350,330,$standurl."app=USERS&action=USER_MOD$paramedit",
                                     $standurl."app=USERS&action=USER_EDIT$paramedit");
  $form->SetParam("id","-1");
  $form->SetParam("firstname","");
  $form->SetParam("lastname","");
  $form->SetParam("login","");
  $form->SetParam("passwd","");
  $form->SetParam("domainid");
  $form->SetParam("groupselect[]");

  $form->SetKey("id");

  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
  $action->parent->AddJsCode($form->GetMainJs());
  $action->lay->set("MAINFORM",$form->GetMainForm());

  if ($action->HasPermission("DOMAIN_MASTER")) {
    $add_icon = new Layout($action->GetLayoutFile("add_icon.xml" ),$action);
    $add_icon->set("group",$isgroup);
    $add_icon->set("title",$title);
    $add_icon->set("paction",$paction);
    $action->lay->set("ADD_ICON",$add_icon->gen());
  } else {
    $action->lay->set("ADD_ICON","");
  }


  // Set the search elements
  $query = new QueryGen($action->GetParam("CORE_USERDB"),"User",$action);
   
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
  $query->table->fields= array("domain", "id","edit","lastname","delete","fullname","login", "papp", "paction", "group");

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
  $jsscript=$form->GetLinkJsMainCall();
  reset ($query->table->array);
  while(list($k,$v) = each($query->table->array)) {
    $query->table->array[$k]["papp"] = "USERS";
    $query->table->array[$k]["paction"] = $paction;
    $query->table->array[$k]["group"] = $isgroup;

    if (!$group) {
      $query->table->array[$k]["fullname"] = 
        ucfirst((isset($query->table->array[$k]["firstname"])?$query->table->array[$k]["firstname"]:"(?)"))." "
        .ucfirst((isset($query->table->array[$k]["lastname"])?$query->table->array[$k]["lastname"]:"(?)"));
    } else {
      $query->table->array[$k]["fullname"] = ucfirst(isset($query->table->array[$k]["lastname"])?$query->table->array[$k]["lastname"]:"(?)");
    }
    $query->table->array[$k]["edit"] = str_replace("[id]",$v["id"],$jsscript);
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
