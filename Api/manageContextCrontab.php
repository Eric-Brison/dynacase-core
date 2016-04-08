<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * API script to manipulate user crontab
 * @subpackage
 */
/**
 */

include_once ("WHAT/Class.Crontab.php");
include_once ("FDL/Lib.Util.php");

$usage = new ApiUsage();
$usage->setDefinitionText("API script to manipulate user crontab");
$cmd = $usage->addRequiredParameter("cmd", "command to execute", array(
    "list",
    "register",
    "unregister",
    "unregister-all"
));
$file = $usage->addOptionalParameter("file", "path to cronfile (needed for cmd=register|unregister)", null, NULL);
$user = $usage->addOptionalParameter("user", "id of user", null, NULL);
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

    case 'unregister-all':
        $crontab = new Crontab($user);
        if ($crontab->unregisterAll() === false) {
            exit(1);
        }
        break;
}

exit(0);
