<?php
// ---------------------------------------------------------------
// $Id: user_del.php,v 1.2 2003/04/14 18:35:16 marc Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Users/user_del.php,v $
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
// $Log: user_del.php,v $
// Revision 1.2  2003/04/14 18:35:16  marc
// Groupe : pas d'ajout et suppression de compte de messagerie
//
// Revision 1.1  2002/01/08 12:41:33  eric
// first
//
// Revision 1.6  2001/08/29 14:01:04  eric
// choix du groupe du domaine
//
// Revision 1.5  2000/11/16 14:07:25  yannick
// Destruction de l'utilisateur possible
//
// Revision 1.4  2000/10/30 10:28:44  marc
// Delete -> Remove pour MailAccount
//
// Revision 1.3  2000/10/26 10:41:03  yannick
// Edition par l'utilisateur
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
include_once("Class.MailAccount.php");
include_once("Class.User.php");

// -----------------------------------
function user_del(&$action) {
// -----------------------------------

  // Get all the params      
  $id=GetHttpVars("id");

  if ($id !== "" && $id != 1) {
    $user = new User($action->GetParam("CORE_USERDB"),$id);
    if ( (isset($action->user)) && 
         ($action->HasPermission("ADMIN") ||
          (($action->HasPermission("DOMAIN_MASTER")) && 
           ($action->user->iddomain == $user->iddomain) && 
           ($action->user->id != $user->id)))) {
      $user->Delete();
      $mailapp = new Application();
      if (($action->user->isgroup != "Y") && $mailapp->Exists("MAILADMIN")) {
        $mailapp->Set("MAILADMIN", $action->parent);
        $acc = new MailAccount($mailapp->Getparam("MAILDB"),$id);
        $acc->Remove();
      }
    } else {
      $action->info("Access Not Allowed");
      Redirect($action,"CORE","");
    }
  }    

  if (isset($user) && ($user->isgroup == "Y")) {
    redirect($action,"USERS","GROUP_TABLE");
  } else {
    redirect($action,"USERS","USER_TABLE");
  }
}
?>
