<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Main program to activate action in WHAT software
 *
 * All HTTP requests call index.php to execute action within application
 *
 * @author Anakeen 2000
 * @version $Id: index.php,v 1.64 2008/12/16 15:51:53 jerome Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ('WHAT/Lib.Main.php');
include_once ('WHAT/Class.AuthenticatorManager.php');

$authtype = getAuthType();

if ($authtype == 'apache') {
    // Apache has already handled the authentication
    global $_SERVER;
    if ($_SERVER['PHP_AUTH_USER'] == "") {
        header('HTTP/1.0 403 Forbidden');
        echo _("User must be authenticate");
        exit;
    }
} else {
    
    $status = AuthenticatorManager::checkAccess();
    switch ($status) {
        case 0: // it'good, user is authentified
            break;

        case -1:
            // User must change his password
            // $action->session->close();
            AuthenticatorManager::$auth->logout("guest.php?sole=A&app=AUTHENT&action=ERRNO_BUG_639");
            exit(0);
            break;

        default:
            sleep(1); // for robots
            // Redirect to authentication
            AuthenticatorManager::$auth->askAuthentication();
            // AuthenticatorManager::$auth->logout("guest.php?sole=A&app=AUTHENT&action=ERRNO_BUG_639");
            // AuthenticatorManager::$auth->askAuthentication(array("error" => $status));
            // Redirect($action, 'AUTHENT', 'LOGINFORM&error='.$status.'&auth_user='.urlencode($_POST['auth_user']));
            exit(0);
    }
    
    $_SERVER['PHP_AUTH_USER'] = AuthenticatorManager::$auth->getAuthUser();
}

if (file_exists('maintenance.lock')) {
    if ($_SERVER['PHP_AUTH_USER'] != 'admin') {
        include_once ('TOOLBOX/stop.php');
        exit(0);
    }
}
#
# This is the main body of App manager
# It is used to launch application and
# function giving them all necessary environment
# element
#
#
// First control
if (!isset($_SERVER['PHP_AUTH_USER'])) {
    $dirname = dirname($_SERVER["SCRIPT_NAME"]);
    
    Header("Location:" . $dirname . "/guest.php");
    exit();
}
// ----------------------------------------
getmainAction(AuthenticatorManager::$auth, $action);
executeAction($action);
?>