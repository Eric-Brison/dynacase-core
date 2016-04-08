<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: freedom_card.php,v 1.7 2005/06/28 08:37:46 eric Exp $
 * @package FDL
 * @subpackage GED
 */
/**
 */
// ---------------------------------------------------------------
// $Id: freedom_card.php,v 1.7 2005/06/28 08:37:46 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/freedom_card.php,v $
// ---------------------------------------------------------------
include_once ("FDL/Class.Dir.php");
// -----------------------------------
// -----------------------------------
function freedom_card(Action & $action)
{
    // -----------------------------------
    $docid = GetHttpVars("id");
    $latest = GetHttpVars("latest");
    $dbaccess = $action->dbaccess;
    $doc = new_Doc($dbaccess, $docid);
    if (!$doc->isAffected()) $action->exitError(sprintf(_("cannot see unknow reference %s") , $docid));
    
    if (($latest == "Y") && ($doc->locked == - 1)) {
        // get latest revision
        SetHttpVar("id", $doc->getLatestId());
    }
    
    $action->lay->eSet("TITLE", $doc->getTitle());
}
