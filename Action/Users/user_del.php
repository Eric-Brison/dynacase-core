<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Action to delete a user account
 *
 * The action also delete mail account of the user
 * @author Anakeen 2000
 * @version $Id: user_del.php,v 1.7 2007/02/21 11:08:02 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage USERS
 */
/**
 */

include_once ("Class.SubForm.php");
include_once ("Class.MailAccount.php");
include_once ("Class.User.php");
/**
 * Action to delete a user account
 *
 * The action also delete mail account of the user
 * @param Action current action
 */
function user_del(&$action)
{
    // -----------------------------------
    // Get all the params
    $id = GetHttpVars("id");
    
    if ($id !== "" && $id != 1) {
        $user = new User($action->GetParam("CORE_DB") , $id);
        if ((isset($action->user)) && ($action->HasPermission("ADMIN") || (($action->HasPermission("DOMAIN_MASTER")) && ($action->user->iddomain == $user->iddomain) && ($action->user->id != $user->id)))) {
            $fid = $user->fid;
            $err = $user->Delete();
            
            if (($err == "") && ($fid > 0) && usefreedomuser()) {
                $du = new_Doc($action->getParam("FREEDOM_DB") , $fid);
                $du->Delete();
            }
            
            $mailapp = new Application();
            if (($action->user->isgroup != "Y") && $mailapp->Exists("MAILADMIN")) {
                $mailapp->Set("MAILADMIN", $action->parent);
                $acc = new MailAccount($mailapp->Getparam("MAILDB") , $id);
                $acc->Remove();
            }
        } else {
            $action->info("Access Not Allowed");
            Redirect($action, "CORE", "");
        }
    }
    
    if (isset($user) && ($user->isgroup == "Y")) {
        redirect($action, "USERS", "GROUP_TABLE");
    } else {
        redirect($action, "USERS", "USER_TABLE");
    }
}
?>
