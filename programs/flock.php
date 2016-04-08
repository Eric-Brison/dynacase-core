#!/usr/bin/env php
<?php
/*
 * @author Anakeen
 * @package CORE
*/
/**
 * Mimic util-linux's flock behaviour to lock on a file and execute a command.
 */

main($argv);

function usage($me)
{
    $msg = <<<EOF

Usage:

  $me <lockfile> <command>


EOF;
    printerr($msg);
}

function main(&$argv)
{
    $me = array_shift($argv);
    if (count($argv) < 2) {
        usage($me);
        exit(64);
    }
    $lockfile = array_shift($argv);
    $fh = fopen($lockfile, 'c+');
    if ($fh === false) {
        printerr(sprintf("Error: cannot open lock file '%s'.\n", $lockfile));
        exit(66);
    }
    if (flock($fh, LOCK_EX) === false) {
        printerr("Error: failed to get lock.\n");
        exit(1);
    }
    $cmd = join(' ', $argv);
    passthru($cmd, $ret);
    flock($fh, LOCK_UN);
    exit($ret);
}

function printerr($msg)
{
    file_put_contents('php://stderr', $msg);
}
