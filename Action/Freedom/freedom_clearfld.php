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
 * @version $Id: freedom_clearfld.php,v 1.3 2005/06/28 08:37:46 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage GED
 */
/**
 */
// ---------------------------------------------------------------
// $Id: freedom_clearfld.php,v 1.3 2005/06/28 08:37:46 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/freedom_clearfld.php,v $
// ---------------------------------------------------------------
// ==========================================================================
// unreference all document in the folder
// ==========================================================================
include_once ("FDL/Lib.Dir.php");
include_once ("FDL/freedom_util.php");
// -----------------------------------
function freedom_clearfld(&$action)
{
    // -----------------------------------
    // insert the documents of $dirid in folder $id
    //    PrintAllHttpVars();
    // Get all the params
    $docid = GetHttpVars("id");
    $mode = GetHttpVars("mode", "latest");
    $return = GetHttpVars("return"); // return action may be folio
    $dbaccess = $action->GetParam("FREEDOM_DB");
    
    $doc = new_Doc($dbaccess, $docid);
    $err = $doc->Clear();
    
    if ($err != "") $action->exitError($err);
    
    redirect($action, "FREEDOM", "FREEDOM_VIEW&dirid=$docid");
}
?>
