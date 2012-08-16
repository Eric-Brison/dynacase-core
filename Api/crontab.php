<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * API script to manipulate user crontab
 *
 * @author Anakeen
 * @version $Id: crontab.php,v 1.2 2009/01/16 15:51:35 jerome Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("WHAT/Class.Crontab.php");
include_once ("FDL/Lib.Util.php");

$usage = new ApiUsage();
$usage->setText("API script to manipulate user crontab");
$cmd = $usage->addNeeded("cmd", "command to execute", array(
    "list",
    "register",
    "unregister"
));
$file = $usage->addOption("file", "path to cronfile (needed for cmd=register|unregister)", null, NULL);
$user = $usage->addOption("user", "id of user", null, NULL);
$usage->verify();
/*function usage()
{
    print "\n";
    print "wsh --api=crontab --cmd=list [--user=<uid>]\n";
    print "wsh --api=crontab --cmd=<register|unregister> --file=<path/to/cronfile> [--user=<uid>]\n";
    print "\n";
}*/

switch ($cmd) {
    case 'list':
        $crontab = new Crontab($user);
        $ret = $crontab->listAll();
        if ($ret === FALSE) {
            exit(1);
        }
        break;

    case 'register':
        if ($file === NULL) {
            error_log("Error: missing --file argument");
            exit(1);
        }
        $crontab = new Crontab($user);
        $ret = $crontab->registerFile($file);
        if ($ret === FALSE) {
            exit(1);
        }
        break;

    case 'unregister':
        if ($file === NULL) {
            error_log("Error: missing --file argument");
            exit(1);
        }
        $crontab = new Crontab($user);
        $ret = $crontab->unregisterFile($file);
        if ($ret === FALSE) {
            exit(1);
        }
        break;
        /* default:
         usage();*/
}

exit(0);
?>