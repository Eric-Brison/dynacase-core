<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: freedom_editimport.php,v 1.8 2008/08/14 09:59:14 eric Exp $
 * @package FDL
 * @subpackage GED
 */
/**
 */
// ---------------------------------------------------------------
include_once ("FDL/import_file.php");
include_once ("FDL/Lib.Dir.php");
// -----------------------------------
function freedom_editimport(Action & $action)
{
    
    $usage = new ActionUsage($action);
    $usage->setDefinitionText("Import document interface");
    $classid = $usage->addOptionalParameter("classid", "family used to view schema");
    $dirid = $usage->addOptionalParameter("dirid", "directory to place imported doc");
    $descr = ($usage->addOptionalParameter("descr", "view information", array(
        "Y",
        "N"
    ) , "Y") == "Y");
    $policy = ($usage->addOptionalParameter("policy", "view policy options", array(
        "Y",
        "N"
    ) , "Y") == "Y");
    $usage->setStrictMode(false);
    $usage->verify();
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/subwindow.js");
    $action->parent->addJsRef("lib/jquery/jquery.js");
    $action->parent->addJsRef("lib/jquery-ui/js/jquery-ui.js");
    $action->parent->addJsRef("FREEDOM:freedom_editimport.js");
    $action->parent->addCssRef("css/dcp/jquery-ui.css");
    $action->parent->addCssRef("FREEDOM:freedom_editimport.css");
    
    $dbaccess = $action->dbaccess;
    // build list of class document
    $query = new QueryDb($dbaccess, "Doc");
    $query->AddQuery("doctype='C'");
    
    $selectclass = array();
    
    $tclassdoc = GetClassesDoc($dbaccess, $action->user->id, 0, "TABLE");
    
    foreach ($tclassdoc as $k => $cdoc) {
        $selectclass[$k]["idcdoc"] = $cdoc["initid"];
        $selectclass[$k]["classname"] = $cdoc["title"];
        if ($cdoc["initid"] == $classid) $selectclass[$k]["selected"] = "selected";
        else $selectclass[$k]["selected"] = "";
    }
    
    $action->lay->SetBlockData("SELECTCLASS", $selectclass);
    
    $action->lay->set("mailaddr", getMailAddr($action->user->id));
    $action->lay->set("separator", $action->getParam("FREEDOM_CSVSEPARATOR"));
    $action->lay->set("enclosure", $action->getParam("FREEDOM_CSVENCLOSURE"));
    $action->lay->set("linebreak", $action->getParam("FREEDOM_CSVLINEBREAK"));
    
    $action->lay->Set("descr", (bool)$descr);
    $action->lay->Set("policy", (bool)$policy);
    
    $action->lay->eSet("dirid", $dirid);
}
