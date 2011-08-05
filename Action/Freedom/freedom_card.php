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
 * @version $Id: freedom_card.php,v 1.7 2005/06/28 08:37:46 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
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
function freedom_card(&$action)
{
    // -----------------------------------
    $docid = GetHttpVars("id");
    $latest = GetHttpVars("latest");
    $dbaccess = $action->GetParam("FREEDOM_DB");
    $doc = new_Doc($dbaccess, $docid);
    if (!$doc->isAffected()) $action->exitError(sprintf(_("cannot see unknow reference %s") , $docid));
    
    if (($latest == "Y") && ($doc->locked == - 1)) {
        // get latest revision
        SetHttpVar("id", $doc->latestId());
    }
    
    $action->lay->Set("TITLE", $doc->title);
}
?>
