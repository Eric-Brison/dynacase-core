<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: user_access.php,v 1.8 2004/10/11 15:40:27 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage ACCESS
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: user_access.php,v 1.8 2004/10/11 15:40:27 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Access/user_access.php,v $
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

include_once("Class.QueryDb.php");
include_once("Class.QueryGen.php");
include_once("Class.SubForm.php");
include_once("Class.TableLayout.php");

// -----------------------------------
function user_access(&$action, $group=false) {
// -----------------------------------

  $baseurl=$action->GetParam("CORE_BASEURL");
  $standurl=$action->GetParam("CORE_STANDURL");

  
  // Set the edit form element
  if ($group) {
    $paramedit="&group=yes";
  } else {
    $paramedit="&group=no";
  }
  $form = new SubForm("edit",500,330,"app=ACCESS&action=MODIFY$paramedit",
                                     $standurl."app=ACCESS&action=EDIT&mod=user$paramedit");
  
  $form->SetKey("id");

  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
  $action->parent->AddJsCode($form->GetMainJs());
  $jsscript=$form-> GetLinkJsMainCall();

  // Set 
  $action->lay->set("ACTION_CHG","ACCESS_USER_CHG$paramedit");
  $action->lay->set("ACTION_MOD","USER_ACCESS_MOD$paramedit");
  $action->lay->set("fhelp",($action->Read("navigator","")=="EXPLORER")?"_blank":"fhidden");

  $action->lay->set("shortname",_($action->text("appname")));
  $action->lay->set("desc",_($action->text("appdesc")));
  $action->lay->set("permission",$action->text("permissions"));

  $action->lay->set("QUERY_FORM","");
  $action->lay->set("FULLTEXTFORM","");

  // affect the select form elements
  $u = new User();
  if ($group) {
    $list = $u-> GetGroupList();
    $varreg = "access_group_id";
    $action->lay->set("imgaccess",$action->GetIcon("access2.gif", "modify",20));
  } else {
    $list = $u-> GetUserList();
    $varreg = "access_user_id";
    $action->lay->set("imgaccess",$action->GetIcon("access.gif", "modify",18));
  }

  // select the first user if not set
  $user_id=$action->Read($varreg);
  $action->log->debug("user_id : $user_id");
  if ($user_id == "") $user_id=0; 

  $tab = array();
  reset($list);
  $user_sel=$list[0];
  while (list($k,$v) = each($list)) {
    if ($v->id == 1) continue;
    if ($user_id == 0)  {
       $user_id = $v->id;
       $action->Register($varreg,$user_id);  
    }
    if (($v->lastname == "") && ($v->firstname == "")) {
      $tab[$k]["text"]=$v->login;
    } else {
      $tab[$k]["text"]=$v->lastname." ".$v->firstname;
    }
    $tab[$k]["id"]=$v->id;
    if ($user_id == $v->id) {
      $user_sel=$v;
      $tab[$k]["selected"]="selected";
    } else {
      $tab[$k]["selected"]="";
    }
  }
  $action->lay->SetBlockData("SELUSER",$tab);
  $action->parent->AddJsRef("change_acl.js");




  // 1) Get all application
  $query = new QueryGen($action->dbaccess,"Application",$action);
  $query-> AddQuery("access_free = 'N'");
  $query-> AddQuery("(objectclass != 'Y' ) OR ( objectclass isnull)");
  $query->table->headsortfields = array ("shortname" =>"name",
                                         "desc"=>"description");
 
  $query->table->headcontent = array (
                              "shortname" =>$action->text("appname"),
                              "desc" =>$action->text("appdesc"),
                              "permission" => $action->text("permissions"));

  $query->table->fields= array("id","name","selname","description","edit");
  $query->slice=20;

  $query->Query();

  // 2) Get all acl for all application
  reset($query->table->array);



  while (list ($k,$v) = each($query->table->array)) {
    
    
    if (!isset($v["id"])) continue;
    // test if application is controled
    $acl=new Acl($action->dbaccess);
    if (! $acl->getAclApplication($v["id"])) continue;


    // get user permissions
    $uperm = new Permission($action->dbaccess,array($user_sel->id, $v["id"]));
    
    
    $name = $v["name"];

    $tab=array();
    $aclids = $uperm->privileges;
    if (! $aclids) { // no privilege
      $aclids=array(0);

    }

    while(list($k2,$v2)=each($aclids)) {
      $tab[$k2]["aclid"]=$v2;

      if ($v2 == 0) {
	$tab[$k2]["aclname"]=$action->text("none");
      } else {
	$acl=new Acl($action->dbaccess,  $v2);
	$tab[$k2]["aclname"]=$acl->name;
      }
    }
    $action->lay->SetBlockData($v["id"],$tab);
    
    unset($tab);
    unset($acls);
    $query->table->array[$k]["name"]=$v["name"];
    $query->table->array[$k]["selname"]=$v["name"];
    $query->table->array[$k]["description"]=_($v["description"]);
    $query->table->array[$k]["id"]=$v["id"];

    $query->table->array[$k]["edit"] = str_replace("[id]",$v["id"],$jsscript);

  }

  
  $query->table->Set();

       

}
?>
