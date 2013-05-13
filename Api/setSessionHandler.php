<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

$usage = new ApiUsage();
$usage->setDefinitionText("add session handler");
$handlerName = $usage->addRequiredParameter("handlerClass", "class name of session handler to use - set to SessionHandler to use php system handler");
$usage->verify();
/**
 * @var Action $action
 */

if ($handlerName != "SessionHandler") {
    if (!class_exists($handlerName)) {
        $action->exitError(sprintf("class handler %s not found", $handlerName));
    }
    
    if (!interface_exists("SessionHandlerInterface", false)) {
        $action->exitError(sprintf("interface SessionHandlerInterface not found : must be in PHP 5.4"));
    }
    
    $h = new $handlerName();
    
    if (!is_a($h, "SessionHandlerInterface")) {
        $action->exitError(sprintf("class handler %s not implement SessionHandlerInterface", $handlerName));
    }
    $ref = new ReflectionClass($handlerName);
    $filePath = $ref->getFileName();
    
    if (strpos($filePath, DEFAULT_PUBDIR) == 0) {
        $basefilePath = substr($filePath, strlen(DEFAULT_PUBDIR) + 1);
        if (file_exists($basefilePath)) {
            $filePath = $basefilePath;
        }
    }
    
    $handlerCode = sprintf('<?php require_once("%s");$handler = new %s();session_set_save_handler($handler, true);', $filePath, $handlerName);
    
    file_put_contents("config/sessionHandler.php", $handlerCode);
    printf("Write config/sessionHandler.php Done.\n");
} else {
    $handlerCode = '<?php';
    file_put_contents("config/sessionHandler.php", $handlerCode);
    printf("Reset config/sessionHandler.php Done.\n");
}

