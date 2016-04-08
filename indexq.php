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
global $tic1;

$deb = gettimeofday();
$tic1 = $deb["sec"] + $deb["usec"] / 1000000;
include_once ('WHAT/Lib.Main.php');
include_once ('WHAT/Class.AuthenticatorManager.php');
include_once ('WHAT/wdebug.php');

register_shutdown_function('handleFatalShutdown');

global $SQLDELAY, $SQLDEBUG;
global $TSQLDELAY;

function sortqdelay($a, $b)
{
    $xa = doubleval($a["t"]);
    $xb = doubleval($b["t"]);
    if ($xa > $xb) return -1;
    else if ($xa < $xb) return 1;
    return 0;
}

$status = AuthenticatorManager::checkAccess();
switch ($status) {
    case AuthenticatorManager::AccessOk: // it'good, user is authentified
        break;

    case AuthenticatorManager::AccessBug:
        // User must change his password
        AuthenticatorManager::$auth->logout("authent.php?sole=A&app=AUTHENT&action=ERRNO_BUG_639");
        exit(0);
        break;

    case AuthenticatorManager::NeedAsk:
    default:
        sleep(1); // for robots
        // Redirect to authentication
        global $_POST;
        AuthenticatorManager::$auth->askAuthentication(array());
        exit(0);
}

$_SERVER['PHP_AUTH_USER'] = AuthenticatorManager::$auth->getAuthUser();

$account = AuthenticatorManager::getAccount();
if ($account === false) {
    throw new \Dcp\Exception(_("User must be authenticate"));
}
if (ActionRouter::inMaintenance()) {
    if ($account->login != 'admin') {
        include_once ('TOOLBOX/stop.php');
        exit(0);
    }
}
$actionRouter = new ActionRouter($account, AuthenticatorManager::$auth);

global $action;
$deb = gettimeofday();
$ticainit = $deb["sec"] + $deb["usec"] / 1000000;
$trace = $action->read("trace");
$trace["url"] = $_SERVER["REQUEST_URI"];
$trace["init"] = sprintf("%.03fs", $ticainit - $tic1);
$out = '';
$action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/tracetime.js");

$actionRouter->executeAction($out);
//usort($TSQLDELAY, "sortqdelay");
addLogMsg($TSQLDELAY);
$deb = gettimeofday();
$tic4 = $deb["sec"] + $deb["usec"] / 1000000;
$trace["app"] = sprintf("%.03fs", $tic4 - $ticainit);
$trace["memory"] = sprintf("%dkb", round(memory_get_usage() / 1024));
$trace["queries"] = sprintf("%.03fs #%d", $SQLDELAY, count($TSQLDELAY));
$trace["server all"] = sprintf("%.03fs", $tic4 - $tic1);
$trace["n"] = "-------------";
$strace = 'var TTRACE=new Object();' . "\n";

foreach ($trace as $k => $v) $strace.= sprintf(" TTRACE['%s']='%s';\n", $k, str_replace("'", " ", $v));

$logcode = $action->lay->GenJsCode(true, true);

$out = str_replace("<head>", sprintf('<head><script>%s</script>', $strace) , $out);
$out = str_replace('</head>', sprintf('<script type="text/javascript">%s</script></head>', $logcode) , $out);
echo ($out);
$action->unregister("trace");
