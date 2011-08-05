<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 * Get Values in XML form
 *
 * @author Anakeen 2006
 * @version $Id: getdocsvalue.php,v 1.1 2008/11/14 16:37:05 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage FDC
 */
/**
 */

include_once ("FDL/Class.Doc.php");
include_once ("FDL/Lib.Dir.php");
/**
 * Return a single same value from a set of doc
 * @param Action &$action current action
 * @global ids Http var : set of document id ( | separated)
 */
function getdocsvalue(&$action)
{
    header('Content-type: text/xml; charset=utf-8');
    
    $mb = microtime();
    $docids = GetHttpVars("ids");
    $attrid = trim(strtolower(GetHttpVars("attrid")));
    $dbaccess = $action->GetParam("FREEDOM_DB");
    
    $action->lay->set("warning", "");
    
    $tdocids = explode('|', $docids);
    
    $tvalues = array();
    foreach ($tdocids as $k => $v) {
        if (!is_numeric($v)) {
            unset($tdocids[$k]);
        }
        $tvalues[$v] = array(
            "attrid" => $attrid,
            "docid" => $v["id"],
            "value" => xml_entity_encode($value)
        );
    }
    
    $tdoc = getDocsFromIds($dbaccess, $tdocids);
    
    foreach ($tdoc as $k => $v) {
        $value = getv($v, $attrid);
        $tvalues[$v["id"]] = array(
            "attrid" => $attrid,
            "docid" => $v["id"],
            "value" => xml_entity_encode($value)
        );
    }
    
    if ($err) $action->lay->set("warning", $err);
    
    $action->lay->setBlockData("VALUES", $tvalues);
    $action->lay->set("CODE", "OK");
    $action->lay->set("count", count($tvalues));
    $action->lay->set("delay", microtime_diff(microtime() , $mb));
}
?>