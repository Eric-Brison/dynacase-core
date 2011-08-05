<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 * Set of usefull debug functions
 *
 * @author Anakeen 2008
 * @version $Id: wdebug.php,v 1.2 2008/12/11 15:16:21 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package WHAT
 * @subpackage CORE
 */
/**
 */

global $SQLDELAY, $SQLDEBUG;
global $TSQLDELAY;
$SQLDEBUG = true;

function dtic($text = "")
{
    static $ptic = 0;
    static $ptic0 = 0;
    
    $deb = gettimeofday();
    $tuc1 = $deb["sec"] + $deb["usec"] / 1000000;
    if ($ptic == 0) {
        $ptic = $tuc1;
        $ptic0 = $tuc1;
    }
    
    $msg = sprintf("%s : %.03f -  %.03f ", $text, ($tuc1 - $ptic) , ($tuc1 - $ptic0));
    $ptic = $tuc1;
    return $msg;
}

function dtrace($text)
{
    global $trace;
    global $TSQLDELAY;
    $tsql = count($TSQLDELAY);
    $trace[] = dtic($text) . "#$tsql";
}

function stacktrace($level = 3, $uplevel = 1)
{
    if (!function_exists('xdebug_get_function_stack')) return '';
    $stack = xdebug_get_function_stack();
    $t = array();
    foreach ($stack as $k => $v) {
        $t[] = sprintf("[%s:%d]%s", basename($v["file"]) , $v["line"], $v["function"]);
    }
    $l = (-1 - $level);
    $t = array_slice($t, $l, -$uplevel);
    return implode("/<br>", $t);
}
function dmtrace($text = "", $level = 3)
{
    dtrace(stacktrace($level, 2) . ':' . $text);
}

function printdtrace()
{
    
    global $trace;
    foreach ($trace as $k => $v) {
        //[test.php:26]AddFile/<br>[Class.Dir.php:276]updateFldRelations:updateFldRelations : 0.075 -  0.075 #17
        if (preg_match("/(.*):([^-]+)-([^#]+)#(.*)$/", $v, $reg)) {
            printf("%30.30s : %.03f | %02.03f | %d\n", substr($reg[1], -30) , $reg[2], $reg[3], $reg[4]);
        }
    }
}
?>