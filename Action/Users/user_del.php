<?php
/**
 * Action to delete a user account 
 *
 * The action also delete mail account of the user
 * @author Anakeen 2000 
 * @version $Id: user_del.php,v 1.6 2005/07/08 15:29:51 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage USERS
 */
/**
 */

include_once("Class.SubForm.php");
include_once("Class.MailAccount.php");
include_once("Class.User.php");
/**
 * Action to delete a user account 
 *
 * The action also delete mail account of the user
 * @param Action current action
 */
function user_del(&$action) {
// -----------------------------------

  // Get all the params      
  $id=GetHttpVars("id");

  if ($id !== "" && $id != 1) {
    $user = new User($action->GetParam("CORE_DB"),$id);
    if ( (isset($action->user)) && 
         ($action->HasPermission("ADMIN") ||
          (($action->HasPermission("DOMAIN_MASTER")) && 
           ($action->user->iddomain == $user->iddomain) && 
           ($action->user->id != $user->id)))) {
      $fid=$user->fid;
      $err=$user->Delete();

      if (($err=="") && ($fid > 0)) {
	include_once("FDL/Class.Doc.php");
	$du=new_Doc($action->getParam("FREEDOM_DB"),$fid);
	$du->Delete();
      }

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
