<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Get Values in XML form
 *
 * @author Anakeen
 * @version $Id: getdocsvalue.php,v 1.1 2008/11/14 16:37:05 eric Exp $
 * @package FDL
 * @subpackage FDC
 */
/**
 */

include_once ("FDL/Class.Doc.php");
include_once ("FDL/Lib.Dir.php");
/**
 * Return a single same value from a set of doc
 * @param Action &$action current action
 * @global ids string Http var : set of document id ( | separated)
 */
function getdocsvalue(&$action)
{
    header('Content-type: text/xml; charset=utf-8');
    
    $err = '';
    $mb = microtime();
    $docids = GetHttpVars("ids");
    $attrid = trim(strtolower(GetHttpVars("attrid")));
    $dbaccess = $action->dbaccess;
    
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
            "value" => ''
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
