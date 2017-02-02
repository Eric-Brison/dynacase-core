#!/usr/bin/env php
<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * WHAT SHELL
 *
 * @author Anakeen
 * @version $Id: wsh.php,v 1.35 2008/05/06 08:43:33 jerome Exp $
 * @package FDL
 */

ini_set("max_execution_time", "0");
require_once 'WHAT/Lib.Prefix.php';
require_once 'Class.Action.php';
require_once 'Class.Application.php';
require_once 'Class.Session.php';
require_once 'Class.Log.php';
require_once 'Lib.Main.php';

enable_wsh_safetybelts();

checkWshExecUid(__FILE__);

function print_usage()
{
    print "Usage\twsh.php --app=APPLICATION --action=ACTION [--ARG=VAL] ...:  execute an action\n" . "\twsh.php --api=API [--ARG=VAL] ....   :  execute an api function\n" . "\twsh.php --listapi                     : view api list\n";
}

wbar(1, -1, "initialisation");
$log = new Log("", "index.php");

$CoreNull = "";
// get param
global $_GET;
global $_SERVER;

if (isset($_SERVER['HTTP_HOST'])) {
    print "<BR><H1>:~(</H1>";
    exit(1);
}
if (count($argv) == 1) {
    print_usage();
    
    exit(1);
}

foreach ($argv as $k => $v) {
    
    if (preg_match("/--([^=]+)=(.*)/", $v, $reg)) {
        if (substr($reg[1], -2) == "[]") {
            $_GET[substr($reg[1], 0, -2) ][] = $reg[2];
        } else {
            $_GET[$reg[1]] = $reg[2];
        }
    } else if (preg_match("/--(.+)/", $v, $reg)) {
        if ($reg[1] == "listapi") {
            print "application list :\n";
            echo "\t- ";
            echo str_replace("\n", "\n\t- ", shell_exec(sprintf("cd %s/API;ls -1 *.php| cut -f1 -d'.'", escapeshellarg(DEFAULT_PUBDIR))));
            echo "\n";
            exit;
        }
        $_GET[$reg[1]] = true;
    }
}

if ((empty($_GET["api"])) && (empty($_GET["app"]) || empty($_GET["action"]))) {
    print_usage();
    exit(1);
}

$core = new Application();
if ($core->dbid < 0) {
    print "Cannot access to main database";
    exit(1);
}

if (isset($_GET["userid"])) { //special user
    if (!is_numeric($_GET["userid"])) {
        $core->user = new Account();
        $core->user->setLoginName($_GET["userid"]);
    } else {
        $core->user = new Account("", $_GET["userid"]);
    }
}
$core->Set("CORE", $CoreNull);
$core->session = new Session();
if (!isset($_GET["userid"])) $core->user = new Account("", 1); //admin
ini_set("memory_limit", -1);

initMainVolatileParam($core);
initExplorerParam($core);
/**
 * @var Action $action
 */
try {
    if (isset($_GET["app"])) {
        $appl = new Application();
        $appl->Set($_GET["app"], $core);
    } else {
        $appl = $core;
    }

    $action = new Action();
    if (isset($_GET["action"])) {
        $action->Set($_GET["action"], $appl);
    } else {
        $action->Set("", $appl);
    }

    if (!$core->user->isAffected()) {
        throw new Dcp\Core\Exception("CORE0013", $_GET["userid"]);
    }

    if ($core->user->status === "D") {
        throw new Dcp\Core\Exception("CORE0014", $_GET["userid"]);
    }

    if ($action->canExecute("CORE_ADMIN_ROOT", "CORE_ADMIN") === '') {
        // Authorize administrators to execute admin actions
        $action->parent->setAdminMode();
    }
}
catch(Dcp\Exception $e) {
    _wsh_exception_handler($e);
}
// init for gettext
setLanguage($action->Getparam("CORE_LANG"));

if (isset($_GET["api"])) {
    $apifile = trim($_GET["api"]);
    if (!file_exists(sprintf("%s/API/%s.php", DEFAULT_PUBDIR, $apifile))) {
        echo sprintf(_("API file %s not found\n") , "API/" . $apifile . ".php");
        exit(4);
    } else {
        try {
            include ("API/" . $apifile . ".php");
        }
        catch(Dcp\ApiUsage\Exception $e) {
            switch ($e->getDcpCode()) {
                case "CORE0002":
                    echo sprintf(_("Error : %s\n") , $e->getDcpMessage());
                    _wsh_exception_handler($e, false);
                    exit(1);
                    break;

                case "CORE0003":
                    echo sprintf($e->getDcpMessage());
                    exit(0);
                    break;

                default:
                    echo sprintf($e->getDcpMessage());
                    _wsh_exception_handler($e, false);
                    exit(1);
            }
        }
        catch(Dcp\Exception $e) {
            _wsh_exception_handler($e);
        }
        catch(Exception $e) {
            _wsh_exception_handler($e);
        }
    }
} else {
    if (!isset($_GET["wshfldid"])) {
        try {
            echo ($action->execute());
        }
        catch(Dcp\ApiUsage\Exception $e) {
            switch ($e->getDcpCode()) {
                case "CORE0002":
                    echo sprintf(_("Error : %s\n") , $e->getDcpMessage());
                    exit(1);
                    break;

                case "CORE0003":
                    echo sprintf($e->getDcpMessage());
                    exit(0);
                    break;

                default:
                    echo sprintf($e->getDcpMessage());
                    exit(1);
            }
        }
        catch(Dcp\Exception $e) {
            _wsh_exception_handler($e);
        }
        catch(Exception $e) {
            _wsh_exception_handler($e);
        }
    } else {
        // REPEAT EXECUTION FOR FREEDOM FOLDERS
        $dbaccess = $appl->dbaccess;
        if ($dbaccess == "") {
            print "Database not found : appl->dbaccess";
            exit;
        }
        include_once ("FDL/Class.Doc.php");
        $http_iddoc = "id"; // default correspondance
        if (isset($_GET["wshfldhttpdocid"])) $http_iddoc = $_GET["wshfldhttpdocid"];
        /**
         * @var Dir $fld
         */
        $fld = new_Doc($dbaccess, $_GET["wshfldid"]);
        $ld = $fld->getContent();
        foreach ($ld as $k => $v) {
            $_GET[$http_iddoc] = $v["id"];
            try {
                echo ($action->execute());
            }
            catch(Exception $e) {
                switch ($e->getCode()) {
                    case THROW_EXITERROR:
                        errorLogException($e);
                        break;

                    default:
                        errorLogException($e);
                }
            }
            echo "<hr>";
        }
    }
}

wbar(-1, -1, "completed");

return (0);
