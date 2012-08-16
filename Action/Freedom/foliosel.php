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
 * @version $Id: foliosel.php,v 1.2 2003/08/18 15:47:03 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage GED
 */
/**
 */
// ---------------------------------------------------------------
// $Id: foliosel.php,v 1.2 2003/08/18 15:47:03 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/foliosel.php,v $
// ---------------------------------------------------------------

include_once ("FDL/Lib.Dir.php");
include_once ("FDL/freedom_util.php");
include_once ('FREEDOM/Lib.portfolio.php');
// -----------------------------------
function foliosel(&$action)
{
    // -----------------------------------
    // Get all the params
    $selid = GetHttpVars("selid", 0); //
    $selected = (GetHttpVars("selected", "N") == "Y"); // is selected
    //  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
    $action->lay->set("selid", $selid);
    $action->lay->set("selected", $selected ? "true" : "false");
}
?>