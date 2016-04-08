<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Add, modify or delete WHAT application
 *
 *
 * @param string $appname internal name of the application
 * @param string $method may be "init","reinit","update","delete"
 * @subpackage WSH
 */
/**
 */
include_once ("Class.Application.php");
global $action;

$usage = new ApiUsage();
$usage->setDefinitionText("Manage application");
$appname = $usage->addRequiredParameter("appname", "application name");
$method = $usage->addOptionalParameter("method", "action to do", array(
    "init",
    "update",
    "reinit",
    "delete"
) , "init");

$usage->verify();

echo " $appname...$method\n";

$app = new Application();

$Null = "";

switch ($method) {
    case "init":
        $app->set($appname, $Null, null, true);
        break;

    case "reinit":
        $app->set($appname, $Null, null, false, false);
        $ret = $app->InitApp($appname, false);
        if ($ret === false) {
            $action->exitError(sprintf("Error initializing application '%s'.", $appname));
        }
        break;

    case "update":
        // Init if application is not already installed
        $app->set($appname, $Null, null, true, false);
        $ret = $app->InitApp($appname, true);
        if ($ret === false) {
            $action->exitError(sprintf("Error updating application '%s'.", $appname));
        }
        break;

    case "delete":
        $app->set($appname, $Null, null, false);
        if ($app->isAffected()) {
            $err = $app->DeleteApp();
            if ($err != '') {
                $action->exitError($err);
            }
        } else {
            echo "already deleted";
        }
        break;
}

