<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Grouped document requests
 *
 * @author Anakeen
 * @version $Id:  $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("DATA/document.php");
/**
 * Retrieve and set documents
 * @param Action &$action current action
 * @global id Http var : document identifier
 */
function grouprequest(Action & $action)
{
    
    $request = json_decode(getHttpVars("request"));
    $out = array();
    $docid = array();
    foreach ($request as $kr => $rs) {
        foreach ($rs as $varname => $r) {
            $method = strtolower($r->method);
            $config = $r->config;
            setHttpVar("config", $config);
            if ($config) {
                foreach ($config as $k => $v) { // set http vars
                    if (is_object($v)) setHttpVar($k, json_encode($v));
                    else setHttpVar($k, ($v));
                }
            }
            
            $id = '';
            if ($r->config && $r->config->id) $id = $r->config->id;
            else if ($r->method == "getSelection") $id = 'selection';
            else if ($r->variable && $docid[$r->variable]) $id = $docid[$r->variable];
            
            if ($id) {
                if ($r->iterative) {
                    $ds = $out[$r->variable]->content;
                    $outi = array();
                    if (is_array($ds)) {
                        foreach ($ds as $k => $v) {
                            documentApplyMethod($action, $v["properties"]["id"], $method, $returntype, $outi[$k], $document);
                        }
                    }
                    $out[$varname] = array(
                        "iterative" => $outi
                    );
                } else {
                    documentApplyMethod($action, $id, $method, $returntype, $out[$varname], $document);
                    if ($document) {
                        $docid[$varname] = $document->getProperty('id');
                    } else if ($id == "selection") $docid[$varname] = $id;
                }
            } else {
                $out["error"] = sprintf(_("request empty reference"));
            }
            if ($config) {
                foreach ($config as $k => $v) { // restore initial http vars
                    setHttpVar($k, "");
                }
            }
        }
    }
    
    $action->lay->template = json_encode($out);
    $action->lay->noparse = true;
}
?>