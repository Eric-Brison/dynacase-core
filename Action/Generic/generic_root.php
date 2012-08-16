<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Display two frames
 *
 * @author Anakeen
 * @version $Id: generic_root.php,v 1.6 2006/11/09 10:52:50 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */
include_once ("GENERIC/generic_util.php");
function generic_root(&$action)
{
    
    $famid = GetHttpVars("famid"); // family restriction
    $dbaccess = $action->GetParam("FREEDOM_DB");
    if ($famid && (!is_numeric($famid))) $famid = getFamIdFromName($dbaccess, $famid);
    if ($famid != "") $action->register("DEFAULT_FAMILY", $famid); // for old compatibility
    $action->lay->set("famid", $famid);
    $smode = getSplitMode($action);
    
    switch ($smode) {
        case "H":
            
            $action->lay->set("rows", $action->getParam("GENEA_HEIGHT") . ",*");
            $action->lay->set("cols", "");
            break;

        default:
        case "V":
            
            $action->lay->set("cols", $action->getParam("GENEA_WIDTH") . ",*");
            $action->lay->set("rows", "");
            break;
    }
    $action->lay->set("GTITLE", _($action->parent->short_name));
    $action->lay->set("famid", $famid);
}
?>