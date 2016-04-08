<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Main program to activate action in WHAT software in guest mode
 *
 * @author Anakeen
 * @version $Id: guest.php,v 1.24 2008/12/16 15:51:53 jerome Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ('WHAT/Class.ActionRouter.php');

$allowGuest = getParam('CORE_ALLOW_GUEST', 'no');
if ($allowGuest != 'yes') {
    $e = new Dcp\Core\Exception("CORE0010");
    $e->addHttpHeader('HTTP/1.0 503 Guest access not allowed');
    throw $e;
}

if (ActionRouter::inMaintenance()) {
    include_once ('TOOLBOX/stop.php');
    exit(0);
}

include_once ('WHAT/Lib.Main.php');
include_once ('WHAT/Class.ActionRouter.php');

register_shutdown_function('handleFatalShutdown');

$account = new Account();
if ($account->setLoginName("anonymous") === false) {
    throw new \Dcp\Exception(sprintf("anonymous account not found."));
}
$actionRouter = new ActionRouter($account);
$actionRouter->executeAction();
