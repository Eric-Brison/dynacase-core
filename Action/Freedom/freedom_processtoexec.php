<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Interface to create new execution from batch
 *
 * @author Anakeen
 * @version $Id: freedom_processtoexec.php,v 1.3 2008/08/14 09:59:14 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
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
 * @global id Http var : document identifier for process document
 */
function freedom_processtoexec(&$action)
{
    // -----------------------------------
    // Get all the params
    $docid = GetHttpVars("id"); // id doc to search
    $action->lay->set("docid", $docid);
    
    $dbaccess = $action->GetParam("FREEDOM_DB");
    
    $doc = new_Doc($dbaccess, $docid);
    if ($doc->isAlive()) {
        $la = $doc->GetActionAttributes();
        if (count($la) == 0) $action - exitError(_("no action found for %s document") , $doc->title);
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
            while (list($k, $v) = each($p)) {
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
?>