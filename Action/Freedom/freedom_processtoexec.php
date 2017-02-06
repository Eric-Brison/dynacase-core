<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Interface to create new execution from batch
 *
 * @author Anakeen
 * @version $Id: freedom_processtoexec.php,v 1.3 2008/08/14 09:59:14 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.DocSearch.php");
include_once ("FDL/freedom_util.php");
include_once ("GENERIC/generic_util.php");
/**
 * Interface to edit new process
 * @param Action &$action current action
 * @global string $id Http var : document identifier for process document
 */
function freedom_processtoexec(Action & $action)
{
    // -----------------------------------
    // Get all the params
    $docid = GetHttpVars("id"); // id doc to search
    $dbaccess = $action->dbaccess;
    
    $doc = new_Doc($dbaccess, $docid);
    $action->lay->set("docid", $doc->id);
    if ($doc->isAlive()) {
        $la = $doc->GetActionAttributes();
        if (count($la) == 0) $action->exitError(sprintf(_("no action found for %s document") , $doc->title));
        if (count($la) == 1) {
            $oa = current($la);
            $ta["exec_application"] = $oa->wapplication;
            $ta["exec_idref"] = $doc->id;
            $ta["exec_ref"] = $doc->title . " (" . $oa->getLabel() . ")";
            
            $p = explode('&', $oa->waction);
            $ta["exec_action"] = current($p);
            next($p);
            if ($oa->getOption("batchfolder") == "yes") $tp = array(
                "wshfldid" => $doc->id
            );
            else $tp = array(
                "id" => $doc->id
            );
            foreach ($p as $k => $v) {
                list($var, $value) = explode("=", $v);
                $tp[$var] = $value;
            }
            $ta["exec_idvar"] = implode("\n", array_keys($tp));
            $ta["exec_valuevar"] = implode("\n", $tp);
            
            $url = "";
            foreach ($ta as $k => $v) {
                $url.= "&$k=" . urlencode($v);
            }
            $action->lay->set("url", sprintf("%s&app=GENERIC&action=GENERIC_EDIT&classid=EXEC%s", $action->GetParam("CORE_STANDURL") , $url));
        } else {
            $action->lay->set("url", sprintf("%s&app=FREEDOM&action=FREEDOM_CHOOSEACTION&id=%s", $action->GetParam("CORE_STANDURL") , $doc->id));
        }
    }
}
