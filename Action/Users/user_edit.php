<?php
// ---------------------------------------------------------------
// $Id: user_edit.php,v 1.5 2003/04/14 18:47:10 marc Exp $
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
  if ($id==-1) $id="";
  $papp = GetHttpVars("papp","APPMNG");
  $paction = GetHttpVars("paction","PARAM_CUACCOUNT");
  $pargs = GetHttpVars("pargs","");

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
  $tfirstname =array();
  $tfirstname[0]["firstname"]="";
  if ($id == "") {

    if ($group) $action->lay->SetBlockData("HIDDENFIRSTNAME", $tfirstname );
    else $action->lay->SetBlockData("FIRSTNAME", $tfirstname );

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
      $ugroup = array("2"); // default group
    } else if ($action->HasPermission("DOMAIN_MASTER")) {
      $seldom=$action->user->iddomain;
      $query=new QueryDb($action->dbaccess, "User");
      $query->AddQuery("iddomain=$seldom");
      $query->AddQuery("login='all'");
      $table=$query->Query();
      if ($query->nb>0) $ugroup = array($table[0]->id); // default domain group 
      else $ugroup = array("2"); // default group
    } else {
      $action->exitError(_("Not Allowed Access Attempt : need DOMAIN_MASTER privilege"));
    }
    

  } else {
    $user = new User($action->GetParam("CORE_USERDB"),$id);
    $action->lay->Set("id",$id);
    if ($group) $action->lay->SetBlockData("HIDDENFIRSTNAME", $tfirstname );
    else {
      $tfirstname[0]["firstname"] = $user->firstname;
      $action->lay->SetBlockData("FIRSTNAME", $tfirstname );
    }
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


    if ($action->HasPermission("DOMAIN_MASTER")) {
      $action->lay->Set("imgchggroup",$action->GetIcon("users.gif",
						       "chggroup",15)); 
    
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
  
  $action->lay->Set("APP", $papp);
  $action->lay->Set("ACTION", $paction);
  $action->lay->Set("ARGS", $pargs);

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
