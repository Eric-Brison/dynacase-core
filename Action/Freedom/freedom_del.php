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
 * @version $Id: freedom_del.php,v 1.10 2005/06/28 08:37:46 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage GED
 */
/**
 */
// ---------------------------------------------------------------
// $Id: freedom_del.php,v 1.10 2005/06/28 08:37:46 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/freedom_del.php,v $
// ---------------------------------------------------------------
include_once ("FDL/Class.Doc.php");
include_once ("FDL/Class.DocAttr.php");
include_once ("FDL/freedom_util.php");
// -----------------------------------
function freedom_del(&$action)
{
    // -----------------------------------
    // Get all the params
    $docid = GetHttpVars("id");
    $dbaccess = $action->GetParam("FREEDOM_DB");
    
    if ($docid == "") return;
    
    $doc = new_Doc($dbaccess, $docid);
    // must unlocked before
    $err = $doc->CanLockFile();
    if ($err != "") $action->ExitError($err);
    // ------------------------------
    // delete POSGRES card
    $err = $doc->Delete();
    if ($err != "") $action->ExitError($err);
    
    $action->AddLogMsg(sprintf(_("%s has been deleted") , $doc->title));
    
    redirect($action, "FDL", "FDL_CARD&id=$docid");
}
?>
