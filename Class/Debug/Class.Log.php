<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Log information class
 *
 * @author Anakeen 2000
 * @version $Id: Class.Log.php,v 1.15 2008/10/31 16:57:18 jerome Exp $
 * @license http://www.gnu.org/licenses/lgpl-3.0.html GNU Lesser General Public License
 * @package FDL
 * @subpackage CORE
 */
/**
 */
// ---------------------------------------------------------------------------
// $Id: Class.Log.php,v 1.15 2008/10/31 16:57:18 jerome Exp $
// yannick.lebriquer@anakeen.com
// ---------------------------------------------------------------------------
$CLASS_LOG_PHP = "";

class Log
{
    
    public $loghead;
    public $application;
    public $function;
    // ------------------------------------------------------------------------
    function Log($logfile = "", $application = "", $function = "")
    {
        $this->usesyslog = 0;
        if ($logfile == "") {
            $this->usesyslog = 1;
        } else {
            $fd = fopen($logfile, "a");
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
    function debug($string, $args = NULL)
    {
        $this->wlog("D", $string);
    }
    function callstack($string, $args = NULL)
    {
        $this->wlog("C", $string);
    }
    function info($string, $args = NULL)
    {
        $this->wlog("I", $string);
    }
    function warning($string, $args = NULL)
    {
        $this->wlog("W", $string);
    }
    function error($string, $args = NULL)
    {
        $this->wlog("E", $string);
    }
    function fatal($string, $args = NULL)
    {
        $this->wlog("F", $string);
    }
    function deprecated($string, $args = NULL)
    {
        $this->wlog("O", $string);
    }
    
    function start($text = "")
    {
        $deb = gettimeofday();
        $this->deb = $deb["sec"] + $deb["usec"] / 1000000;
        $this->tic = $this->deb;
        $this->ptext = $text; // prefix
        
    }
    
    function tic($text)
    {
        $tic = gettimeofday();
        $now = $tic["sec"] + $tic["usec"] / 1000000;
        $duree = round($now - $this->tic, 3);
        $this->info("CHRONO-INT [$this->ptext]/[$text] : $duree");
        $this->tic = $now;
    }
    
    function end($text)
    {
        $fin = gettimeofday();
        $this->fin = $fin["sec"] + $fin["usec"] / 1000000;
        $duree = round($this->fin - $this->deb, 3);
        $this->info("CHRONO [$this->ptext]/[$text] : $duree");
    }
    
    function push($string)
    {
        global $CORE_LOGLEVEL;
        if (isset($CORE_LOGLEVEL) && is_int(strpos($CORE_LOGLEVEL, "C"))) {
            global $call_ind, $call_stack, $call_pre, $call_reqid;
            if (!isset($call_ind)) $call_ind = 0;
            if (!isset($call_pre)) $call_pre = "-";
            if (!isset($call_reqid)) $call_reqid = rand(1, 100);
            $this->callstack("($call_reqid) $call_pre : entering $string");
            $call_stack[$call_ind] = $string;
            $call_ind+= 1;
            $call_pre = $call_pre . "-";
        }
    }
    
    function pop()
    {
        global $CORE_LOGLEVEL;
        if (isset($CORE_LOGLEVEL) && is_int(strpos($CORE_LOGLEVEL, "C"))) {
            global $call_ind, $call_stack, $call_pre, $call_reqid;
            $call_pre = substr($call_pre, 0, strlen($call_pre) - 1);
            $call_ind-= 1;
            $this->callstack("($call_reqid) $call_pre : exiting  {$call_stack[$call_ind]}");
        }
    }
    // ------------------------------------------------------------------------
    function wlog($sta, $str, $args = NULL, $facility = LOG_LOCAL6)
    {
        
        global $_SERVER;
        global $CORE_LOGLEVEL;
        
        if (!$str) return;
        if (is_array($str)) $str = implode(", ", $str);
        if ($sta == "S" || (isset($CORE_LOGLEVEL) && is_int(strpos($CORE_LOGLEVEL, $sta)))) {
            $addr = $_SERVER["REMOTE_ADDR"];
            $appf = "[{$sta}] Dynacase";
            $appf.= ($this->application != "" ? ":" . $this->application : "");
            $appf.= ($this->function != "" ? ":" . $this->function : "");
            $str = ' ' . $this->loghead . ': ' . $str;
            if (!$this->usesyslog) {
                $xx = date("d/m/Y H:i:s", time()) . " {$appf} [{$addr}] ";
                $xx = $xx . $str . "\n";
                $fd = fopen($this->logfile, "a");
                fputs($fd, $xx);
                fclose($fd);
            } else {
                switch ($sta) {
                    case "D":
                        $pri = LOG_DEBUG;
                        break;

                    case "O":
                        $td = @debug_backtrace(false);
                        $str.= sprintf("%s called in %s%s%s(), file %s:%s", $td[3]["function"], $td[4]["class"], $td[4]["class"] ? '::' : '', $td[4]["function"], $td[4]["file"], $td[4]["line"]);
                    case "I":
                        $pri = LOG_INFO;
                        break;

                    case "W":
                        $pri = LOG_WARNING;
                        break;

                    case "E":
                        $pri = LOG_ERR;
                        break;

                    case "F":
                        $pri = LOG_ALERT;
                        break;

                    default:
                        $pri = LOG_NOTICE;
                }
                if ($_SERVER['HTTP_HOST'] == "") {
                    error_log(sprintf("%s LOG::$appf %s", date("d/m/Y H:i:s", time()) , $str));
                }
                openlog("{$appf}", 0, $facility);
                syslog($pri, "[{$addr}] " . $str);
                closelog();
            }
        }
    }
} // Class.Log

?>
