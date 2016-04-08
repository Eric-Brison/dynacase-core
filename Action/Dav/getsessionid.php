<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Get DAV session
 *
 * @author Anakeen
 * @version $Id: getsessionid.php,v 1.4 2007/03/08 16:35:35 eric Exp $
 * @package FDL
 * @subpackage DAV
 */
/**
 */

include_once ("FDL/Class.Doc.php");
include_once ("DAV/Class.FdlDav.php");
/**
 * Get DAV session id for current user
 * @param Action &$action current action
 */
function getsessionid(Action & $action)
{
    header('Content-type: text/xml; charset=utf-8');
    
    $mb = microtime();
    $vid = GetHttpVars("vid");
    $docid = GetHttpVars("docid");
    
    $action->lay->set("warning", "");
    $action->lay->set("CODE", "OK");
    $sessid = dav_sessionid($docid, $vid);
    $action->lay->set("sessid", $sessid);
    $action->lay->set("count", 1);
    $action->lay->set("delay", microtime_diff(microtime() , $mb));
}

function dav_sessionid($docid, $vid)
{
    global $action;
    $s = new HTTP_WebDAV_Server_Freedom($action->dbaccess);
    $s->setFolderMaxItem(getParam('WEBDAV_FOLDERMAXITEM'));
    $sid = $s->getSession($docid, $vid, $action->user->login);
    if (!$sid) {
        $sid = md5(uniqid($vid));
        $s->addsession($sid, $vid, $docid, $action->user->login, time() + 3600);
        if (!$s) $action->lay->set("CODE", "KO");
    }
    return "$docid-$vid-$sid";
}
function dav_getdavurl($docid, $vid)
{
    global $action;
    $dbaccess = $action->dbaccess;
    $vf = newFreeVaultFile($dbaccess);
    $sdav = getParam("FREEDAV_SERVEUR", false);
    if ($sdav && $vf->Show($vid, $info) == "") {
        return sprintf("asdav://%s/freedav/vid-%s/%s", $sdav, dav_sessionid($docid, $vid) , $info->name);
    }
    return false;
}
