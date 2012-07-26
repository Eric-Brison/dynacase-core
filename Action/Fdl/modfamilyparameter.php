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
function modfamilyparameter(Action & $action) {

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
            $out["errors"] = sprintf(_("Parameter [%s] not found in family [%s]"), $attrid, $famid);
        } else {
            if ($attr->type == "array" && $value) {
                $modify = false;
                $result = array();
                $i = 0;
                $d = createDoc($action->dbaccess, $doc->id, false, true, false);
                /**
                 * @var array $value
                 */
                foreach ($value as $v) {
                    $key = $v["attrid"];
                    /**
                     * @var NormalAttribute $paramAttribute
                     */
                    $paramAttribute = $doc->getAttribute($v["attrid"]);
                    $phpfunc = $paramAttribute->phpfunc;
                    if (!array_attrid_exists($key, $result)) {
                        $result[$i] = array(
                            "attrid" => $key,
                            "phpfunc" => $phpfunc
                        );
                        foreach ($value as $e) {
                            if ($e["attrid"] == $key) {
                                $result[$i]["value"][] = $e["value"][0];
                            }
                        }
                        $i++;
                    }
                }
                foreach ($result as $v) {
                    if (!empty($v["value"])) {
                        $val = $doc->_array2val($v["value"]);
                        $oldValue = $doc->getParamValue($v["attrid"]);
                        if ($oldValue != $val) {
                            $modify = true;
                        }
                        $doc->setParam($v["attrid"], $val);
                    }
                }
                foreach ($result as $v) {
                    $phpfunc = $v["phpfunc"];
                    if ($phpfunc) {
                        $valueMethod = $d->getValueMethod($phpfunc);
                        if ($valueMethod != $phpfunc) {
                            $doc->setParam($v["attrid"], $valueMethod);
                        }
                    }
                }
                $err = $doc->store();
                if ($err) {
                    $out["success"] = false;
                    $out["errors"] = sprintf(_("an error has occured: %s"), $err);
                } elseif ($modify) {
                    $out["modify"] = true;
                }
            } else {
                $oldValue = $doc->getParamValue($attrid);
                $doc->setParam($attrid, $value);
                $err = $doc->store();
                if ($err) {
                    $out["success"] = false;
                    $out["errors"] = sprintf(_("an error has occured: %s"), $err);
                } else {
                    if ($oldValue != $value) {
                        $out["modify"] = true;
                    }
                }
            }
        }
    } else {
        $out["success"] = false;
        $out["errors"] = sprintf(_("Doucment [%s] not found"), $famid);
    }
    $action->lay->set("success", $out["success"]);
    $action->lay->set("warning", $out["errors"]);
    $action->lay->set("count", 1);
    $action->lay->set("parameterid", $out["parameterid"]);
    $action->lay->set("modify", $out["modify"]);
    $action->lay->set("delay", microtime_diff(microtime(), $mb));
}

/**
 * Return true if key is in attrid of array
 * @param string $key key to search
 * @param array $array array to search in
 * @return bool
 */
function array_attrid_exists($key, array $array) {
    foreach ($array as $val) {
        if ($val["attrid"] == $key) {
            return true;
        }
    }
    return false;
}
