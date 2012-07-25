<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

include_once ("FDL/Class.Dir.php");
include_once ("FDL/editutil.php");
include_once ("ACCESS/download.php");
/**
 * Edit application parameters
 * @param Action $action
 * @return bool
 */
function editapplicationparameter(Action & $action)
{
    
    $usage = new ActionUsage($action);
    $parameterid = $usage->addNeeded("parameterId", _("Parameter's id"));
    $appid = $usage->addOption("appId", _("Application Id"));
    $default = $usage->addOption("emptyValue", _("value for empty field"));
    $value = $usage->addOption("value", _("value in field"));
    $onChange = $usage->addOption("submitOnChange", _("Sending input on change?"));
    $localSubmit = $usage->addOption("localSubmit", _("Adding button to submit")) == "yes" ? true : false;
    $submitLabel = $usage->addOption("submitLabel", _("Label of submit button") , array() , _("Submit"));
    $usage->strict();
    $usage->verify();
    
    $action->lay->set("parameterid", $parameterid);
    
    $paramdef = array();
    $err = simpleQuery($action->dbaccess, sprintf("SELECT * from paramdef where name='%s'", pg_escape_string($parameterid)) , $paramdef);
    if ($err) {
        $action->AddWarningMsg(sprintf(_("Parameter [%s] not found. Error message: [%s]") , $parameterid, $err));
        $action->lay->template = sprintf(_("Parameter [%s] not found. Error message: [%s]") , $parameterid, $err);
        return false;
    }
    $action->lay->set("type_text", ($paramdef[0]["kind"] == "text"));
    $enum = substr($paramdef[0]["kind"], 0, 4) == "enum";
    $action->lay->set("type_enum", $enum);
    
    $type = ($paramdef[0]["isglob"] == "Y") ? PARAM_GLB : PARAM_APP;
    $action->lay->set("type", $type);
    
    if (!$appid) {
        if ($type !== PARAM_GLB) {
            $action->AddWarningMsg(sprintf(_("Parameter [%s] is not global, an apllication muste be given") , $parameterid));
            $action->lay->template = sprintf(_("Parameter [%s] is not global, an apllication muste be given") , $parameterid);
            return false;
        }
        $appid = $paramdef[0]["appid"];
    } else {
        $app = new Application();
        $null = null;
        $err = $app->set($appid, $null);
        if ($err) {
            $action->AddWarningMsg(sprintf(_("Application [%s] not found. Error message: [%s]") , $appid, $err));
            $action->lay->template = sprintf(_("Application [%s] not found. Error message: [%s]") , $appid, $err);
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
        $action->lay->template = sprintf(_("Parameter [%s] not found. Error message: [%s]") , $parameterid, $err);
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
    
    $action->lay->set("local_submit", $localSubmit);
    $action->lay->set("submit_label", $submitLabel);
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
        $action->lay->SetBlockData("options", $values);
    } else {
        $action->lay->set("value", $value);
    }
    $label = $paramdef[0]["descr"] ? _($paramdef[0]["descr"]) : "";
    $action->lay->set("label", $label);

    $action->parent->addJsRef("lib/jquery/jquery.js");
    $action->parent->addJsRef("FDL/Layout/editparameter.js");
    return true;
}
