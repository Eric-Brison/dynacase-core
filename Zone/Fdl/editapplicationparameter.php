<?php
/*
 * @author Anakeen
 * @package FDL
*/

include_once ("FDL/Class.Dir.php");
include_once ("FDL/editutil.php");
/**
 * Edit application parameters
 * @param Action $action
 * @return bool
 */
function editapplicationparameter(Action & $action)
{
    
    $usage = new ActionUsage($action);
    $parameterid = $usage->addRequiredParameter("parameterId", _("Parameter's id"));
    $appid = $usage->addOptionalParameter("appId", _("Application Id"));
    $default = $usage->addOptionalParameter("emptyValue", _("value for empty field"));
    $value = $usage->addOptionalParameter("value", _("value in field"));
    $onChange = $usage->addOptionalParameter("submitOnChange", _("Sending input on change?"));
    $localSubmit = $usage->addOptionalParameter("localSubmit", _("Adding button to submit")) == "yes" ? true : false;
    $submitLabel = $usage->addOptionalParameter("submitLabel", _("Label of submit button") , array() , _("Submit"));
    $usage->setStrictMode();
    $usage->verify();
    
    $action->lay->eset("parameterid", $parameterid);
    
    $paramdef = array();
    $err = simpleQuery($action->dbaccess, sprintf("SELECT * from paramdef where name='%s'", pg_escape_string($parameterid)) , $paramdef);
    if ($err) {
        $action->AddWarningMsg(sprintf(_("Parameter [%s] not found. Error message: [%s]") , $parameterid, $err));
        $action->lay->template = htmlspecialchars(sprintf(_("Parameter [%s] not found. Error message: [%s]") , $parameterid, $err) , ENT_QUOTES);
        $action->lay->noparse = true;
        return false;
    }
    $action->lay->set("type_text", ($paramdef[0]["kind"] == "text"));
    $enum = substr($paramdef[0]["kind"], 0, 4) == "enum";
    $action->lay->set("type_enum", $enum);
    
    $type = ($paramdef[0]["isglob"] == "Y") ? Param::PARAM_GLB : Param::PARAM_APP;
    $action->lay->set("type", $type);
    
    if (!$appid) {
        if ($type !== Param::PARAM_GLB) {
            $action->AddWarningMsg(sprintf(_("Parameter [%s] is not global, an apllication muste be given") , $parameterid));
            $action->lay->template = htmlspecialchars(sprintf(_("Parameter [%s] is not global, an apllication muste be given") , $parameterid) , ENT_QUOTES);
            $action->lay->noparse = true;
            return false;
        }
        $appid = $paramdef[0]["appid"];
    } else {
        $app = new Application();
        $null = null;
        $err = $app->set($appid, $null);
        if ($err) {
            $action->AddWarningMsg(sprintf(_("Application [%s] not found. Error message: [%s]") , $appid, $err));
            $action->lay->template = htmlspecialchars(sprintf(_("Application [%s] not found. Error message: [%s]") , $appid, $err) , ENT_QUOTES);
            $action->lay->noparse = true;
            return false;
        }
        $appid = $app->id;
    }
    $action->lay->set("appid", getApplicationNameFromId($action->dbaccess, $appid));
    
    $val = array();
    $query = sprintf("SELECT * from paramv where name='%s' and appid='%s' and type='%s'", pg_escape_string($parameterid) , pg_escape_string($appid) , pg_escape_string($type));
    $err = simpleQuery($action->dbaccess, $query, $val);
    if ($err) {
        $action->AddWarningMsg(sprintf(_("Parameter [%s] not found. Error message: [%s]") , $parameterid, $err));
        $action->lay->template = htmlspecialchars(sprintf(_("Parameter [%s] not found. Error message: [%s]") , $parameterid, $err));
        $action->lay->noparse = true;
        return false;
    }
    if (!$value) {
        if ($default !== null) {
            $value = $default;
        } else {
            $value = $val[0]["val"];
        }
    }
    
    if ($onChange == "no") {
        $onChange = "";
    } elseif ($onChange == "yes" || (!$onChange && !$localSubmit)) {
        $onChange = "yes";
    }
    
    $action->lay->set("local_submit", (bool)$localSubmit);
    $action->lay->eset("submit_label", $submitLabel);
    $action->lay->set("on_change", "");
    $action->lay->set("change", ($onChange != ""));
    if ($enum) {
        $matches = array();
        preg_match('/enum\(([^.]*)\)/', $paramdef[0]["kind"], $matches);
        $valuestmp = explode("|", $matches[1]);
        $values = array();
        foreach ($valuestmp as $v) {
            if ($v == $value) {
                $values[] = array(
                    "selected" => 'selected="selected"',
                    "value" => $v
                );
            } else {
                $values[] = array(
                    "selected" => '',
                    "value" => $v
                );
            }
        }
        $action->lay->eSetBlockData("options", $values);
    } else {
        $action->lay->eset("value", $value);
    }
    $label = $paramdef[0]["descr"] ? _($paramdef[0]["descr"]) : "";
    $action->lay->set("label", $label);
    
    $action->parent->addJsRef("lib/jquery/jquery.js");
    $action->parent->addJsRef("FDL/Layout/editparameter.js");
    return true;
}

function getApplicationNameFromId($dbaccess, $id, &$cache = null)
{
    if (is_array($cache) && array_key_exists('app', $cache)) {
        if (array_key_exists($id, $cache['app'])) {
            return $cache['app'][$id];
        }
    }
    
    $query = new QueryDb($dbaccess, "application");
    $query->addQuery(sprintf("id = %s", pg_escape_string($id)));
    $res = $query->query(0, 0, "TABLE");
    if (!is_array($res)) {
        return null;
    }
    
    $name = $res[0]['name'];
    if (is_array($cache) && array_key_exists('app', $cache)) {
        $cache['app'][$id] = $name;
    }
    
    return $name;
}
