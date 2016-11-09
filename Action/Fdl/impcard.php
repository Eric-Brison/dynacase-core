<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * View document only - without any menu
 *
 * @author Anakeen
 * @version $Id: impcard.php,v 1.11 2008/02/08 09:50:26 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.Doc.php");

function impcard(Action & $action)
{
    // GetAllParameters
    $usage = new ActionUsage($action);
    $usage->setDefinitionText("view document in HTML page");
    $docid = $usage->addRequiredParameter("id", "document identifier");
    $mime = $usage->addOptionalParameter("mime", "other mime type header to send");
    $ext = $usage->addOptionalParameter("ext", "file extension if inline is no", null, "html");
    $inline = $usage->addOptionalParameter("inline", "inline (yes|no) - if set to 'no', mime must be set also");
    $inline = (strtolower(substr($inline, 0, 1)) == "y"); // view file inline
    $zonebodycard = $usage->addOptionalParameter("zone", "special document view zone");
    $vid = $usage->addOptionalParameter("vid", "special controlled view");
    $state = $usage->addOptionalParameter("state", "search doc in this state");
    $latest = $usage->addOptionalParameter("latest", "get document in latest version if Y", array(
        "Y",
        "N",
        "L",
        "P"
    ));
    $view = $usage->addOptionalParameter("view", "add view css print", array(
        "print"
    ));
    
    $usage->setStrictMode(false);
    $usage->verify();
    $szone = false;
    
    $dbaccess = $action->dbaccess;
    $action->lay->rSet("viewprint", ($view == "print"));
    
    $doc = new_Doc($dbaccess, $docid);
    
    $action->parent->addCssRef("css/dcp/main.css");
    if ($state != "") {
        $docid = $doc->getRevisionState($state, true);
        if ($docid == 0) {
            $action->exitError(sprintf(_("Document %s in %s state not found") , $doc->title, _($state)));
        }
        SetHttpVar("id", $docid);
    } else {
        if (($latest == "Y") && ($doc->locked == - 1)) {
            // get latest revision
            $docid = $doc->getLatestId();
            SetHttpVar("id", $docid);
        } else if (($latest == "L") && ($doc->lmodify != 'L')) {
            // get latest fixed revision
            $docid = $doc->getLatestId(true);
            SetHttpVar("id", $docid);
        } else if (($latest == "P") && ($doc->revision > 0)) {
            // get previous fixed revision
            $pdoc = getRevTDoc($dbaccess, $doc->initid, $doc->revision - 1);
            $docid = $pdoc["id"];
            SetHttpVar("id", $docid);
        }
    }
    $action->lay->eset("TITLE", $doc->getTitle());
    if (($zonebodycard == "") && ($vid != "")) {
        /**
         * @var \Dcp\Family\CVDOC $cvdoc
         */
        $cvdoc = new_Doc($dbaccess, $doc->cvid);
        $tview = $cvdoc->getView($vid);
        $zonebodycard = $tview["CV_ZVIEW"];
    }
    if ($zonebodycard == "") $zonebodycard = $doc->defaultview;
    if ($zonebodycard == "") $zonebodycard = "FDL:VIEWCARD";
    
    $zo = $doc->getZoneOption($zonebodycard);
    if ($zo == "B") {
        // binary layout file
        $ulink = false;
        $target = "ooo";
        $file = $doc->viewdoc($zonebodycard, $target, $ulink);
        Http_DownloadFile($file, $doc->title . ".odt", 'application/vnd.oasis.opendocument.text', false, false);
        @unlink($file);
        exit;
    }
    
    if ($zo == 'S') $szone = true; // the zonebodycard is a standalone zone ?
    $action->lay->rSet("nocss", ($zo == "U"));
    if ($szone) {
        // change layout
        include_once ("FDL/viewscard.php");
        $action->lay = new Layout(getLayoutFile("FDL", "viewscard.xml") , $action);
        viewscard($action);
    }
    
    if ($mime != "") {
        $export_file = uniqid(getTmpDir() . "/export") . ".$ext";
        
        $of = fopen($export_file, "w+");
        fwrite($of, $action->lay->gen());
        fclose($of);
        http_DownloadFile($export_file, chop($doc->title) . ".$ext", "$mime", $inline, false);
        
        unlink($export_file);
        exit;
    }
}
