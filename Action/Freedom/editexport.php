<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Export edition
 *
 * @author Anakeen
 * @version $Id: editexport.php,v 1.4 2008/11/13 17:25:29 eric Exp $
 * @package FDL
 * @subpackage GED
 */
/**
 */

include_once ("FDL/Class.Doc.php");
include_once ("FDL/Lib.Dir.php");
include_once ("GENERIC/generic_util.php");

function editexport(Action & $action)
{
    $dbaccess = $action->dbaccess;
    $docid = GetHttpVars("id", 0);
    $action->parent->addJsRef("lib/jquery/jquery.js");
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/subwindow.js");
    $action->parent->AddJsRef("FDL:editexport.js", true);
    $doc = new_Doc($dbaccess, $docid);
    $exportId = uniqid("export");
    $action->lay->Set("dirid", $doc->id);
    $action->lay->Set("title", $doc->getHTMLTitle());
    $action->lay->Set("exportid", $exportId);
    $famid = 0;
    $tclassdoc = GetClassesDoc($dbaccess, $action->user->id, $famid, "TABLE");
    $selectclass = array();
    $pref = getFamilyParameters($action, "FREEDOM_EXPORTCOLS");
    $tfam = array_keys($pref);
    foreach ($tclassdoc as $k => $cdoc) {
        $selectclass[$k]["idcdoc"] = $cdoc["id"];
        $selectclass[$k]["classname"] = getFamTitle($cdoc);
        $selectclass[$k]["pref"] = in_array($cdoc["id"], $tfam);
    }
    $action->lay->setBlockData("coptions", $selectclass);
    
    $csvSeparator = $action->getParam("EXPORT_CSVSEPARATOR");
    $csvEnclosure = $action->getParam("EXPORT_CSVENCLOSURE");
    
    $action->lay->set("selectDoubleQuote", false);
    $action->lay->set("selectSimpleQuote", false);
    $action->lay->set("customEnclosure", false);
    $action->lay->set("customSeparator", false);
    $action->lay->set("selectComma", false);
    
    if ($csvSeparator === ",") {
        $action->lay->set("selectComma", true);
    } elseif ($csvSeparator !== ";") {
        $action->lay->set("customSeparator", $csvSeparator);
    }
    
    if ($csvEnclosure === '"') {
        $action->lay->set("selectDoubleQuote", true);
    } elseif ($csvEnclosure === "'") {
        $action->lay->set("selectSimpleQuote", true);
    } elseif ($csvEnclosure !== "") {
        $action->lay->set("customEnclosure", $csvEnclosure);
    }
    
    if ($action->canExecute("EXPORTFOLDER", "DOCADMIN") == "" && preg_match('/\\/admin.php$/', $_SERVER["SCRIPT_NAME"])) {
        $action->lay->set("exportaction", "EXPORTFOLDER");
        $action->lay->set("exportapp", "DOCADMIN");
        $action->lay->set("viewinfo", true);
    } else {
        $action->lay->set("exportaction", "EXPORTFLD");
        $action->lay->set("exportapp", "FDL");
        $action->lay->set("viewinfo", false);
    }
}
