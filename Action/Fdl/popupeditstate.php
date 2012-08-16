<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * States menu in edit mode
 *
 * @author Anakeen
 * @version $Id: popupeditstate.php,v 1.1 2007/06/27 10:04:29 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/popupdocdetail.php");
function popupeditstate(&$action)
{
    $docid = GetHttpVars("id");
    if ($docid == "") $action->exitError(_("No identificator"));
    $popup = array();
    
    $dbaccess = $action->GetParam("FREEDOM_DB");
    $doc = new_Doc($dbaccess, $docid);
    
    addStatesPopup($popup, $doc);
    
    foreach ($popup as $k => $v) {
        $popup[$k]["submenu"] = "";
        $popup[$k]["jsfunction"] = "document.getElementById('seltrans').value='$k';askForTransition(event)";
    }
    
    popupdoc($action, $popup);
}
