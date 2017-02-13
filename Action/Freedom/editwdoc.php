<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: editwdoc.php,v 1.4 2005/06/28 08:37:46 eric Exp $
 * @package FDL
 * @subpackage GED
 */
/**
 */
// ---------------------------------------------------------------
// $Id: editwdoc.php,v 1.4 2005/06/28 08:37:46 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/editwdoc.php,v $
// ---------------------------------------------------------------
include_once ("FDL/Lib.Dir.php");

function editwdoc(Action & $action)
{
    $dbaccess = $action->dbaccess;
    $docid = GetHttpVars("id", 0);
    
    $doc = new_Doc($dbaccess, $docid);
    $action->lay->Set("docid", $doc->id);
    
    $action->lay->eSet("doctitle", $doc->getTitle());
    $sqlfilters = array();
    
    $wid = $doc->wid;
    
    $chdoc = $doc->GetFromDoc();
    $sqlfilters[] = "(" . GetSqlCond($chdoc, "wf_famid") . ") OR (wf_famid isnull)";
    $tclassdoc = internalGetDocCollection($dbaccess, 0, "0", "ALL", $sqlfilters, $action->user->id, "TABLE", "WDOC");
    
    $selectclass = array();
    if (is_array($tclassdoc)) {
        foreach ($tclassdoc as $k => $pdoc) {
            
            $selectclass[$k]["idpdoc"] = $pdoc["id"];
            $selectclass[$k]["profname"] = $pdoc["title"];
            
            $selectclass[$k]["selected"] = ($pdoc["id"] == $wid) ? "selected" : "";
        }
    }
    
    $action->lay->SetBlockData("SELECTFLD", $selectclass);
}
