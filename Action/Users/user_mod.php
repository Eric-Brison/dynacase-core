<?php
// ---------------------------------------------------------------
// $Id: user_mod.php,v 1.4 2003/04/14 18:35:16 marc Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Users/user_mod.php,v $
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
include_once("Class.User.php");
include_once("Class.MailAccount.php");

// -----------------------------------
function user_mod(&$action) {
// -----------------------------------

  $action->log->info("USERMOD ");
  // Get all the params      
  $id=GetHttpVars("id");
  $newgroup=GetHttpVars("groupview");

  if ($id == "") {
    $user = new User($action->GetParam("CORE_USERDB"));
  } else {
    $user = new User($action->GetParam("CORE_USERDB"),$id);
  } 

  $papp = GetHttpVars("papp","APPMNG");
  $paction = GetHttpVars("paction","PARAM_CUACCOUNT");
  $pargs = GetHttpVars("pargs","");

  $group = (GetHttpVars("group") == "yes");

  $user->firstname=GetHttpVars("firstname");
  $user->lastname=GetHttpVars("lastname");

  if ($id == "") {
    $user->login=GetHttpVars("login");
    $user->iddomain = GetHttpVars("domainid"); 
    $user->password_new=GetHttpVars("passwd");
    $user->isgroup = ($group) ? 'Y' : 'N'; 
    $res = $user->Add();
    if ($res != "") { 
      $txt = $action->text("err_add_user")." : $res";
      $action->Register("USERS_ERROR",AddSlashes($txt));
    }
    if (!$group && $user->iddomain != 1) {
      $mailapp = new Application();
      if ($mailapp->Exists("MAILADMIN")) {
        $mailapp->Set("MAILADMIN", $action->parent);
        $uacc = new MailAccount($mailapp->GetParam("MAILDB"));
        $uacc->iddomain    = $user->iddomain ;
        $uacc->iduser      = $user->id;
        $uacc->login       = $user->login;
        $uacc->Add();
      }
    }
  } else {
    // Affect the user to a domain
    if (!$group && ($user->iddomain == 1) && (GetHttpVars("domainid") !=1)) {
      $user->iddomain = GetHttpVars("domainid");
      $mailapp = new Application();
      if ($mailapp->Exists("MAILADMIN")) {
        $mailapp->Set("MAILADMIN", $action->parent);
        $uacc = new MailAccount($mailapp->GetParam("MAILDB"));
        $uacc->iddomain    = $user->iddomain ;
        $uacc->iduser      = $user->id;
        $uacc->login       = $user->login;
        $uacc->Add();
      }
    }
    if (GetHttpVars("passwd")!="") {
       $user->password_new=GetHttpVars("passwd");
    }
    $user->Modify();
  }
    
  $ugroup = new Group($action->dbaccess,$user->id);
  if ($ugroup-> IsAffected()) {
      $ugroup -> Delete(); // delete all before add
    } else { // new group
      $ugroup->iduser = $user->id;
    }
  if ( (is_array($newgroup))) {
    while(list($k,$v) = each($newgroup)) {
      $ugroup->idgroup = $v;
      $ugroup-> Add();
    }
  }
  

  if ($group) {
    redirect($action,"USERS","GROUP_TABLE");
  } else {
    redirect($action,$papp,$paction);
  }
}
?>
