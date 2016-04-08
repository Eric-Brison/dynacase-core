#!/usr/bin/env php
<?php
/*
 * @author Anakeen
 * @package FDL
*/

$WIFF_ROOT = getenv("WIFF_ROOT");
if ($WIFF_ROOT === false) {
    print "WIFF_ROOT environment variable is not set!\n";
    exit(1);
}

$WIFF_CONTEXT_ROOT = getenv("WIFF_CONTEXT_ROOT");
if ($WIFF_CONTEXT_ROOT === false) {
    print "WIFF_CONTEXT_ROOT environment variable not set!\n";
    exit(1);
}

set_include_path(get_include_path() . PATH_SEPARATOR . $WIFF_CONTEXT_ROOT . PATH_SEPARATOR . "$WIFF_ROOT/include");

require_once ('WHAT/Lib.Common.php');
require_once ('WHAT/Class.Log.php');

main($WIFF_CONTEXT_ROOT, $argv);

function usage($me)
{
    print <<<EOF
Usage
-----

    $me <'check'|'reconfigure'> <'CORE_TMMPDIR'|'FREEDOM_UPLOADDIR'>


EOF;
    
    
}

function main($WIFF_CONTEXT_ROOT, &$argv)
{
    $me = array_shift($argv);
    if (count($argv) != 2) {
        usage($me);
        exit(1);
    }
    $cmd = array_shift($argv);
    $paramName = array_shift($argv);
    
    $ret = false;
    switch ($cmd) {
        case 'check':
            $ret = check($WIFF_CONTEXT_ROOT, $paramName);
            break;

        case 'reconfigure':
            $ret = reconfigure($WIFF_CONTEXT_ROOT, $paramName);
            break;

        default:
            usage($me);
    }
    exit(($ret === false) ? 1 : 0);
}

function check($WIFF_CONTEXT_ROOT, $paramName)
{
    $log = new Log("", "CORE");
    $paramValue = getCoreParam($paramName, false);
    if ($paramValue === false || !pathIsUnderContext($WIFF_CONTEXT_ROOT, $paramValue)) {
        $log->warning(sprintf("Parameter '%s' references a directory outside the context's root: '%s'.", $WIFF_CONTEXT_ROOT, $paramName, $paramValue));
        return false;
    }
    return true;
}

function reconfigure($WIFF_CONTEXT_ROOT, $paramName)
{
    $log = new Log("", "CORE");
    if (check($WIFF_CONTEXT_ROOT, $paramName) === true) {
        return true;
    }
    $log->warning(sprintf("Restoring default value for parameter '%s'.", $paramName));
    $err = simpleQuery('', sprintf('UPDATE paramv SET val = %s WHERE name = %s', pg_escape_literal('./var/tmp') , pg_escape_literal($paramName)));
    if ($err != '') {
        $msg = sprintf("Error setting '%s' to '%s': %s", $paramName, './var/tmp', $err);
        printf("%s\n", $msg);
        $log->warning($msg);
        return false;
    }
    return true;
}

function pathIsUnderContext($contextRoot, $path)
{
    $realPath = realpath($contextRoot);
    if ($realPath === false) {
        printf("Error: could not get realpath from context root '%s'!\n", $contextRoot);
        return false;
    }
    $contextRoot = $realPath;
    if (!file_exists($path)) {
        printf("Warning: path '%s' does not exists!\n", $path);
        return false;
    }
    $realPath = realpath($path);
    if ($realPath === false) {
        printf("Error: could not get realpath from path '%s'!\n", $path);
        return false;
    }
    $path = $realPath;
    return (strpos($path, $contextRoot) === 0);
}
