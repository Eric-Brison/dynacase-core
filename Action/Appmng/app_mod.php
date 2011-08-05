<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000
 * @version $Id:  $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package WHAT
 * @subpackage APPMNG
 */
/**
 */

include_once ("Class.SubForm.php");
include_once ("Class.Application.php");
// -----------------------------------
function app_mod(&$action)
{
    // -----------------------------------
    // Get all the params
    $id = GetHttpVars("id");
    
    if ($id == "") {
        $AppCour = new Application($action->GetParam("CORE_DB"));
    } else {
        $AppCour = new Application($action->GetParam("CORE_DB") , $id);
    }
    $AppCour->name = GetHttpVars("name");
    $AppCour->short_name = GetHttpVars("short_name");
    $AppCour->description = GetHttpVars("description");
    $AppCour->displayable = GetHttpVars("displayable");
    $AppCour->available = GetHttpVars("available");
    $AppCour->access_free = GetHttpVars("access_free");
    $AppCour->machine = GetHttpVars("machine");
    $AppCour->ssl = GetHttpVars("ssl");
    
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
