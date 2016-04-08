<?php
/*
 * @author Anakeen
 * @package FDL
*/

include_once ('WHAT/Class.Param.php');
/**
 * Modify application parameters
 * @param Action $action
 */
function modapplicationparameter(Action & $action)
{
    $usage = new ActionUsage($action);
    $appid = $usage->addRequiredParameter("appid", "application id");
    $name = $usage->addRequiredParameter("name", "parameter name");
    $type = $usage->addRequiredParameter("type", "type of parameter");
    $value = $usage->addOptionalParameter("value", "value for parameter");
    $usage->setStrictMode();
    $usage->verify();
    
    header('Content-type: text/xml; charset=utf-8');
    
    $mb = microtime();
    
    $out = array(
        "errors" => "",
        "success" => true,
        "parameterid" => $name,
        "modify" => false
    );
    $app = new Application();
    $null = null;
    $err = $app->set($appid, $null);
    if ($err) {
        $out["success"] = false;
        $out["errors"] = sprintf(_("Application not found: [%s]") , $appid);
    } else {
        $appid = $app->id;
        $param = new Param($action->dbaccess, array(
            $name,
            $type,
            $appid
        ));
        if ($param->isAffected()) {
            $oldValue = $param->val;
            $param->val = $value;
            $err = $param->Modify();
            if ($err) {
                $out["success"] = false;
                $out["errors"] = sprintf(_("an error has occured: %s") , $err);
            } else {
                if ($oldValue != $value) {
                    $out["modify"] = true;
                }
            }
        } else {
            $out["errors"] = sprintf(_("Parameter [%s] not found") , $name);
            $out["success"] = false;
        }
    }
    
    $action->lay->set("success", $out["success"]);
    $action->lay->set("warning", $out["errors"]);
    $action->lay->set("count", 1);
    $action->lay->set("parameterid", $out["parameterid"]);
    $action->lay->set("modify", $out["modify"]);
    $action->lay->set("delay", microtime_diff(microtime() , $mb));
}
