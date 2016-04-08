<?php
/*
 * @author Anakeen
 * @package FDL
*/

$usage = new ApiUsage();
$usage->setDefinitionText("add session handler");
$handlerName = $usage->addRequiredParameter("handlerClass", "class name of session handler to use - set to SessionHandler to use php system handler");
$usage->verify();
/**
 * @var Action $action
 */
$handlerCode = '';
if ($handlerName != "SessionHandler") {
    
    if (!class_exists($handlerName)) {
        $action->exitError(sprintf("class handler %s not found", $handlerName));
    }
    $ref = new ReflectionClass($handlerName);
    $filePath = $ref->getFileName();
    
    if (strpos($filePath, DEFAULT_PUBDIR) == 0) {
        $basefilePath = substr($filePath, strlen(DEFAULT_PUBDIR) + 1);
        if (file_exists($basefilePath)) {
            $filePath = $basefilePath;
        }
    }
    $h = new $handlerName();
    if (interface_exists("SessionHandlerInterface", false) && is_a($h, "SessionHandlerInterface")) {
        // PHP 5.4 method used
        $handlerCode = sprintf('<?php require_once("%s");$handler = new %s();session_set_save_handler($handler, true);', $filePath, $handlerName);
    } else {
        // Old method compatible PHP 5.3
        if ($ref->hasMethod("open") && $ref->hasMethod("close") && $ref->hasMethod("read") && $ref->hasMethod("write") && $ref->hasMethod("destroy") && $ref->hasMethod("gc")) {
            // PHP 5.3 mode
            $handlerCode = sprintf('<?php require_once("%s");$handler = new %s();session_set_save_handler(array($handler, "open"), array($handler, "close"),array($handler, "read"),array($handler, "write"),array($handler, "destroy"),array($handler, "gc"));register_shutdown_function("session_write_close");', $filePath, $handlerName);
        } else {
            $action->exitError(sprintf('class "%s" incompatible with session handler', $handlerName));
        }
    }
    file_put_contents("config/sessionHandler.php", $handlerCode);
    printf("Write config/sessionHandler.php Done.\n");
} else {
    $handlerCode = '';
    file_put_contents("config/sessionHandler.php", $handlerCode);
    printf("Reset config/sessionHandler.php Done.\n");
}

