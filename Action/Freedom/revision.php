<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: revision.php,v 1.9 2005/06/28 08:37:46 eric Exp $
 * @package FDL
 * @subpackage GED
 */
/**
 */
// ---------------------------------------------------------------
// $Id: revision.php,v 1.9 2005/06/28 08:37:46 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/revision.php,v $
// ---------------------------------------------------------------
include_once ("FDL/Class.Doc.php");
include_once ("FDL/Class.DocAttr.php");
function revision(Action & $action)
{
    $dbaccess = $action->dbaccess;
    $docid = GetHttpVars("id", 0);
    $comment = GetHttpVars("comment", _("no comment"));
    
    $doc = new_Doc($dbaccess, $docid);
    
    $err = $doc->canEdit();
    if ($err != "") $action->ExitError($err);
    
    $err = $doc->revise($comment);
    if ($err != "") $action->ExitError($err);
    
    $action->AddLogMsg(sprintf(_("%s new revision %d") , $doc->title, $doc->revision));
    
    redirect($action, "FDL", "FDL_CARD&refreshfld=Y&id=" . $doc->id, $action->GetParam("CORE_STANDURL"));
}
