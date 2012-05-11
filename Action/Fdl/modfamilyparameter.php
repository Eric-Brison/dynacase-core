<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

include_once ('FDL/Class.Doc.php');
/**
 * Change value of family parameter
 * @param Action $action
 */
function modfamilyparameter(Action & $action)
{
    
    $usage = new ActionUsage($action);
    $famid = $usage->addNeeded("famid", "family id");
    $attrid = $usage->addNeeded("attrid", "attribute id");
    $value = $usage->addOption("value", "value in field");
    $usage->strict();
    $usage->verify();
    
    header('Content-type: text/xml; charset=utf-8');
    
    $mb = microtime();
    
    $out = array(
        "errors" => "",
        "success" => true,
        "parameterid" => $attrid,
        "modify" => false
    );
    /**
     * @var DocFam $doc
     */
    $doc = new_Doc($action->dbaccess, $famid);
    if ($doc->isAlive()) {
        $attr = $doc->getAttribute($attrid);
        if (!$attr) {
            $out["success"] = false;
            $out["errors"] = sprintf(_("Parameter [%s] not found in family [%s]") , $attrid, $famid);
        } else {
            if ($attr->type == "array") {
                $modify = false;
                /**
                 * @var array $value
                 */
                foreach ($value as $v) {
                    if (!empty($v["value"])) {
                        $val = $doc->_array2val($v["value"]);
                        if ($doc->getParam($v["attrid"]) != $val) {
                            $modify = true;
                        }
                        $doc->setParam($v["attrid"], $val);
                    }
                }
                $err = $doc->Modify();
                if ($err) {
                    $out["success"] = false;
                    $out["errors"] = sprintf(_("an error has occured: %s") , $err);
                } elseif ($modify) {
                    $out["modify"] = true;
                }
            } else {
                $oldValue = $doc->getParam($attrid);
                $doc->setParam($attrid, $value);
                $err = $doc->Modify();
                if ($err) {
                    $out["success"] = false;
                    $out["errors"] = sprintf(_("an error has occured: %s") , $err);
                } else {
                    if ($oldValue != $value) {
                        $out["modify"] = true;
                    }
                }
            }
        }
    } else {
        $out["success"] = false;
        $out["errors"] = sprintf(_("Doucment [%s] not found") , $famid);
    }
    $action->lay->set("success", $out["success"]);
    $action->lay->set("warning", $out["errors"]);
    $action->lay->set("count", 1);
    $action->lay->set("parameterid", $out["parameterid"]);
    $action->lay->set("modify", $out["modify"]);
    $action->lay->set("delay", microtime_diff(microtime() , $mb));
}
