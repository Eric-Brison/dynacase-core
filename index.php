<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Main program to activate action in WHAT software
 *
 * All HTTP requests call index.php to execute action within application
 *
 * @author Anakeen
 * @version $Id: index.php,v 1.64 2008/12/16 15:51:53 jerome Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ('WHAT/Lib.Main.php');
include_once ('WHAT/Class.AuthenticatorManager.php');
include_once ('WHAT/Class.ActionRouter.php');

register_shutdown_function('handleFatalShutdown');

$guestMode = getDbAccessValue("useIndexAsGuest");

$needToBeGuest = false;

$noAsk = ($guestMode == true);
$status = AuthenticatorManager::checkAccess(null, $noAsk);
switch ($status) {
    case AuthenticatorManager::AccessOk: // it'good, user is authentified
        $_SERVER['PHP_AUTH_USER'] = AuthenticatorManager::$auth->getAuthUser();
        break;

    case AuthenticatorManager::AccessBug:
        // User must change his password
        // $action->session->close();
        AuthenticatorManager::$auth->logout("authent.php?sole=A&app=AUTHENT&action=ERRNO_BUG_639");
        exit(0);
        break;

    case AuthenticatorManager::NeedAsk:
        $needToBeGuest = true;
        break;

    default:
        sleep(1); // for robots
        // Redirect to authentication
        AuthenticatorManager::$auth->askAuthentication(array());
        
        exit(0);
}

$account = AuthenticatorManager::getAccount();
if ($account === false) {
    if (!$needToBeGuest) {
        throw new \Dcp\Exception("You are not supposed to be here...");
    }
}
if ($needToBeGuest) {
    $account = new Account();
    if ($account->setLoginName("anonymous") === false) {
        throw new \Dcp\Exception("anonymous account not found.");
    }
}
if (ActionRouter::inMaintenance()) {
    if ($account->login != 'admin') {
        include_once ('TOOLBOX/stop.php');
        exit(0);
    }
}
$actionRouter = new ActionRouter($account, AuthenticatorManager::$auth);
$actionRouter->executeAction();
