<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: freedom_del.php,v 1.10 2005/06/28 08:37:46 eric Exp $
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
function freedom_del(Action & $action)
{
    // -----------------------------------
    // Get all the params
    $docid = GetHttpVars("id");
    $dbaccess = $action->dbaccess;
    
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
