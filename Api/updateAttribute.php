<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Update attribute values for a document set
 *
 * @author Anakeen
 * @package FDL
 */
/**
 * @var Action $action
 */
global $action;

include_once ("FDL/Lib.Attr.php");
include_once ("FDL/Class.DocFam.php");

$usage = new ApiUsage();
$usage->setDefinitionText("Update attribute value for a document set");
$sObject = $usage->addRequiredParameter("objectFile", "serialized object");
$sArg = $usage->addRequiredParameter("argsFile", "serialized args");
$method = $usage->addRequiredParameter("method", "method to apply");
$statusFile = $usage->addRequiredParameter("statusFile", "status file output");
$usage->verify();
/**
 * @var UpdateAttribute $ua
 */
$ua = unserialize(file_get_contents($sObject));
$args = unserialize(file_get_contents($sArg));

$ua->setStatusFile($statusFile);
switch ($method) {
    case "addValue":
    case "removeValue":
    case "replaceValue":
    case "setValue":
        $s = $ua->dl->getSearchDocument();
        $s->reset();
        try {
            call_user_func_array(array(
                $ua,
                $method
            ) , $args);
        }
        catch(Exception $e) {
            $ua->logStatus("ERROR:" . $e->getMessage());
            $ua->logStatus("END");
        }
        break;

    default:
        $ua->logStatus("ERROR:" . sprintf("method %s not available", $method));
        $ua->logStatus("END");
        $action->exitError(sprintf("method %s not available", $method));
}
?>