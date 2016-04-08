<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * importation of documents
 *
 * @author Anakeen
 * @version $Id: freedom_import.php,v 1.9 2008/11/13 16:49:16 eric Exp $
 * @package FDL
 * @subpackage WSH
 */

error_log("*** API script 'freedom_import' is deprecated! You should use 'importDocuments' instead. ***");

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
        $arg = '--api=importDocuments';
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
