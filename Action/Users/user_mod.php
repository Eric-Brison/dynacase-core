<?php
// ---------------------------------------------------------------
// $Id: user_mod.php,v 1.1 2002/01/08 12:41:33 eric Exp $
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
// $Log: user_mod.php,v $
// Revision 1.1  2002/01/08 12:41:33  eric
// first
//
// Revision 1.16  2001/08/31 13:22:46  eric
// modification pour éviter la récursivité dans les groupes
//
// Revision 1.15  2001/08/28 10:14:07  eric
// modification pour la prise en comptes des groupes d'utilisateurs
//
// Revision 1.14  2001/07/23 17:11:23  marc
// Release 0.3.3, see CHANGELOG
//
// Revision 1.13  2001/03/27 21:08:50  marc
// User creation : set permissions for Squid (FULL if exists) & Mail (USER if
// user have a mail account).
//
// Revision 1.12  2000/12/22 19:58:45  marc
// Connexion login/compte messagerie
//
// Revision 1.11  2000/11/16 14:07:25  yannick
// Destruction de l'utilisateur possible
//
// Revision 1.10  2000/10/26 19:48:34  marc
// Correction petits bugs lors de la creation d'un user sur domaine messagerie
//
// Revision 1.9  2000/10/26 15:18:18  yannick
// Gestion des erreurs
//
// Revision 1.8  2000/10/26 12:52:05  yannick
// Bug : perte du mot de passe
//
// Revision 1.7  2000/10/26 10:41:03  yannick
// Edition par l'utilisateur
//
// Revision 1.6  2000/10/26 08:09:30  yannick
// Traitement de la modification du mot de passe
//
// Revision 1.5  2000/10/26 07:54:27  yannick
// Gestion du domaine sur les utilisateur
//
// Revision 1.4  2000/10/23 14:11:45  yannick
// récupération de mot de passe
//
// Revision 1.3  2000/10/23 10:40:07  marc
// Stable release with mail account creation
//
// Revision 1.2  2000/10/22 16:27:04  marc
// Connexion avec la messagerie
//
// Revision 1.1.1.1  2000/10/19 10:35:49  yannick
// Import initial
//
//
//
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
    if ($user->iddomain != 1) {
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
    if (($user->iddomain == 1) && (GetHttpVars("domainid") !=1)) {
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
    redirect($action,"USERS","USER_TABLE");
  }
}
?>
