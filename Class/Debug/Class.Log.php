<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: Class.Log.php,v 1.11 2004/07/27 09:49:28 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
 /**
 */

// ---------------------------------------------------------------------------
//    O   Anakeen - 2000
//   O*O  Yannick Le Briquer, Marc Claverie
//    O   yannick.lebriquer@anakeen.com, marc.claverie@anakeen.com
// ---------------------------------------------------------------------------
// This program is free software; you can redistribute it and/or modify 
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or (at
//  your option) any later version.
//
// This program is distributed in the hope that it will be useful, but 
// WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
// or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License
// for more details.
// 
// You should have received a copy of the GNU General Public License along 
// with this program; if not, write to the Free Software Foundation, Inc.,
// 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
// ---------------------------------------------------------------------------
// $Id: Class.Log.php,v 1.11 2004/07/27 09:49:28 eric Exp $
// yannick.lebriquer@anakeen.com
// ---------------------------------------------------------------------------

$CLASS_LOG_PHP="";

Class Log {


// ------------------------------------------------------------------------
function Log($logfile="",$application="",$function="") {
  $this->usesyslog = 0;
  if ($logfile == "") {
    $this->usesyslog = 1;
  } else {
    $fd = fopen($logfile,"a");
    if (!$fd) {
      $this->usesyslog = 1;
      $this->error("Can't access $logfile, using syslog");
    } else {
      $this->logfile = $logfile;
      fclose($fd);
    }
  }
  $this->application = $application;
  $this->function = $function;
}

// ------------------------------------------------------------------------
function debug($string, $args=NULL) { $this->wlog("D",$string); }
function callstack($string, $args=NULL) { $this->wlog("C",$string); }
function info($string, $args=NULL) { $this->wlog("I",$string); }
function warning($string, $args=NULL) { $this->wlog("W",$string); }
function error($string, $args=NULL) { $this->wlog("E",$string); }
function fatal($string, $args=NULL) { $this->wlog("F",$string); }

function start($text="") {
   $deb=gettimeofday();
   $this->deb=$deb["sec"]+$deb["usec"]/1000000;
   $this->tic=$this->deb;
   $this->ptext=$text; // prefix
}

function tic($text) {
   $tic=gettimeofday();
   $now=$tic["sec"]+$tic["usec"]/1000000;
   $duree=round($now-$this->tic,3);
   $this->info("CHRONO-INT [$this->ptext]/[$text] : $duree");
   $this->tic=$now;
}

function end($text) {
   $fin=gettimeofday();
   $this->fin=$fin["sec"]+$fin["usec"]/1000000;
   $duree=round($this->fin-$this->deb,3);
   $this->info("CHRONO [$this->ptext]/[$text] : $duree");
}

function push($string) {
  global $CORE_LOGLEVEL;
  if (isset($CORE_LOGLEVEL) && is_int(strpos($CORE_LOGLEVEL, "C"))) {
    global $call_ind,$call_stack,$call_pre,$call_reqid;
    if (!isset($call_ind)) $call_ind=0;
    if (!isset($call_pre)) $call_pre="-";
    if (!isset($call_reqid)) $call_reqid=rand(1,100);
    $this->callstack("($call_reqid) $call_pre : entering $string");
    $call_stack[$call_ind]=$string;
    $call_ind +=1;
    $call_pre = $call_pre."-";
  }
}

function pop() {
  global $CORE_LOGLEVEL;
  if (isset($CORE_LOGLEVEL) && is_int(strpos($CORE_LOGLEVEL, "C"))) {
    global $call_ind,$call_stack,$call_pre,$call_reqid;
    $call_pre=substr($call_pre,0,strlen($call_pre)-1);
    $call_ind -= 1;
    $this->callstack("($call_reqid) $call_pre : exiting  {$call_stack[$call_ind]}");
  }
}

// ------------------------------------------------------------------------
function wlog($sta, $str, $args=NULL) {

  global $_SERVER; // use only syslog with HTTP
  global $REMOTE_ADDR, $CORE_LOGLEVEL;

  if (isset($CORE_LOGLEVEL) && is_int(strpos($CORE_LOGLEVEL, $sta))) {
    $appf = "[{$sta}] What";
    $appf .= ($this->application!=""?":".$this->application:"");
    $appf .= ($this->function!=""?":".$this->function:"");
    if (!$this->usesyslog) {
      $xx = date("d/m/Y H:i:s",time()) . " {$appf} [{$REMOTE_ADDR}] ";
      $xx = $xx . $str . "\n";
      $fd = fopen($this->logfile,"a");
      fputs($fd,$xx);
      fclose($fd);
    } else {
      switch($sta) {
      case "D" :
	$pri = LOG_NOTICE;
	break;
      case "I" :
	$pri = LOG_INFO;
	break;
      case "W" :
	$pri = LOG_WARNING;
	break;
      case "E" :
	$pri = LOG_ERR;
	break;
      case "F" :
	$pri = LOG_ALERT;
	break;
      default:
	$pri = LOG_NOTICE;
      }
      if ($_SERVER['HTTP_HOST'] == "") {
	$stderr = fopen('php://stderr', 'w');
	fwrite($stderr, "LOG::($sta)::".$str."\n");
      } else {
	define_syslog_variables();    
	openlog("{$appf}", 0, LOG_LOCAL6);
	syslog($pri, "[{$REMOTE_ADDR}] ".$str);
	closelog();

      }
    }
  }
 
}

} // Class.Log

?>
