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
 * @version $Id: edit_search.php,v 1.7 2005/02/08 11:34:37 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage GED
 */
/**
 */
// ---------------------------------------------------------------
// $Id: edit_search.php,v 1.7 2005/02/08 11:34:37 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/edit_search.php,v $
// ---------------------------------------------------------------

include_once ("FDL/Lib.Dir.php");
// -----------------------------------
function edit_search(Action &$action)
{
    // -----------------------------------
    $dbaccess = $action->GetParam("FREEDOM_DB");
    // Get all the params
    $dir = GetHttpVars("dirid"); // insert search in this folder
    $action->lay->eSet("dirid", $dir);
    
    $tclassdoc = GetClassesDoc($dbaccess, $action->user->id, 0, "TABLE");
    $selectclass=array();
    while (list($k, $cdoc) = each($tclassdoc)) {
        $selectclass[$k]["idcdoc"] = $cdoc["initid"];
        $selectclass[$k]["classname"] = $cdoc["title"];
    }
    
    $action->lay->SetBlockData("SELECTCLASS", $selectclass);
}
?>