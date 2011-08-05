<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * View Document
 *
 * @author Anakeen 2000
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
 * @global docid Http var : document identificator to see
 * @global latest Http var : (Y|N) if Y force view latest revision
 * @global attrid Http var : the attribute id to see
 * @global vid Http var : if set, view represention describe in view control (can be use only if doc has controlled view)
 */
function fdl_getvalue(&$action)
{
    // -----------------------------------
    $docid = GetHttpVars("id");
    $latest = GetHttpVars("latest", "Y");
    $attrid = GetHttpVars("attrid");
    $dbaccess = $action->GetParam("FREEDOM_DB");
    
    if ($docid == "") $action->exitError(_("no document reference"));
    if (!is_numeric($docid)) $docid = getIdFromName($dbaccess, $docid);
    if (intval($docid) == 0) $action->exitError(sprintf(_("unknow logical reference '%s'") , GetHttpVars("id")));
    $doc = new_Doc($dbaccess, $docid);
    if (!$doc->isAffected()) $action->exitError(sprintf(_("cannot see unknow reference %s") , $docid));
    
    if (($latest == "Y") && ($doc->locked == - 1)) {
        // get latest revision
        $docid = $doc->latestId();
        $doc = new_Doc($dbaccess, $docid);
    }
    $err = $doc->control("view");
    if ($err != "") $action->exitError($err);
    
    if (($vid != "") && ($doc->cvid > 0)) {
        // special controlled view
        $cvdoc = new_Doc($dbaccess, $doc->cvid);
        $cvdoc->set($doc);
        
        $err = $cvdoc->control($vid); // control special view
        if ($err != "") $action->exitError($err);
        
        $tview = $cvdoc->getView($vid);
        $doc->setMask($tview["CV_MSKID"]);
    }
    
    $a = $doc->getAttribute($attrid);
    if ($a) {
        if ($doc->mvisibility != "I") $v = $doc->getValue($attrid);
        else $v = sprintf("no privilege to access attribute [%s] for document %s |%d]", $attrid, $doc->title, $doc->id);
    } else {
        $v = sprintf("unknown attribute [%s] for document %s |%d]", $attrid, $doc->title, $doc->id);
    }
    
    $action->lay = new Layout();
    $action->lay->set("OUT", $v);
}
?>
