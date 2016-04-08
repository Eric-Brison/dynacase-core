<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: querytitle.php,v 1.4 2005/06/28 08:37:46 eric Exp $
 * @package FDL
 * @subpackage GED
 */
/**
 */
// ---------------------------------------------------------------
// $Id: querytitle.php,v 1.4 2005/06/28 08:37:46 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/querytitle.php,v $
// ---------------------------------------------------------------
include_once ("FDL/Class.Doc.php");
include_once ("FDL/Class.DocAttr.php");

function querytitle(Action & $action)
{
    $dbaccess = $action->dbaccess;
    $docid = GetHttpVars("id", 0);
    
    $doc = new_Doc($dbaccess, $docid);
    $action->lay->eSet("APP_TITLE", _($action->parent->description));
    $action->lay->Set("docid", $doc->id);
    $action->lay->eSet("title", $doc->title);
    $action->lay->Set("iconsrc", $doc->geticon());
}
