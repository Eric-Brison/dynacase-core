<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Modify action parameters
 *
 * @author Anakeen
 * @version $Id: action_mod.php,v 1.4 2005/07/08 15:29:51 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage APPMNG
 */
/**
 */

include_once ("Class.SubForm.php");
include_once ("Class.Action.php");
// -----------------------------------
function action_mod(&$action)
{
    // -----------------------------------
    // Get all the params
    $id = GetHttpVars("id");
    $appl_id = $action->Read("action_appl_id");
    
    if ($id == "") {
        $ActionCour = new Action($action->GetParam("CORE_DB"));
    } else {
        $ActionCour = new Action($action->GetParam("CORE_DB") , array(
            $id,
            $appl_id
        ));
    }
    
    $ActionCour->available = GetHttpVars("available");
    
    if ($id == "") {
        $res = $ActionCour->Add();
        if ($res != "") {
            $txt = $action->text("err_add_action") . " : $res";
            $action->Register("USERS_ERROR", AddSlashes($txt));
        }
    } else {
        $res = $ActionCour->Modify();
        if ($res != "") {
            $txt = $action->text("err_mod_action") . " : $res";
            $action->Register("USERS_ERROR", AddSlashes($txt));
        }
    }
    redirect($action, "APPMNG", "ACTIONLIST");
}
?>
