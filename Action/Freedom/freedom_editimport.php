<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: freedom_editimport.php,v 1.8 2008/08/14 09:59:14 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
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
    $usage->setText("Import document interface");
    $classid = $usage->addOption("classid", "family used to view schema");
    $dirid = $usage->addOption("dirid", "directory to place imported doc");
    $descr = ($usage->addOption("descr", "view information", array(
        "Y",
        "N"
    ) , "Y") == "Y");
    $policy = ($usage->addOption("policy", "view policy options", array(
        "Y",
        "N"
    ) , "Y") == "Y");
    $usage->strict(false);
    $usage->verify();
    $dbaccess = $action->GetParam("FREEDOM_DB");
    
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/subwindow.js");
    // build list of class document
    $query = new QueryDb($dbaccess, "Doc");
    $query->AddQuery("doctype='C'");
    
    $selectclass = array();
    
    $doc = new_Doc($dbaccess, $classid);
    $tclassdoc = GetClassesDoc($dbaccess, $action->user->id, $classid, "TABLE");
    
    while (list($k, $cdoc) = each($tclassdoc)) {
        $selectclass[$k]["idcdoc"] = $cdoc["initid"];
        $selectclass[$k]["classname"] = $cdoc["title"];
        if ($cdoc["initid"] == $classid) $selectclass[$k]["selected"] = "selected";
        else $selectclass[$k]["selected"] = "";
    }
    
    $action->lay->SetBlockData("SELECTCLASS", $selectclass);
    
    $lattr = $doc->GetImportAttributes();
    $format = "DOC;" . (($doc->name != "") ? $doc->name : $doc->id) . ";<special id>;<special dirid> ";
    
    $ttemp = explode(";", $format);
    $tformat = array();
    foreach ($ttemp as $k => $v) {
        $tformat[$k]["labeltext"] = htmlentities($v, ENT_COMPAT, "UTF-8");
    }
    
    foreach ($lattr as $k => $attr) {
        $format.= "; " . $attr->getLabel();
        $tformat[$k]["labeltext"] = $attr->getLabel();
    }
    
    $action->lay->set("mailaddr", getMailAddr($action->user->id));
    
    $action->lay->SetBlockData("TFORMAT", $tformat);
    
    $action->lay->Set("cols", count($tformat));
    $action->lay->Set("descr", $descr);
    $action->lay->Set("policy", $policy);
    
    $action->lay->Set("dirid", $dirid);
    
    $action->lay->Set("format", $format);
}
?>
