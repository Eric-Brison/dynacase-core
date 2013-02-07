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
function generic_root(Action & $action)
{

    $usage = new ActionUsage($action);
    $famid = $usage->addRequiredParameter("famid", "id or logical name of the family"); // family restriction
    $usage->setStrictMode(false);
    $usage->verify(true);

    $dbaccess = $action->GetParam("FREEDOM_DB");
    if ($famid && (!is_numeric($famid))) {
        $famid = getFamIdFromName($dbaccess, $famid);
    }
    if ($famid != "") {
        $action->register("DEFAULT_FAMILY", $famid); // for old compatibility
    }
    $action->lay->set("famid", $famid);
    $smode = getSplitMode($action);
    
    switch ($smode) {
        case "H":
            $action->lay->set("rows", $action->getParam("GENEA_HEIGHT") . ",*");
            $action->lay->set("cols", "");
            break;

        case "V":
        default:
            $action->lay->set("cols", $action->getParam("GENEA_WIDTH") . ",*");
            $action->lay->set("rows", "");
    }
    $action->lay->set("GTITLE", _($action->parent->short_name));
    $action->lay->set("famid", $famid);
}
