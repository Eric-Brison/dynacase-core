<?php
/**
 * User modify properties
 *
 * @author Anakeen 2000 
 * @version $Id: user_mod.php,v 1.12 2007/02/21 11:08:02 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage USERS
 */
 /**
 */




include_once("Class.SubForm.php");
include_once("Class.User.php");
include_once("Class.MailAccount.php");

// -----------------------------------
function user_mod(&$action) {
  // -----------------------------------

  $action->log->info("USERMOD ");
  // Get all the params      
  $id=GetHttpVars("id");
  $newgroup=GetHttpVars("groupview",array());
  

  if ($id == "") {
    $user = new User($action->GetParam("CORE_DB"));
  } else {
    $user = new User($action->GetParam("CORE_DB"),$id);
  } 

 

  $group = (GetHttpVars("group") == "yes");

  $user->firstname=GetHttpVars("firstname");
  $user->lastname=GetHttpVars("lastname");
  $user->status=GetHttpVars("status");
  $user->passdelay=intval(GetHttpVars("passdelay"))*3600*24; // day to second

  $expdate= GetHttpVars("expdate");
  $exptime=0;
  if ($expdate != "") {
    if (ereg("([0-9][0-9])/([0-9][0-9])/(2[0-9][0-9][0-9]) ([0-2][0-9]):([0-5][0-9]):([0-5][0-9])", $expdate, $reg)) {
   
      $exptime=mktime($reg[4],$reg[5],$reg[6],$reg[2],$reg[1],$reg[3]);
      
    } else {
      $action->AddWarningMsg(_("expire date format is not correct : will be set to default"));
    }
  }

  if ($id == "") {
    $user->login=GetHttpVars("login");
    $user->iddomain = GetHttpVars("domainid"); 
    $user->password_new=GetHttpVars("passwd");
    $user->isgroup = ($group) ? 'Y' : 'N'; 
    $res = $user->Add(true);
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
  }
  if (GetHttpVars("passwd")!="") {
    $user->password_new=GetHttpVars("passwd");
       
    if (($exptime>0) && ($exptime != $user->expires))  $user->expires=$exptime;
    else  $user->expires=0; // means recompute expire date if needed
       
  } else {
    if (($exptime>0) && ($exptime != $user->expires))  $user->expires=$exptime;	 
  }
    
  $user->Modify();
    
  $rgid=$user->GetGroupsId();

  if ((count($rgid)!=count($newgroup)) || (count(array_diff($rgid,$newgroup))!=0)) {
      
    $ugroup = new Group($action->dbaccess,$user->id);
    if ($ugroup-> IsAffected()) {
      $ugroup -> Delete(true); // delete all before add
    } else { // new group
      $ugroup->iduser = $user->id;
    }
    if ( (is_array($newgroup))) {
      while(list($k,$v) = each($newgroup)) {
	$ugroup->idgroup = $v;
	$ugroup-> Add(true);
      }
    }
  
    // only at the end : it is not necessary before
    $ugroup->FreedomCopyGroup();
    if (usefreedomuser()) {
      $gdif=array_merge(array_diff($rgid,$newgroup),array_diff($newgroup,$rgid));

      refreshGroups($gdif,true);
    }
  }

  if ($group) {
    redirect($action,"USERS","GROUP_TABLE");
  } else {
    redirect($action,"USERS","USER_TABLE");
  }
}
?>
