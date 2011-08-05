<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 * list available families
 *
 * @author Anakeen 2003
 * @version $Id: onefam_list.php,v 1.13 2007/01/03 19:38:59 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.Doc.php");
include_once ("FDL/Lib.Dir.php");

function onefam_list(&$action)
{
    $action->lay->set("APP_TITLE", _($action->parent->description));
    
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/subwindow.js");
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/resizeimg.js");
    
    $action->lay->SetBlockData("SELECTMASTER", getTableFamilyList($action->GetParam("ONEFAM_MIDS")));
    
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
        $action->lay->SetBlockData("SELECTUSER", getTableFamilyList($action->GetParam("ONEFAM_IDS")));
    }
    if ($action->HasPermission("ONEFAM_MASTER")) {
        $action->lay->SetBlockData("CHOOSEMASTERFAMILIES", array(
            array(
                "zou"
            )
        ));
    }
    $iz = $action->getParam("CORE_ICONSIZE");
    $izpx = intval($action->getParam("SIZE_IMG-SMALL"));
    
    $action->lay->set("izpx", $izpx);
}

function getTableFamilyList($idsfam)
{
    $selectclass = array();
    if ($idsfam != "") {
        $tidsfam = explode(",", $idsfam);
        
        $dbaccess = GetParam("FREEDOM_DB");
        
        foreach ($tidsfam as $k => $cid) {
            $cdoc = new_Doc($dbaccess, $cid);
            if ($cdoc->dfldid > 0) {
                if ($cdoc->control('view') == "") {
                    $selectclass[$k]["idcdoc"] = $cdoc->initid;
                    $selectclass[$k]["ftitle"] = $cdoc->title;
                    $selectclass[$k]["iconsrc"] = $cdoc->getIcon();
                }
            }
        }
    }
    return $selectclass;
}
?>
