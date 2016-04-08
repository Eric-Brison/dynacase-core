<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: foliosel.php,v 1.2 2003/08/18 15:47:03 eric Exp $
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
function foliosel(Action &$action)
{
    // -----------------------------------
    // Get all the params
    $selid = GetHttpVars("selid", 0); //
    $selected = (GetHttpVars("selected", "N") == "Y"); // is selected
    //  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
    $action->lay->eset("selid", $selid);
    $action->lay->set("selected", $selected ? "true" : "false");
}
?>