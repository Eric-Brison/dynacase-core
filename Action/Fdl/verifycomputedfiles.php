<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 *
 * @author Anakeen
 * @version $Id: verifycomputedfiles.php,v 1.5 2008/04/14 10:12:14 eric Exp $
 * @package FDL
 * @subpackage
 */

include_once "FDL/Class.Doc.php";
/**
 * Verify if a file has been computed
 * @param Action &$action current action
 */
function verifycomputedfiles(Action & $action)
{
    $usage = new ActionUsage($action);
    
    $docid = $usage->addRequiredParameter("id", "docid");
    
    $dbaccess = $action->dbaccess;
    
    header('Content-type: text/xml; charset=utf-8');
    
    $mb = microtime();
    
    $action->lay->set("CODE", "OK");
    $action->lay->set("warning", "");
    
    $doc = new_Doc($dbaccess, $docid);
    $err = "";
    if (!$doc->isAffected()) {
        $err = sprintf(_("cannot see unknow reference %s") , $docid);
    }
    $files = array();
    if ($err == "") {
        $action->lay->set("docid", $doc->id);
        $files = $doc->GetFilesProperties();
    }
    
    if ($err != "") {
        $action->lay->set("CODE", "KO");
    }
    $action->lay->set("warning", $err);
    $action->lay->set("delay", microtime_diff(microtime() , $mb));
    
    foreach ($files as $k => $v) {
        if (($v["teng_state"] == \Dcp\TransformationEngine\Client::status_done) || ($v["teng_state"] < 0)) {
            $files[$k]["icon"] = sprintf("resizeimg.php?img=Images/%s&amp;size=20", htmlspecialchars(getIconMimeFile($v["mime_s"]) , ENT_QUOTES));
        } else {
            $files[$k]["icon"] = "";
        }
        $files[$k]["name"] = str_replace('&', '&amp;', $files[$k]["name"]);
    }
    
    $action->lay->setBlockData("FILES", $files);
    $action->lay->set("count", count($files));
    $action->lay->set("docid", $doc->id);
}
