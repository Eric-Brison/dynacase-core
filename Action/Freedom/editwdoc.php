<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000
 * @version $Id: editwdoc.php,v 1.4 2005/06/28 08:37:46 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage GED
 */
/**
 */
// ---------------------------------------------------------------
// $Id: editwdoc.php,v 1.4 2005/06/28 08:37:46 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/editwdoc.php,v $
// ---------------------------------------------------------------

include_once ("FDL/Lib.Dir.php");

function editwdoc(&$action)
{
    $dbaccess = $action->GetParam("FREEDOM_DB");
    $docid = GetHttpVars("id", 0);
    $current = (GetHttpVars("current", "N") == "Y");
    
    $action->lay->Set("docid", $docid);
    
    $doc = new_Doc($dbaccess, $docid);
    
    $action->lay->Set("doctitle", $doc->title);
    $sqlfilters = array();
    
    $wid = $doc->wid;
    
    $chdoc = $doc->GetFromDoc();
    $sqlfilters[] = "(" . GetSqlCond($chdoc, "wf_famid") . ") OR (wf_famid isnull)";
    $tclassdoc = getChildDoc($dbaccess, 0, "0", "ALL", $sqlfilters, $action->user->id, "TABLE", "WDOC");
    
    $selectclass = array();
    if (is_array($tclassdoc)) {
        while (list($k, $pdoc) = each($tclassdoc)) {
            
            $selectclass[$k]["idpdoc"] = $pdoc["id"];
            $selectclass[$k]["profname"] = $pdoc["title"];
            
            $selectclass[$k]["selected"] = ($pdoc["id"] == $wid) ? "selected" : "";
        }
    }
    
    $action->lay->SetBlockData("SELECTFLD", $selectclass);
}
?>
