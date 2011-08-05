<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * View family schema for import
 *
 * @author Anakeen 2000
 * @version $Id: fdl_css.php,v 1.6 2007/11/08 15:53:59 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */
/**
 * View scheam family
 * @param Action &$action current action
 * @global id Http var : family identificator to see
 * @global latest Http var : (Y|N|L|P) if Y force view latest revision, L : latest fixed revision, P : previous revision
 * @global state Http var : to view document in latest fixed state (only if revision > 0)
 * @global abstract Http var : (Y|N) if Y view only abstract attribute
 * @global props Http var : (Y|N) if Y view properties also
 * @global zonebodycard Http var : if set, view other specific representation
 * @global vid Http var : if set, view represention describe in view control (can be use only if doc has controlled view)
 * @global ulink Http var : (Y|N)if N hyperlink are disabled
 * @global target Http var : is set target of hyperlink can change (default _self)
 * @global inline Http var : (Y|N) set to Y for binary template. View in navigator
 * @global reload Http var : (Y|N) if Y update freedom folders in client navigator
 * @global dochead Http var :  (Y|N) if N don't see head of document (not title and icon)
 */
include_once ("FDL/Class.Doc.php");
// -----------------------------------
function fdl_familyschema(&$action)
{
    // -----------------------------------
    $docid = getHttpVars("id");
    $dbaccess = $action->getParam("FREEDOM_DB");
    
    $doc = new_Doc($dbaccess, $docid);
    if (!$doc->isAlive()) $action->exitError(_("no family identificator"));
    if ($doc->doctype != 'C') $action->exitError(sprintf(_("document %s is not a family") , $docid));
    
    header('Content-Type: text/xml; charset="utf-8"');
    $action->lay->noparse = true;
    $action->lay->template = $doc->getXmlSchema();
}
?>