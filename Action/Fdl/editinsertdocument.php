<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Interface to inser document in  folder
 *
 * @author Anakeen
 * @version $Id: editinsertdocument.php,v 1.3 2008/06/03 10:12:21 eric Exp $
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
 * @global id Http var : folder document identificator to see
 * @global famid Http var : family to use for search
 */
function editinsertdocument(&$action)
{
    
    $docid = GetHttpVars("id");
    $famid = GetHttpVars("famid");
    $dbaccess = $action->GetParam("FREEDOM_DB");
    
    if ($docid == "") $action->exitError(_("no document reference"));
    if (!is_numeric($docid)) $docid = getIdFromName($dbaccess, $docid);
    if (intval($docid) == 0) $action->exitError(sprintf(_("unknow logical reference '%s'") , GetHttpVars("id")));
    $doc = new_Doc($dbaccess, $docid);
    if (!$doc->isAffected()) $action->exitError(sprintf(_("cannot see unknow reference %s") , $docid));
    if ($doc->defDoctype != 'D') $action->exitError(sprintf(_("not a static folder %s") , $doc->title));
    $err = $doc->canModify();
    if ($err != "") $action->exitError($err);
    $action->parent->AddJsRef($action->GetParam("CORE_PUBURL") . "/FDC/Layout/inserthtml.js");
    $action->parent->AddJsRef($action->GetParam("CORE_STANDURL") . "app=FDL&action=EDITJS");
    $action->parent->AddJsRef($action->GetParam("CORE_PUBURL") . "/FDL/Layout/editinsertdocument.js");
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/resizeimg.js");
    
    $l = $doc->getContent();
    foreach ($l as $k => $v) {
        $l[$k]["icon"] = $doc->getIcon($v["icon"]);
    }
    
    $action->lay->set("restrict", false);
    
    if (!$famid) {
        if (method_exists($doc, "isAuthorized")) {
            if ($doc->isAuthorized($classid)) {
                // verify if classid is possible
                if ($doc->hasNoRestriction()) $tclassdoc = GetClassesDoc($dbaccess, $action->user->id, $classid, "TABLE");
                else {
                    $tclassdoc = $doc->getAuthorizedFamilies();
                    $action->lay->set("restrict", true);
                }
            } else {
                $tclassdoc = $doc->getAuthorizedFamilies();
                $first = current($tclassdoc);
                $famid = $first["id"];
                $action->lay->set("restrict", true);
            }
        } else {
            $tclassdoc = GetClassesDoc($dbaccess, $action->user->id, $classid, "TABLE");
        }
        $action->lay->SetBlockData("SELECTCLASS", $tclassdoc);
        
        $action->lay->SetBlockData("SELECTCLASS", $tclassdoc);
        $action->lay->set("famid", false);
    } else {
        $action->lay->set("famid", $famid);
        
        $fdoc = new_Doc($dbaccess, $famid);
        $action->lay->set("famicon", $fdoc->getIcon());
        $action->lay->set("famtitle", sprintf(_("Search %s") , $fdoc->title));
    }
    $action->lay->set("docid", $doc->id);
    $fdoc = $doc->getFamDoc();
    $action->lay->set("classtitle", $fdoc->title);
    $action->lay->set("iconsrc", $doc->getIcon());
    $action->lay->set("TITLE", sprintf(_("Content managing of %s") , $doc->title));
    $action->lay->set("version", $doc->version);
    $action->lay->set("hasstate", ($doc->getState() != ""));
    $action->lay->set("state", $doc->getState());
    $action->lay->set("statecolor", $doc->getStateColor());
    $action->lay->set("count", count($l));
    
    $action->lay->setBlockData("CONTENT", $l);
    $action->lay->set("nmembers", sprintf(_("%d documents") , count($l)));
}
?>