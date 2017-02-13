<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: editdfld.php,v 1.8 2008/11/27 14:18:33 eric Exp $
 * @package FDL
 * @subpackage GED
 */
/**
 */
// ---------------------------------------------------------------
// $Id: editdfld.php,v 1.8 2008/11/27 14:18:33 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/editdfld.php,v $
// ---------------------------------------------------------------
include_once ("FDL/Lib.Dir.php");

function editdfld(Action & $action)
{
    $dbaccess = $action->dbaccess;
    $docid = GetHttpVars("id", 0);
    $firstfld = (GetHttpVars("current", "N") == "Y");
    
    $action->lay->eSet("TITLE", _("change root folder"));
    /**
     * @var DocFam $doc
     */
    $doc = new_Doc($dbaccess, $docid);
    $action->lay->rSet("docid", $doc->id);
    
    $action->lay->eSet("doctitle", $doc->getTitle());
    $sqlfilters = array();
    if ($firstfld) {
        $fldid = $doc->cfldid;
        $action->lay->eSet("TITLE", _("Change default search"));
        $action->lay->rSet("current", "Y");
        
        $tclassdoc = internalGetDocCollection($dbaccess, $doc->dfldid, "0", "ALL", $sqlfilters, $action->user->id, "TABLE", 5);
        //$tclassdoc = array_merge($tclassdoc,getChildDoc($dbaccess,$doc->dfldid,"0","ALL",$sqlfilters, $action->user->id, "TABLE",2));
        
    } else {
        $fldid = $doc->dfldid;
        $action->lay->eSet("TITLE", _("change root folder"));
        $action->lay->rSet("current", "N");
        $sqlfilters[] = "doctype='D'";
        $tclassdoc = internalGetDocCollection($dbaccess, 0, "0", "ALL", $sqlfilters, $action->user->id, "TABLE", 2);
    }
    
    $selectclass = array();
    if (is_array($tclassdoc)) {
        foreach ($tclassdoc as $k => $pdoc) {
            
            $selectclass[$k]["idpdoc"] = $pdoc["id"];
            $selectclass[$k]["profname"] = $pdoc["title"];
            
            $selectclass[$k]["selected"] = ($pdoc["id"] == $fldid) ? "selected" : "";
        }
    }
    
    $action->lay->rSet("autodisabled", $firstfld || ($fldid > 0) ? "disabled" : "");
    $action->lay->rSet("ROOTFOLDER", (!$firstfld));
    
    $action->lay->eSetBlockData("SELECTFLD", $selectclass);
}
