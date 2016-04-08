<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Interface to inser document in  folder
 *
 * @author Anakeen
 * @version $Id: editinsertdocument.php,v 1.3 2008/06/03 10:12:21 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.Dir.php");
/**
 * View a document
 * @param Action &$action current action
 * @global string $id Http var : folder document identifier to see
 * @global string $famid Http var : family to use for search
 */
function editinsertdocument(Action & $action)
{
    
    $docid = GetHttpVars("id");
    $famid = GetHttpVars("famid");
    
    if ($docid == "") $action->exitError(_("no document reference"));
    if (!is_numeric($docid)) $docid = getIdFromName($action->dbaccess, $docid);
    if (intval($docid) == 0) $action->exitError(sprintf(_("unknow logical reference '%s'") , GetHttpVars("id")));
    /**
     * @var Dir $doc
     */
    $doc = new_Doc($action->dbaccess, $docid);
    if (!$doc->isAffected()) $action->exitError(sprintf(_("cannot see unknow reference %s") , $docid));
    if ($doc->defDoctype != 'D') $action->exitError(sprintf(_("not a static folder %s") , $doc->title));
    $err = $doc->canModify();
    if ($err != "") $action->exitError($err);
    $action->parent->addJsRef('lib/jquery/jquery.js');
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
        $classid = 0;
        if (method_exists($doc, "isAuthorized")) {
            if ($doc->isAuthorized($classid)) {
                // verify if classid is possible
                if ($doc->hasNoRestriction()) $tclassdoc = GetClassesDoc($action->dbaccess, $action->user->id, $classid, "TABLE");
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
            $tclassdoc = GetClassesDoc($action->dbaccess, $action->user->id, $classid, "TABLE");
        }
        $action->lay->SetBlockData("SELECTCLASS", $tclassdoc);
        
        $action->lay->SetBlockData("SELECTCLASS", $tclassdoc);
        $action->lay->set("famid", false);
    } else {
        $fdoc = new_Doc($action->dbaccess, $famid);
        $action->lay->set("famid", $fdoc->id);
        $action->lay->set("famicon", $fdoc->getIcon());
        $action->lay->set("famtitle", sprintf(_("Search %s") , $fdoc->title));
    }
    $action->lay->set("docid", $doc->id);
    $fdoc = $doc->getFamilyDocument();
    $action->lay->eset("classtitle", $fdoc->title);
    $action->lay->set("iconsrc", $doc->getIcon());
    $action->lay->eset("TITLE", sprintf(_("Content managing of %s") , $doc->title));
    $action->lay->eset("version", $doc->version);
    $action->lay->set("hasstate", ($doc->getState() != ""));
    $action->lay->eset("state", $doc->getState());
    $action->lay->set("statecolor", $doc->getStateColor());
    $action->lay->set("count", count($l));
    
    $action->lay->eSetBlockData("CONTENT", $l);
    $action->lay->set("nmembers", sprintf(_("%d documents") , count($l)));
}
