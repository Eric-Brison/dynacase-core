<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * View Document
 *
 * @author Anakeen
 * @version $Id: fdl_getvalue.php,v 1.1 2005/07/28 16:47:51 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.Dir.php");
/**
 * View a document
 * @param Action &$action current action
 * @global string $docid Http var : document identifier to see
 * @global string $latest Http var : (Y|N) if Y force view latest revision
 * @global string $attrid Http var : the attribute id to see
 */
function fdl_getvalue(Action & $action)
{
    // -----------------------------------
    $docid = $action->getArgument("id");
    $latest = $action->getArgument("latest", "Y");
    $attrid = $action->getArgument("attrid");
    $dbaccess = $action->GetParam("FREEDOM_DB");
    
    if ($docid == "") $action->exitError(_("no document reference"));
    if (!is_numeric($docid)) $docid = getIdFromName($dbaccess, $docid);
    if (intval($docid) == 0) $action->exitError(sprintf(_("unknow logical reference '%s'") , $docid));
    $doc = new_Doc($dbaccess, $docid);
    if (!$doc->isAffected()) $action->exitError(sprintf(_("cannot see unknow reference %s") , $docid));
    
    if (($latest == "Y") && ($doc->locked == - 1)) {
        // get latest revision
        $docid = $doc->latestId();
        $doc = new_Doc($dbaccess, $docid);
    }
    $err = $doc->control("view");
    if ($err != "") $action->exitError($err);
    
    $a = $doc->getAttribute($attrid);
    if ($a) {
        if ($a->mvisibility != "I") $v = $doc->getValue($attrid);
        else $v = sprintf("no privilege to access attribute [%s] for document %s |%d]", $attrid, $doc->title, $doc->id);
    } else {
        $v = sprintf("unknown attribute [%s] for document %s |%d]", $attrid, $doc->title, $doc->id);
    }
    
    $action->lay->template = $v;
    $action->lay->noparse = true;
}
?>
