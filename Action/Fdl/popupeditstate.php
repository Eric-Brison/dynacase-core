<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * States menu in edit mode
 *
 * @author Anakeen
 * @version $Id: popupeditstate.php,v 1.1 2007/06/27 10:04:29 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/popupdocdetail.php");
function popupeditstate(Action & $action)
{
    $docid = GetHttpVars("id");
    if ($docid == "") $action->exitError(_("No identificator"));
    $popup = array();
    
    $dbaccess = $action->dbaccess;
    $doc = new_Doc($dbaccess, $docid);
    
    addStatesPopup($popup, $doc);
    
    foreach ($popup as $k => $v) {
        $popup[$k]["submenu"] = "";
        $popup[$k]["jsfunction"] = "document.getElementById('seltrans').value='$k';askForTransition(event)";
    }
    
    popupdoc($action, $popup);
}
