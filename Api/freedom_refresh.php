<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: freedom_refresh.php,v 1.22 2008/12/12 17:48:25 eric Exp $
 * @package FDL
 * @subpackage
 */

error_log("*** API script 'freedom_refresh' is deprecated! You should use 'refreshDocuments' instead. ***");

$wsh = array_shift($argv);
if (substr($wsh, 0, 1) != '/') {
    $realwsh = realpath($wsh);
    if ($realwsh === false) {
        error_log("Error: could not get real path of '%s'.", $wsh);
        exit(1);
    }
    $wsh = $realwsh;
}
$args = array(
    escapeshellarg($wsh)
);
foreach ($argv as $arg) {
    if (preg_match('/^--api=/', $arg)) {
        $arg = '--api=refreshDocuments';
    }
    $args[] = escapeshellarg($arg);
}
$cmd = join(' ', $args);
$ret = 0;
$out = system($cmd, $ret);
if ($out === false) {
    error_log("Error: could not execute command '%s'.", $cmd);
    exit(1);
}
exit($ret);
