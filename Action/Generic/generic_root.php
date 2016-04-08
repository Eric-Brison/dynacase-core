<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Display two frames
 *
 * @author Anakeen
 * @version $Id: generic_root.php,v 1.6 2006/11/09 10:52:50 eric Exp $
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
    
    $dbaccess = $action->dbaccess;
    if ($famid && (!is_numeric($famid))) {
        $famid = getFamIdFromName($dbaccess, $famid);
    }
    if ($famid != "") {
        $action->register("DEFAULT_FAMILY", $famid); // for old compatibility
        
    }
    $smode = getSplitMode($action);
    
    switch ($smode) {
        case "H":
            $action->lay->eset("rows", $action->getParam("GENEA_HEIGHT") . ",*");
            $action->lay->eset("cols", "");
            break;

        case "V":
        default:
            $action->lay->eset("cols", $action->getParam("GENEA_WIDTH") . ",*");
            $action->lay->eset("rows", "");
    }
    $action->lay->eset("GTITLE", _($action->parent->short_name));
    $action->lay->set("famid", urlencode($famid));
}
