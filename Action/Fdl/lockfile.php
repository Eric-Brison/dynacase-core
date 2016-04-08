<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Lock a document
 *
 * @author Anakeen
 * @version $Id: lockfile.php,v 1.6 2006/04/28 14:33:39 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.Doc.php");
function lockfile(Action & $action)
{
    
    $dbaccess = $action->dbaccess;
    $docid = GetHttpVars("id", 0);
    
    $doc = new_Doc($dbaccess, $docid);
    
    $err = $doc->lock();
    if ($err != "") $action->ExitError($err);
    
    $action->AddActionDone("LOCKDOC", $doc->id);
    $action->AddLogMsg(sprintf(_("%s has been locked") , $doc->title));
    // add events for  folders
    $fdlids = $doc->getParentFolderIds();
    foreach ($fdlids as $fldid) {
        $action->AddActionDone("MODFOLDERCONTAINT", $fldid);
    }
    
    redirect($action, "FDL", "FDL_CARD&id=" . $doc->id, $action->GetParam("CORE_STANDURL"));
}
