<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 */

include_once ("Class.QueryDb.php");
include_once ("Class.Application.php");
include_once ("Class.Acl.php");
include_once ("Class.Permission.php");
// -----------------------------------
function access_user_chg(Action & $action)
{
    // -----------------------------------
    // select the first user if not set
    // What user are we working on ? ask session.
    $user_id = GetHttpVars("id", getHttpVars('_accountid'));
    $accountType = $action->getArgument("accountType");
    $filteruser = getHttpVars("userfilter");
    
    $action->log->debug("user_id : " . $user_id);
    
    if ($accountType == "G") {
        $action->Register("access_group_id", $user_id);
        redirect($action, "ACCESS", "GROUP_ACCESS");
    } else if ($accountType == "R") {
        $action->Register("access_role_id", $user_id);
        redirect($action, "ACCESS", "ROLE_ACCESS");
    } else {
        $action->Register("access_user_id", $user_id);
        redirect($action, "ACCESS", "USER_ACCESS&userfilter=$filteruser");
    }
}
?>
