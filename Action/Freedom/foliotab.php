<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * View tab in portfolio
 *
 * @author Anakeen
 * @version $Id: foliotab.php,v 1.9 2008/01/22 16:42:49 eric Exp $
 * @package FDL
 * @subpackage GED
 */
/**
 */

include_once ("FDL/Lib.Dir.php");
include_once ("FDL/freedom_util.php");
include_once ('FREEDOM/Lib.portfolio.php');
// -----------------------------------
function foliotab(Action & $action)
{
    // -----------------------------------
    // Get all the params
    $docid = GetHttpVars("id", 0); // portfolio id
    include_once ("FDL/popup_util.php");
    $action->parent->AddJsRef($action->GetParam("CORE_PUBURL") . "/FDL/Layout/common.js");
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/subwindow.js");
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/AnchorPosition.js");
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/resizeimg.js");
    
    $doc = new_Doc($action->dbaccess, $docid);
    if (!$doc->isAffected()) $action->exitError(sprintf(_("document %s not exists") , $docid));
    $action->lay->set("docid", $doc->id);
    $action->lay->set("dirid", $doc->initid);
    $action->lay->eSet("title", $doc->title);
    
    $child = getChildDir($action->dbaccess, $action->user->id, $doc->initid, false, "TABLE");
    
    if ($action->Read("navigator") == "EXPLORER") { // different tab class for PNG transparency
        $tabonglet = "ongletvgie";
        $tabongletsel = "ongletvsie";
    } else {
        $tabonglet = "ongletvg";
        $tabongletsel = "ongletvs";
    }
    
    $linktab = $doc->getFamilyParameterValue("pfl_idlinktab");
    if ($linktab) {
        $linktab = $doc->rawValueToArray($linktab);
        foreach ($linktab as $k => $id) {
            $tdoc = getTDoc($action->dbaccess, $id);
            if (controlTdoc($tdoc, "view")) $child[] = $tdoc;
        }
    }
    
    $action->lay->set("tabonglets", $tabongletsel);
    $action->lay->set("icon", $doc->getIcon());
    $ttag = array();
    foreach ($child as $v) {
        $icolor = getv($v, "gui_color");
        if ($v["initid"] != $doc->initid) {
            $ttag[$v["initid"]] = array(
                "tabid" => $v["initid"],
                "doctype" => $v["doctype"],
                "TAG_LABELCLASS" => $v["doctype"] == "S" ? "searchtab" : "",
                "tag_cellbgclass" => ($v["id"] == $docid) ? $tabongletsel : $tabonglet,
                "icolor" => $icolor,
                "icontab" => $doc->getIcon($v["icon"]) ,
                "tabtitle" => htmlspecialchars(str_replace(" ", "\xC2\xA0" /* UTF-8 for NO-BREAK SPACE */
                , $v["title"]) , ENT_QUOTES)
            );
        }
    }
    
    $action->lay->setBlockData("TAG", $ttag);
    $action->lay->setBlockData("nbcol", count($ttag) + 1);
}
