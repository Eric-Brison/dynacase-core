<?php
/*
 * @author Anakeen
 * @package FDL
*/

include_once ('WHAT/autoload.php');
include_once ('WHAT/Class.ActionRouter.php');

if (ActionRouter::inMaintenance()) {
    include_once ('TOOLBOX/stop.php');
    exit(0);
}

$auth = getAuthenticator();
if ($auth === false) {
    throw new \Dcp\Exception("Could not get authenticator.");
}

if (isset($_REQUEST['logout'])) {
    AuthenticatorManager::closeAccess();
} else {
    if (!method_exists($auth, 'logon')) {
        throw new \Dcp\Exception(sprintf("Authenticator '%s' does not provide a logon() method.", get_class($auth)));
    }
    // default application is AUTHENT
    if (!isset($_GET["app"])) $_GET["app"] = "AUTHENT";
    /**
     * @var htmlAuthenticator $auth
     */
    $auth->logon();
}
/**
 * @param string $authtype
 * @return Authenticator
 */
function getAuthenticator($authtype = '')
{
    if ($authtype == '') {
        $authtype = \AuthenticatorManager::getAuthType();
    }
    if (!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $authtype)) {
        return false;
    }
    $authClass = strtolower($authtype) . "Authenticator";
    $authFile = DEFAULT_PUBDIR . DIRECTORY_SEPARATOR . 'WHAT' . DIRECTORY_SEPARATOR . 'Class.' . $authClass . '.php';
    if (!file_exists($authFile)) {
        return false;
    }
    include_once ($authFile);
    return new $authClass($authtype, Authenticator::nullProvider);
}
