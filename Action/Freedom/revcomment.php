<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: revcomment.php,v 1.5 2005/06/28 08:37:46 eric Exp $
 * @package FDL
 * @subpackage GED
 */
/**
 */
// ---------------------------------------------------------------
// $Id: revcomment.php,v 1.5 2005/06/28 08:37:46 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/revcomment.php,v $
// ---------------------------------------------------------------
include_once ("FDL/Class.Doc.php");
include_once ("FDL/Class.DocAttr.php");
function revcomment(Action & $action)
{
    $dbaccess = $action->dbaccess;
    $docid = GetHttpVars("id", 0);
    
    $doc = new_Doc($dbaccess, $docid);
    
    $err = $doc->lock(true); // auto lock
    if ($err != "") $action->ExitError($err);
    
    $err = $doc->canEdit();
    if ($err != "") $action->ExitError($err);
    
    $action->lay->eSet("APP_TITLE", _($action->parent->description));
    $action->lay->eSet("title", $doc->getTitle());
    $action->lay->eSet("docid", (int)$doc->id);
}
