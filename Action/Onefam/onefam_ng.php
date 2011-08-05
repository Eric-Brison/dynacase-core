<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000
 * @version $Id: onefam_ng.php,v 1.9 2008/04/18 09:47:38 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage
 */
/**
 */
include_once ("ONEFAM/onefam_root.php");
function onefam_ng(Action &$action)
{

    $action->lay->set("APP_TITLE", _($action->parent->description));
    
    $nbcol = intval($action->getParam("ONEFAM_LWIDTH", 1));
    
    $delta = 0;
    if ($action->read("navigator") == "EXPLORER") $delta = 10;
    
    $iz = $action->getParam("CORE_ICONSIZE");
    
    $dbaccess = $action->GetParam("FREEDOM_DB");
    
    $izpx = intval($action->getParam("SIZE_IMG-SMALL")) + 2;
    $action->lay->set("wcols", $izpx * $nbcol + $delta);
    $action->lay->set("Title", _($action->parent->short_name));
    
    $openfam = $action->getParam("ONEFAM_FAMOPEN");
    if (($openfam != "") && (!is_numeric($openfam))) $openfam = getFamIdFromName($dbaccess, $openfam);
    if ($openfam > 0) {
        $action->lay->set("OPENFAM", true);
        $action->lay->set("openfam", $openfam);
    } else {
        $action->lay->set("OPENFAM", false);
    }
    $action->lay->Set("oneBgColor", (($action->getParam("ONEFAM_BGCOLOR")!='inherit') && ($action->getParam("ONEFAM_BGCOLOR")!='')));
    
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/subwindow.js");
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/resizeimg.js");
    /* Multidoc */
    $action->parent->AddJsRef("lib/jquery/jquery.js", false);
    $action->parent->AddJsRef("ONEFAM/Layout/onefam_ng.js");
    $action->parent->AddJsRef("FDL:underscore.js", true);
    $action->parent->AddJsRef("FDL:backbone.js", true);
    $action->parent->AddJsRef("FDL:multidoc.js", true);
    
    $iz = $action->getParam("CORE_ICONSIZE");
    $izpx = intval($action->getParam("SIZE_IMG-SMALL"));
    $action->lay->SetBlockData("SELECTMASTER", getTableFamilyList($action->GetParam("ONEFAM_MIDS"),$izpx));
    
    if (($action->GetParam("ONEFAM_IDS") != "") && ($action->GetParam("ONEFAM_MIDS") != "")) {
        $action->lay->SetBlockData("SEPARATOR", array(
            array(
                "zou"
            )
        ));
    
    }
    
    if ($action->HasPermission("ONEFAM")) {
        $action->lay->SetBlockData("CHOOSEUSERFAMILIES", array(
            array(
                "zou"
            )
        ));
        $action->lay->SetBlockData("SELECTUSER", getTableFamilyList($action->GetParam("ONEFAM_IDS"),$izpx));
    }
    if ($action->HasPermission("ONEFAM_MASTER")) {
        $action->lay->SetBlockData("CHOOSEMASTERFAMILIES", array(
            array(
                "zou"
            )
        ));
    }
    
    $action->lay->set("izpx", $izpx);
    // Change CSS
    
    $smode = getSplitMode($action);
    
    switch ($smode) {
    case "vertical" :
        $action->parent->AddCssRef("WHAT/Layout/HB.css");
        break;
    case "inverse" :
        $action->parent->AddCssRef("WHAT/Layout/inverse.css");
        break;
    default :
    case "basic" :
        $action->parent->AddCssRef("ONEFAM:onefam_ng.css",true);
        break;
    
    }

}

?>
