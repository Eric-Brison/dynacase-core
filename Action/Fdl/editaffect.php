<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Edition to affect document
 *
 * @author Anakeen
 * @version $Id: editaffect.php,v 1.6 2007/01/15 14:39:46 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.Doc.php");
include_once ("FDL/Lib.Dir.php");
include_once ("FDL/editutil.php");
// -----------------------------------
// -----------------------------------

/**
 * Edition to affect document
 * @param Action &$action current action
 * @global id int Http var : document id to affect
 * @global viewdoc string Http var : with preview of affect document [Y|N]
 */
function editaffect(&$action)
{
    $docid = GetHttpVars("id");
    $viewdoc = (GetHttpVars("viewdoc", "N") == "Y");
    $dbaccess = $action->dbaccess;
    
    $doc = new_doc($dbaccess, $docid);
    editmode($action);
    
    $action->lay->Set("id", $doc->id);
    $action->lay->Set("title", $doc->getHTMLTitle());
    $action->lay->set("VIEWDOC", $viewdoc);
    $action->lay->eset("affecttitle", sprintf(_("Affectation for %s") , $doc->getTitle()));
    // search free states
    $sqlfilters = array(
        "(frst_famid='" . $doc->fromid . "') or (frst_famid is null) or (frst_famid='')"
    );
    $tfree = internalGetDocCollection($dbaccess, 0, "0", "ALL", $sqlfilters, $action->user->id, "TABLE", "FREESTATE");
    $tstate = array();
    if ($doc->wid == 0) {
        foreach ($tfree as $k => $v) {
            $tstate[] = array(
                "fstate" => $v["initid"],
                "lstate" => $v["title"],
                "color" => getv($v, "frst_color") ,
                "dstate" => nl2br(getv($v, "frst_desc"))
            );
        }
    }
    $action->lay->set("viewstate", ($doc->wid == 0));
    $state = $doc->getState();
    if ($state) {
        $action->lay->eset("textstate", sprintf(_("From %s state to") , $state));
        $action->lay->set("colorstate", $doc->getStateColor("transparent"));
    } else {
        $action->lay->eset("textstate", _("New state"));
        $action->lay->set("colorstate", "transparent");
    }
    
    $action->lay->eSetBlockData("freestate", $tstate);
}
