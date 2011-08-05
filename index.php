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
 * @package WHAT
 * @subpackage
 */
/**
 */

include_once ('WHAT/Lib.Main.php');

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
    if (!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $authtype)) {
        header('HTTP/1.0 500 Internal Server Error');
        echo sprintf("Invalid authtype '%s'", $authtype);
        error_log(__FILE__ . ":" . __LINE__ . "> Invalid authtype " . $authtype);
        exit;
    }
    //print_r2("coucuo");
    $authProviderList = getAuthProviderList();
    foreach ($authProviderList as $ka => $authprovider) {
        $authClass = strtolower($authtype) . "Authenticator";
        if (!@include_once ('WHAT/Class.' . $authClass . '.php')) {
            error_log(__FILE__ . ":" . __LINE__ . "> Unknown authtype " . $authtype);
        } else {
            
            $auth = new $authClass($authtype, $authprovider);
            $status = $auth->checkAuthentication();
            if ($status) {
                $statusA = $auth->checkAuthorization(array(
                    'username' => $auth->getauthUser()
                ));
                if ($statusA == FALSE) {
                    $auth->logout("guest.php?sole=A&app=AUTHENT&action=UNAUTHORIZED");
                    exit(0);
                }
                break;
            }
        }
    }
    if ($status == FALSE) {
        $providerErrno = $auth->getProviderErrno();
        if ($providerErrno != 0) {
            switch ($providerErrno) {
                case Provider::ERRNO_BUG_639:
                    // User must change his password
                    $auth->logout("guest.php?sole=A&app=AUTHENT&action=ERRNO_BUG_639");
            }
        }
        sleep(2); // wait for robots
        $auth->askAuthentication();
        exit(0);
    }
    
    $_SERVER['PHP_AUTH_USER'] = $auth->getAuthUser();
}

if (file_exists('maintenance.lock')) {
    if ($_SERVER['PHP_AUTH_USER'] != 'admin') {
        if ($authtype != 'apache') {
            $auth->logout("");
        }
        include_once ('WHAT/stop.php');
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
getmainAction($auth, $action);
executeAction($action);
?>