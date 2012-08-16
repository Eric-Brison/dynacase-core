<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Get DAV session
 *
 * @author Anakeen
 * @version $Id: getsessionid.php,v 1.4 2007/03/08 16:35:35 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
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
 * @param string $vid identificator for file <vaultid>-<docid>
 */
function getsessionid(&$action)
{
    header('Content-type: text/xml; charset=utf-8');
    
    $mb = microtime();
    $vid = GetHttpVars("vid");
    $docid = GetHttpVars("docid");
    
    $dbaccess = $action->GetParam("FREEDOM_DB");
    
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
    $s = new HTTP_WebDAV_Server_Freedom(getParam("WEBDAV_DB"));
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
    $dbaccess = getParam("FREEDOM_DB");
    $vf = newFreeVaultFile($dbaccess);
    $sdav = getParam("FREEDAV_SERVEUR", false);
    if ($sdav && $vf->Show($vid, $info) == "") {
        return sprintf("asdav://%s/freedav/vid-%s/%s", $sdav, dav_sessionid($docid, $vid) , $info->name);
    }
}
