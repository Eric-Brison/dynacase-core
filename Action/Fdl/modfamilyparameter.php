<?php
/*
 * @author Anakeen
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
    $famid = $usage->addRequiredParameter("famid", "family id");
    $attrid = $usage->addRequiredParameter("attrid", "attribute id");
    $value = $usage->addOptionalParameter("value", "value in field");
    $usage->setStrictMode();
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
            if ($attr->type == "array" && $value) {
                $modify = false;
                $result = array();
                $i = 0;
                /**
                 * @var array $value
                 */
                foreach ($value as $v) {
                    $key = $v["attrid"];
                    if (!array_attrid_exists($key, $result)) {
                        $result[$i] = array(
                            "attrid" => $key
                        );
                        foreach ($value as $e) {
                            if ($e["attrid"] == $key) {
                                $result[$i]["value"][] = $e["value"][0];
                            }
                        }
                        $i++;
                    }
                }
                $err = '';
                foreach ($result as $v) {
                    $val = "";
                    if (!empty($v["value"])) {
                        $val = $doc->arrayToRawValue($v["value"]);
                    }
                    $val = trim($val);
                    $ownParams = $doc->getOwnParams();
                    $oldValue = $ownParams[$v["attrid"]];
                    if ($oldValue != $val) {
                        $modify = true;
                    }
                    $err = $doc->setParam($v["attrid"], $val);
                    if ($err) break;
                }
                if (!$err) $err = $doc->store();
                if ($err) {
                    $out["success"] = false;
                    $out["errors"] = $err;
                } elseif ($modify) {
                    $out["modify"] = true;
                }
            } else {
                $oldValue = $doc->getParameterRawValue($attrid);
                $err = $doc->setParam($attrid, $value);
                if (!$err) $err = $doc->store();
                if ($err) {
                    $out["success"] = false;
                    $out["errors"] = $err;
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
    $action->lay->set("warning", htmlspecialchars($out["errors"]));
    $action->lay->set("count", 1);
    $action->lay->set("parameterid", $out["parameterid"]);
    $action->lay->set("modify", $out["modify"]);
    $action->lay->set("delay", microtime_diff(microtime() , $mb));
}
/**
 * Return true if key is in attrid of array
 * @param string $key key to search
 * @param array $array array to search in
 * @return bool
 */
function array_attrid_exists($key, array $array)
{
    foreach ($array as $val) {
        if ($val["attrid"] == $key) {
            return true;
        }
    }
    return false;
}
