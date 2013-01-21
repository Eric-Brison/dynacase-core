<?php

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

if (!method_exists($auth, 'logon')) {
    throw new \Dcp\Exception("Authenticator '%s' does not provide a logon() method.", get_class($auth));
}
$auth->logon();

function getAuthenticator($authtype = '') {
    if ($authtype == '') {
        $authtype = getAuthType();
    }
    if (!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $authtype)) {
        return false;
    }
    $authClass = strtolower($authtype) . "Authenticator";
    if (!@include_once (DEFAULT_PUBDIR . DIRECTORY_SEPARATOR . 'WHAT' . DIRECTORY_SEPARATOR . 'Class.' . $authClass . '.php')) {
        return false;
    }
    return new $authClass($authtype, "__for_logout__");
}
