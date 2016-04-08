<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Historique view
 *
 * @author Anakeen
 * @version $Id: histo.php,v 1.7 2005/06/28 08:37:46 eric Exp $
 * @package FDL
 * @subpackage GED
 */
/**
 */

include_once ("FDL/Class.Doc.php");
function histo(Action & $action)
{
    $dbaccess = $action->dbaccess;
    $docid = GetHttpVars("id", 0);
    
    $doc = new_Doc($dbaccess, $docid);
    $action->lay->eSet("title", $doc->getTitle());
}
