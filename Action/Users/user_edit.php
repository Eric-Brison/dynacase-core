<?php
// ---------------------------------------------------------------
// $Id: user_edit.php,v 1.1 2002/01/08 12:41:33 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Users/user_edit.php,v $
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
// $Log: user_edit.php,v $
// Revision 1.1  2002/01/08 12:41:33  eric
// first
//
// Revision 1.15  2001/09/12 10:31:41  eric
// seul domain_master peut changer les groupes
//
// Revision 1.14  2001/08/31 13:22:46  eric
// modification pour éviter la récursivité dans les groupes
//
// Revision 1.13  2001/08/29 14:01:04  eric
// choix du groupe du domaine
//
// Revision 1.12  2001/08/28 10:14:07  eric
// modification pour la prise en comptes des groupes d'utilisateurs
//
// Revision 1.11  2000/11/13 11:38:16  marc
// Edition d'un nouvel utilisateur : ne fonctionnait pas, le domaine
// 'local' n'étant pas récupéré
//
// Revision 1.10  2000/10/27 10:28:28  marc
// Utilisation de la derniere version de Class.Domain.php
//
// Revision 1.9  2000/10/26 15:58:51  yannick
// L'utilisateur ne peut pas modifier son domaine
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
// Revision 1.4  2000/10/23 11:05:40  marc
// Domain mngt
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
include_once("Class.Domain.php");
include_once("Class.MailAccount.php");
include_once("Class.User.php");

// -----------------------------------
function user_edit(&$action) {
// -----------------------------------


  // Get all the params      
  global $HTTP_POST_VARS;
  $id=GetHttpVars("id");

  // initialise if user group or single user
  $group = (GetHttpVars("group") == "yes");

  $tpasswd =array();
  $tpasswd[0]["passwd"]="";   

 
  if ($group) { // dont't see passwd
    $action->lay->SetBlockData("HIDDENPASSWD", $tpasswd );
  } else {
    // in user mode : display passwd field
    $action->lay->SetBlockData("PASSWD", $tpasswd );
  }


  if (!$action->HasPermission("DOMAIN_MASTER")) {
    $id=$action->user->id;
  }
  if ($id == "") {
    $action->lay->Set("firstname","");
    $action->lay->Set("lastname","");
    
    $action->lay->Set("id","");
    if ($group) {
      $action->lay->Set("TITRE",$action->text("titlecreateg"));
    } else {
      $action->lay->Set("TITRE",$action->text("titlecreateu"));

    }
    $action->lay->Set("BUTTONTYPE",$action->text("butcreate"));
    $login = new Layout($action->GetLayoutFile("login_create.xml"),$action);
    $login->set("login","");
    if ($action->HasPermission("ADMIN")) {
      $seldom=1;
    } else if ($action->HasPermission("DOMAIN_MASTER")) {
      $seldom=$action->user->iddomain;
    } else {
      $action->info("Not Allowed Access Attempt");
    }
    
    $ugroup = array("2"); // default group

  } else {
    $user = new User($action->GetParam("CORE_USERDB"),$id);
    $action->lay->Set("id",$id);
    $action->lay->Set("firstname",$user->firstname);
    $action->lay->Set("lastname",$user->lastname);

    if ($group) {
      $action->lay->Set("TITRE",$action->text("titlemodifyg"));
    } else {      
      $action->lay->Set("TITRE",$action->text("titlemodifyu"));
    }
    $action->lay->Set("BUTTONTYPE",$action->text("butmodify"));
    $login = new Layout($action->GetLayoutFile("login.xml"),$action);
    $action->log->debug(" utilisateur connu : {$user->login}");
    $login->set("login",$user->login);
    $seldom=$user->iddomain;
    
    $ug = new Group($action->GetParam("CORE_USERDB"),$user->id);
    $ugroup = $ug->groups;  // direct group 
  }

  $tab = array();

  // Mail domain can be change only if it's not yet set
  if ($seldom == 1) {
    if ($action->HasPermission("ADMIN")) {

      $dom = new Domain($action->GetParam("CORE_USERDB"));
      $dom->ListAll(0);
    
      while (list($k, $v) = each($dom->qlist)) {
        $tab[$k]["selected"] = ($v->iddomain == $seldom ? "selected" : "");
        $tab[$k]["domainid"] = $v->iddomain;
        $tab[$k]["domainname"] = ($v->iddomain == 1 ? $action->text("nomail") : $v->name);
      }
    } else {
      $tab[0]["selected"] = "selected";
      $tab[0]["domainid"] = $seldom;
      $tab[0]["domainname"] = $action->text("nomail");
    }
    $action->lay->Set("disableddomain","");
  } else {
    $tab[0]["selected"] = "selected";
    $dom = new Domain($action->GetParam("CORE_USERDB"),$seldom);
    $tab[0]["domainid"]=$dom->iddomain; 
    $tab[0]["domainname"]=$dom->name; 
    $action->lay->Set("disableddomain","disabled");
  }

  $action->lay->SetBlockData("SELECTDOMAIN", $tab);



  // search user group
  $tab = array();

  // 
  while (list($k, $v) = each($ugroup)) {
    $gu = new User($action->GetParam("CORE_USERDB"), $v);
        $tab[$k]["groupid"] = $v;
	$dom = new Domain($action->GetParam("CORE_USERDB"),$gu->iddomain);
        $tab[$k]["groupname"] = "{$gu->login}@{$dom->name}";
      }
  
  if (count($tab) > 1) {
    $action->lay->Set("sizegv","2");
  } else {
    $action->lay->Set("sizegv","1");
  }
  $action->lay->SetBlockData("VIEWGROUP", $tab);



  $action->lay->Set("imgchggroup",""); 

  if ($id != "") {       
    if ($action->HasPermission("DOMAIN_MASTER")) {
      $action->lay->Set("imgchggroup",$action->GetIcon("users.gif",
						       "chggroup",15)); 
    
    }
  }  
  
  

  // search all group
  $tabd = array(); // domain table
  $tabo = array(); // other table

  // 
  $bduser = new User($action->GetParam("CORE_USERDB"));
  $allgroups = $bduser-> GetGroupList();

  while (list($k, $g) = each($allgroups)) {
      $infogroup = array();
        $infogroup["groupid"] = $g->id;
	if (in_array($g->id, $ugroup)) {	  
	  $infogroup["selectgroup"] = "selected"; 
	} else {
	  $infogroup["selectgroup"] = ""; 
	}
	$dom = new Domain($action->GetParam("CORE_USERDB"),$g->iddomain);
        $infogroup["groupname"] = "{$g->login}@{$dom->name}";

	
	if (isset($user)) {
	  // search group inherit in group to avoid recursion
	  $ug = new Group($action->GetParam("CORE_USERDB"), $g->id);

	  if (($user->isgroup != "Y") ||
	      ((! in_array($user->id, $ug-> GetAllGroups())) && // don
	       ($g->id != $user->id))
	      ) {
	    
	    if ($g->iddomain == $user->iddomain) {
	      $tabd[] = $infogroup;
	    } else {
	      $tabo[] = $infogroup;
	    }
	  
	  }
	} else {
	  $tabo[] = $infogroup;
	}
      }
  

  $action->lay->SetBlockData("SELECTDOMAINGROUP", $tabd);
  $action->lay->SetBlockData("SELECTOTHERGROUP", $tabo);
  
  $action->lay->Set("LOGIN_MOD",$login->gen());
  $form = new SubForm("edit");
  $form->SetParam("firstname");
  $form->SetParam("lastname");
  $form->SetParam("login");
  $form->SetParam("passwd");
  $form->SetParam("id");
  $form->SetParam("domainid","","sel");
  $action->parent->AddJsCode($form->GetSubJs());
  $control=$action->GetLayoutFile("user_control.js");
  $lay = new Layout($control);
  $action->parent->AddJsCode($lay->gen());

}
?>
