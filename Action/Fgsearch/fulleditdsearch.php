<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Full Text Search document
 *
 * @author Anakeen
 * @version $Id: fulleditdsearch.php,v 1.1 2007/10/17 14:27:28 marc Exp $
 * @package FDL
 * @subpackage GED
 */
/**
 */

include_once ("FDL/Class.Dir.php");
include_once ("FDL/Lib.Dir.php");
include_once ("FDL/freedom_util.php");
/**
 * Interface Fulltext Detailled  Search document
 * @param Action &$action current action
 * @global keyword string Http var : word to search in any values
 * @global famid int Http var : restrict to this family identioficator
 * @global start int Http var : page number
 * @global dirid int Http var : search identifier
 */
function fulleditdsearch(Action & $action)
{
    
    $famid = $action->getArgument("famid", 0);
    $substitute = ($action->getArgument("substitute") == "yes");
    
    $dbaccess = $action->dbaccess;
    if (!is_numeric($famid)) $famid = getFamIdFromName($dbaccess, $famid);
    if ($famid == 0) $famid = 7; // FILE family
    $action->lay->set("searchtitle", _("detailled search"));
    $action->lay->set("substitute", (bool)$substitute);
    
    $tclassdoc = getNonSystemFamilies($dbaccess, $action->user->id, "TABLE");
    $selectclass = array();
    foreach ($tclassdoc as $k => $cdoc) {
        $selectclass[$k]["idcdoc"] = $cdoc["initid"];
        $selectclass[$k]["classname"] = $cdoc["title"];
        $selectclass[$k]["famselect"] = ($cdoc["initid"] == $famid) ? "selected" : "";
    }
    $action->lay->SetBlockData("SELECTFAM", $selectclass);
    
    if ($famid > 0) {
        /**
         * @var \Dcp\Family\DSEARCH $search
         */
        $search = createTmpDoc($dbaccess, 16);
        
        $search->setValue("se_famid", $famid);
        $search->setValue("se_latest", "yes");
        $search->lay = $action->lay;
        $search->editdsearch();
        
        $fdoc = new_doc($dbaccess, $famid);
        $action->lay->set("famicon", $fdoc->getIcon());
        $action->lay->set("famid", $fdoc->id);
    }
}
