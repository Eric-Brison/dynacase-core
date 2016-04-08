<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Search document and return list of them
 *
 * @author Anakeen
 * @version $Id: searchdocument.php,v 1.5 2007/09/07 15:26:49 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Lib.Dir.php");
/**
 * View list of documents from one folder
 * @param Action &$action current action
 * @internal string $famid Http var : family id where search document
 * @global string $key Http var : filter key on the title
 */
function searchdocument(Action & $action)
{
    header('Content-type: text/xml; charset=utf-8');
    
    $mb = microtime();
    $famid = GetHttpVars("famid");
    $key = GetHttpVars("key");
    $noids = explode('|', GetHttpVars("noids"));
    
    $action->lay->set("warning", "");
    $action->lay->set("CODE", "OK");
    $limit = 20;
    if ($key != "") $filter[] = "title ~* '" . pg_escape_string($key) . "'";
    $filter[] = "doctype!='T'";
    
    $lq = internalGetDocCollection($action->dbaccess, 0, 0, $limit, $filter, $action->user->id, "TABLE", $famid);
    $doc = new_doc($action->dbaccess);
    
    foreach ($lq as $k => $v) {
        if (!in_array($v["initid"], $noids)) {
            $lq[$k]["title"] = $v["title"];
            $lq[$k]["stitle"] = str_replace("'", "\\'", ($v["title"]));
            $lq[$k]["icon"] = $doc->getIcon($v["icon"]);
        } else {
            unset($lq[$k]);
        }
    }
    
    $action->lay->eSetBlockData("DOCS", $lq);
    
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
