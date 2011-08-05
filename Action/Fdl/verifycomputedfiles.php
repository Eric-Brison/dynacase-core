<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 * Specific menu for family
 *
 * @author Anakeen 2000
 * @version $Id: verifycomputedfiles.php,v 1.5 2008/04/14 10:12:14 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.Doc.php");
/**
 * Edit an attribute inline
 * @param Action &$action current action
 * @global docid Http var : document identificator to see
 * @global attrid Http var : the id of attribute to edit
 */
function verifycomputedfiles(&$action)
{
    $docid = GetHttpVars("id");
    $attrid = GetHttpVars("attrid");
    $dbaccess = $action->GetParam("FREEDOM_DB");
    
    header('Content-type: text/xml; charset=utf-8');
    
    $mb = microtime();
    
    $action->lay->set("CODE", "OK");
    $action->lay->set("warning", "");
    $action->lay->set("modjsft", $modjsft);
    
    $doc = new_Doc($dbaccess, $docid);
    if (!$doc->isAffected()) $err = sprintf(_("cannot see unknow reference %s") , $docid);
    if ($err == "") {
        $action->lay->set("docid", $doc->id);
        $files = $doc->GetFilesProperties();
    }
    
    if ($err != "") $action->lay->set("CODE", "KO");
    $action->lay->set("warning", $err);
    $action->lay->set("delay", microtime_diff(microtime() , $mb));
    
    foreach ($files as $k => $v) {
        if (($v["teng_state"] == 1) || ($v["teng_state"] < 0)) {
            $files[$k]["icon"] = "img-cache/20-" . getIconMimeFile($v["mime_s"]) . ".png";
        } else {
            $files[$k]["icon"] = "";
        }
        $files[$k]["name"] = str_replace('&', '&amp;', $files[$k]["name"]);
    }
    
    $action->lay->setBlockData("FILES", $files);
    $action->lay->set("count", count($files));
    $action->lay->set("docid", $doc->id);
}
?>