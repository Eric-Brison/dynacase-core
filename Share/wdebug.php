<?php
/**
 * Set of usefull debug functions
 *
 * @author Anakeen 2008
 * @version $Id: wdebug.php,v 1.1 2008/12/11 15:13:07 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
/**
 */
function dtic($text="") {
  static $ptic=0;
  static $ptic0=0;

  $deb=gettimeofday();
  $tuc1= $deb["sec"]+$deb["usec"]/1000000;
  if ($ptic==0) {
    $ptic=$tuc1;
    $ptic0=$tuc1;
  }

  $msg= sprintf("%s : %.03f -  %.03f ",$text,($tuc1-$ptic), ($tuc1-$ptic0));
  $ptic=$tuc1;
  return $msg;
}

function dtrace($text) {
  global $trace;
  global $TSQLDELAY;
  $tsql=count($TSQLDELAY);
  $trace[]=dtic($text). "#$tsql";
}

function stacktrace($level=3) {
  $stack=xdebug_get_function_stack();
  $t=array();
  foreach ($stack as $k=>$v) {
    $t[]= sprintf("[%s:%d]%s",
		  basename($v["file"]),
		  $v["line"],
		  $v["function"]);    
  }
  $l=(-1 -$level);
  $t= array_slice($t,$l,-1);
  return implode("/<br>",$t);
}
?>