<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 * Choose action before create en exec document
 *
 * @author Anakeen 2005
 * @version $Id: freedom_chooseaction.php,v 1.2 2008/08/14 09:59:14 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.Dir.php");
/**
 * To choose action before create en exec document
 * @param Action &$action current action
 * @global id Http var : document identificator folder
 * @global saction Http var : action name to execute
 * @global sapp Http var : app name to execute
 */
function freedom_chooseaction(&$action)
{
    
    $docid = GetHttpVars("id");
    
    $dbaccess = $action->GetParam("FREEDOM_DB");
    $doc = new_Doc($dbaccess, $docid);
    if ($doc->isAlive()) {
        $la = $doc->GetActionAttributes();
        if (count($la) == 0) $action - exitError(_("no action found for %s document") , $doc->title);
        $oa = current($la);
        foreach ($la as $ka => $oa) {
            $taction[$ka]["label"] = $oa->getLabel();
            
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
            $taction[$ka]["url"] = sprintf("%s&app=GENERIC&action=GENERIC_EDIT&classid=EXEC%s", $action->GetParam("CORE_STANDURL") , $url);
        }
        $action->lay->setBlockData("ACTIONS", $taction);
    }
}
