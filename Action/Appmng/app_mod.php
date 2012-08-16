<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id:  $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage APPMNG
 */
/**
 */

include_once ("Class.SubForm.php");
include_once ("Class.Application.php");
// -----------------------------------
function app_mod(Action & $action)
{
    // -----------------------------------
    // Get all the params
    $id = $action->getArgument("id");
    
    if ($id == "") {
        $AppCour = new Application($action->GetParam("CORE_DB"));
    } else {
        $AppCour = new Application($action->GetParam("CORE_DB") , $id);
    }
    $AppCour->displayable = $action->getArgument("displayable");
    $AppCour->available = $action->getArgument("available");
    $AppCour->machine = $action->getArgument("machine");
    $AppCour->ssl = $action->getArgument("ssl");
    
    if ($id == "") {
        $res = $AppCour->Add();
        if ($res != "") {
            $txt = $action->text("err_add_application") . " : $res";
            $action->Register("USERS_ERROR", AddSlashes($txt));
        }
    } else {
        $res = $AppCour->Modify();
        if ($res != "") {
            $txt = $action->text("err_mod_application") . " : $res";
            $action->Register("USERS_ERROR", AddSlashes($txt));
        }
    }
    redirect($action, "APPMNG", "");
}
?>
