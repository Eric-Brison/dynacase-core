<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Display interface to create a new family
 *
 * @author Anakeen
 * @version $Id: createfam.php,v 1.1 2006/03/31 12:29:30 eric Exp $
 * @package FDL
 * @subpackage GED
 */
/**
 */

include_once ("FDL/Lib.Dir.php");
include_once ("FDL/Class.Doc.php");
include_once ("FDL/Class.DocAttr.php");

function createfam(Action & $action)
{
    $dbaccess = $action->dbaccess;
    $docid = GetHttpVars("id", 0);
    $classid = GetHttpVars("classid", 0); // use when new doc or change class
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/geometry.js");
    
    $doc = new_Doc($dbaccess, $docid);
    $action->lay->Set("docid", $doc->id);
    
    $action->lay->eSet("TITLE", _("creation of document family"));
    $action->lay->eSet("ftitle", _("untitle family"));
    // when modification
    if (($classid == 0) && ($docid != 0)) $classid = $doc->fromid;
    else
    // to show inherit attributes
    if (($docid == 0) && ($classid > 0)) $doc = new_Doc($dbaccess, $classid); // the doc inherit from chosen class
    $selectclass = array();
    $tclassdoc = GetClassesDoc($dbaccess, $action->user->id, $classid, "TABLE");
    foreach ($tclassdoc as $k => $cdoc) {
        $selectclass[$k]["idcdoc"] = $cdoc["id"];
        $selectclass[$k]["classname"] = $cdoc["title"];
        $selectclass[$k]["selected"] = "";
    }
    
    $nbattr = 0; // if new document
    // display current values
    $newelem = array();
    if ($docid > 0) {
        // control if user can update
        $err = $doc->CanLockFile();
        if ($err != "") $action->ExitError($err);
        $action->lay->Set("TITLE", $doc->title);
    }
    if (($classid > 0) || ($doc->doctype = 'C')) {
        // selected the current class document
        foreach ($selectclass as $k => $cdoc) {
            
            if ($classid == $selectclass[$k]["idcdoc"]) {
                
                $selectclass[$k]["selected"] = "selected";
            }
        }
        
        $action->lay->SetBlockData("SELECTCLASS", $selectclass);
    }
}
