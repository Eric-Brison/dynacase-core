<?php
// ---------------------------------------------------------------
// $Id: user_uedit.php,v 1.1 2003/08/12 12:17:05 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Users/user_uedit.php,v $
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

include_once("Class.SubForm.php");
include_once("Class.Domain.php");
include_once("Class.MailAccount.php");
include_once("Class.User.php");

// -----------------------------------
function user_uedit(&$action) {
  // -----------------------------------


  // Get all the params   
  $id=$action->user->id; // himself


  if ($id == 0) $action->exitError(_("the user identification is unknow"));

  $papp = GetHttpVars("papp","APPMNG");
  $paction = GetHttpVars("paction","PARAM_CUACCOUNT");
  $pargs = GetHttpVars("pargs","");

  // initialise if user group or single user
  $group = (GetHttpVars("group") == "yes");

  $tpasswd =array();
  $tpasswd[0]["passwd"]="";   

 

  $tfirstname =array();
  $tfirstname[0]["firstname"]="";
  
  $user = $action->user;
   
    
  $action->lay->Set("firstname", $user->firstname);    
  $action->lay->Set("lastname",$user->lastname);
  $action->lay->Set("login",$user->login);
  $action->lay->Set("expdate",$user->expires>0?strftime("%d/%m/%Y %X",intval($user->expires)):"no date limit");

    
  $dom = new Domain($action->GetParam("CORE_USERDB"),$user->iddomain);
  $action->lay->Set("domain",$dom->name);
    

  $ug = new Group($action->GetParam("CORE_USERDB"),$user->id);
  $ugroup = $ug->groups;  // direct group 
  

  $tab = array();




  // search user group
  $tab = array();

  // 
  while (list($k, $v) = each($ugroup)) {
    $gu = new User($action->GetParam("CORE_USERDB"), $v);
    $tab[$k]["groupid"] = $v;
    $dom = new Domain($action->GetParam("CORE_USERDB"),$gu->iddomain);
    $tab[$k]["groupname"] = "{$gu->login}@{$dom->name}";
  }
  
  
  $action->lay->SetBlockData("VIEWGROUP", $tab);



    
  
  
  
  $action->lay->Set("APP", $papp);
  $action->lay->Set("ACTION", $paction);
  $action->lay->Set("ARGS", $pargs);

  
  $form = new SubForm("edit");
  $form->SetParam("firstname");
  $form->SetParam("lastname");
  $form->SetParam("passwd");
  $form->SetParam("id");
  $form->SetParam("domainid","","sel");
  $action->parent->AddJsCode($form->GetSubJs());
  $control=$action->GetLayoutFile("user_control.js");
  $lay = new Layout($control);
  $action->parent->AddJsCode($lay->gen());

}
?>
