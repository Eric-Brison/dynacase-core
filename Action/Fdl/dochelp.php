<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * View Help Document
 *
 * @author Anakeen
 * @version $Id: family_help.php,v 1.4 2007/09/04 09:09:10 eric Exp $
 * @package FDL
 */
/**
 */

include_once ("Lib.Http.php");
include_once ("FDL/Class.Doc.php");

function dochelp(Action & $action)
{
    
    $docid = $action->getArgument("id");
    $anchor = $action->getArgument("anchor");
    $dbaccess = $action->dbaccess;
    $doc = new_doc($dbaccess, $docid);
    if (!$doc->isAlive()) {
    }
    
    redirect($action, "FDL", "IMPCARD&id=$docid#$anchor");
}
