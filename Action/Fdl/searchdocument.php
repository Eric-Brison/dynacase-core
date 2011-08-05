<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 * Search document and return list of them
 *
 * @author Anakeen 2006
 * @version $Id: searchdocument.php,v 1.5 2007/09/07 15:26:49 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage
 */
/**
 */

include_once ("FDL/Lib.Dir.php");
/**
 * View list of documents from one folder
 * @param Action &$action current action
 * @global famid Http var : family id where search document
 * @global key Http var : filter key on the title
 */
function searchdocument(&$action)
{
    header('Content-type: text/xml; charset=utf-8');
    $action->lay->setEncoding("utf-8");
    
    $mb = microtime();
    $famid = GetHttpVars("famid");
    $key = GetHttpVars("key");
    $noids = explode('|', GetHttpVars("noids"));
    $dbaccess = $action->GetParam("FREEDOM_DB");
    
    $action->lay->set("warning", "");
    $action->lay->set("CODE", "OK");
    $limit = 20;
    if ($key != "") $filter[] = "title ~* '" . pg_escape_string($key) . "'";
    $filter[] = "doctype!='T'";
    
    $lq = getChildDoc($dbaccess, 0, 0, $limit, $filter, $action->user->id, "TABLE", $famid);
    $doc = new_doc($dbaccess);
    
    foreach ($lq as $k => $v) {
        if (!in_array($v["initid"], $noids)) {
            $lq[$k]["title"] = ($v["title"]);
            $lq[$k]["stitle"] = str_replace("'", "\\'", ($v["title"]));
            $lq[$k]["icon"] = $doc->getIcon($v["icon"]);
        } else {
            unset($lq[$k]);
        }
    }
    
    $action->lay->setBlockData("DOCS", $lq);
    
    $action->lay->set("onecount", false);
    if (count($lq) == 1) {
        $action->lay->set("onecount", true);
        reset($lq);
        $q = current($lq);
        $action->lay->set("firstinsert", sprintf(_("%s inserted") , $q["title"]));
    }
    $action->lay->set("count", count($lq));
    $action->lay->set("delay", microtime_diff(microtime() , $mb));
}
?>