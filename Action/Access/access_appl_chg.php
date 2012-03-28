<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

include_once ("Class.QueryDb.php");
include_once ("Class.Application.php");
include_once ("Class.Acl.php");
include_once ("Class.Permission.php");
// -----------------------------------
function access_appl_chg(Action & $action)
{
    // -----------------------------------
    // select the first user if not set
    // What user are we working on ? ask session.
    $user_id = GetHttpVars("id", getHttpVars("_appid"));
    $isclass = (GetHttpVars("isclass") == "yes");
    $action->log->debug("appl_id : " . $user_id);
    
    if ($isclass) {
        $action->Register("access_class_id", $user_id);
        redirect($action, "ACCESS", "OBJECT_ACCESS");
    } else {
        $action->Register("access_appl_id", $user_id);
        redirect($action, "ACCESS", "APPL_ACCESS");
    }
}
?>
