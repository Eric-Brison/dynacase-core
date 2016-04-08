<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * View standalone document (without popup menu)
 *
 * @author Anakeen
 * @version $Id: viewscard.php,v 1.8 2005/11/04 15:38:29 marc Exp $
 * @package FDL
 * @subpackage
 */
/**
 */
// ---------------------------------------------------------------
// $Id: viewscard.php,v 1.8 2005/11/04 15:38:29 marc Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Fdl/viewscard.php,v $
// ---------------------------------------------------------------
include_once ("FDL/Class.Doc.php");
/**
 * View a document without standard header and footer. It is display in raw format
 * @param Action &$action current action
 * @global id int Http var : document identifier to see
 * @global latest string Http var : (Y|N) if Y force view latest revision
 * @global abstract string Http var : (Y|N) if Y view only abstract attribute
 * @global zonebodycard string Http var : if set, view other specific representation
 * @global vid int Http var : if set, view represention describe in view control (can be use only if doc has controlled view)
 * @global ulink string Http var : (Y|N)if N hyperlink are disabled
 * @global target string Http var : is set target of hyperlink can change (default _self)
 */
function viewscard(&$action)
{
    // GetAllParameters
    $usage = new ActionUsage($action);
    $usage->setStrictMode(false);
    $docid = $usage->addRequiredParameter("id", "id of the document");
    $abstract = ($usage->addOptionalParameter("abstract", "view only abstract's attributes", array(
        "Y",
        "N"
    ) , "N") == "Y");
    $zonebodycard = $usage->addOptionalParameter("zone", "view with another specific representation", null, "");
    $ulink = ($usage->addOptionalParameter("ulink", "enable or disable hyperlink", array(
        "Y",
        "N",
        ""
    ) , "Y") !== "N");
    $target = $usage->addOptionalParameter("target", "target for hyperlinks ('mail', '_self', etc.)", null, "");
    $wedit = ($usage->addOptionalParameter("wedit", "view by word editor", array(
        "Y",
        "N"
    ) , "N") == "Y");
    $fromedit = ($usage->addOptionalParameter("fromedit", "compose temporary document", array(
        "Y",
        "N"
    ) , "N") == "Y");
    $latest = $usage->addOptionalParameter("latest", "view latest revision of document", null, "");
    $tmime = $usage->addOptionalParameter("tmime", "MIME type", null, "");
    $charset = $usage->addOptionalParameter("chset", "charset", null, "UTF-8");
    $usage->verify();
    // Set the globals elements
    $dbaccess = $action->dbaccess;
    
    $doc = new_Doc($dbaccess, $docid);
    if (($latest == "Y") && ($doc->locked == - 1)) {
        // get latest revision
        $docid = $doc->getLatestId();
        $doc = new_Doc($dbaccess, $docid);
        SetHttpVar("id", $docid);
    }
    $err = $doc->control("view");
    if ($err != "") $action->exitError($err);
    if ($fromedit) {
        include_once ("FDL/modcard.php");
        
        $doc = $doc->duplicate(true, false, true);
        $err = setPostVars($doc);
        $doc->modify();
        setHttpVar("id", $doc->id);
    }
    if ($zonebodycard == "") $zonebodycard = $doc->defaultview;
    if ($zonebodycard == "") $action->exitError(_("no zone specified"));
    
    $err = $doc->refresh();
    $action->lay->Set("ZONESCARD", $doc->viewDoc($zonebodycard, $target, $ulink, $abstract));
    
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/geometry.js");
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/subwindow.js");
    
    if ($wedit) {
        $export_file = uniqid(getTmpDir() . "/export") . ".doc";
        
        $of = fopen($export_file, "w+");
        fwrite($of, $action->lay->gen());
        fclose($of);
        
        http_DownloadFile($export_file, chop($doc->title) . ".html", "application/msword");
        
        unlink($export_file);
        exit;
    }
    
    if ($tmime != "") {
        header("Content-Type: $tmime; charset=$charset");
        print $action->lay->gen();
        exit;
    }
}
