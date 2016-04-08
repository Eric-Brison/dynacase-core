<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Get Values in XML form
 *
 * @author Anakeen
 * @version $Id: getdocvalues.php,v 1.4 2008/11/05 10:10:41 eric Exp $
 * @package FDL
 * @subpackage FDC
 */
/**
 */

include_once ("FDL/Class.Doc.php");
/**
 * Get  doc attributes values
 * @param Action &$action current action
 * @global id int Http var : document id to view
 */
function getdocvalues(&$action)
{
    header('Content-type: text/xml; charset=utf-8');
    
    $err = '';
    $mb = microtime();
    $docid = GetHttpVars("id");
    $attrid = strtolower(GetHttpVars("attrid"));
    $dbaccess = $action->dbaccess;
    
    $action->lay->set("warning", "");
    
    $doc = new_doc($dbaccess, $docid);
    $tvalues = array();
    
    if (!$doc->isAlive()) $err = sprintf(_("document [%s] not found") , $docid);
    if ($err == "") {
        $err = $doc->control("view");
        if ($err == "") {
            if ($attrid) $values[$attrid] = $doc->getRawValue($attrid);
            else $values = $doc->getValues();
            foreach ($values as $aid => $v) {
                $a = $doc->getAttribute($aid);
                if ($a->visibility != "I") {
                    $tvalues[] = array(
                        "attrid" => $aid,
                        "value" => xml_entity_encode($v)
                    );
                }
            }
        }
    }
    if ($err) $action->lay->set("warning", $err);
    
    $action->lay->setBlockData("VALUES", $tvalues);
    $action->lay->set("CODE", "OK");
    $action->lay->set("count", 1);
    $action->lay->set("delay", microtime_diff(microtime() , $mb));
}
