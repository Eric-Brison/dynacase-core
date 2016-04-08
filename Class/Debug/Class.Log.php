<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Log information class
 *
 * @author Anakeen
 * @version $Id: Class.Log.php,v 1.15 2008/10/31 16:57:18 jerome Exp $
 * @package FDL
 * @subpackage CORE
 */
/**
 */
/**
 * Log manager
 * log message according to CORE_LOGLEVEL parameter
 * @class Log
 *
 */
class Log
{
    public $loghead;
    public $application;
    public $function;
    private $deb;
    private $fin;
    private $tic;
    private $ptext;
    /**
     * @var string Level to log
     */
    protected $logLevel = null;
    /**
     * Constant to set log to debug level
     * Debug level is used by Core.
     * It's used to assert taht Core works properly
     */
    const DEBUG = "D";
    /**
     * Constant to set log to callstack level
     */
    const CALLSTACK = "C";
    /**
     * Constant to set log to trace level
     * The trace level is a level reserved for user usage.
     * Core will never log with this level
     */
    const TRACE = "T";
    /**
     * Constant to set log to info level
     */
    const INFO = "I";
    /**
     * Constant to set log to warning level
     */
    const WARNING = "W";
    /**
     * Constant to set log to error level
     */
    const ERROR = "E";
    /**
     * Constant to set log to fatal level
     */
    const FATAL = "F";
    /**
     * Constant to set log to deprecated level
     */
    const DEPRECATED = "O";
    // ------------------------------------------------------------------------
    
    /**
     * @api initialize log manager
     * @param string $logfile
     * @param string $application
     * @param string $function
     */
    public function __construct($logfile = "", $application = "", $function = "")
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
    /**
     * log with debug level
     * @api log with debug level
     * @param string $string message text
     */
    public function debug($string)
    {
        $this->wlog(Log::DEBUG, $string);
    }
    /**
     * @param string $string message text
     */
    public function callstack($string)
    {
        $this->wlog(Log::CALLSTACK, $string);
    }
    /**
     * log with trace level
     * @api log with trace level
     * @param string $string mesage text
     */
    public function trace($string)
    {
        $this->wlog(Log::TRACE, $string);
    }
    /**
     * log with info level
     * @api log with info level
     * @param string $string message text
     */
    public function info($string)
    {
        $this->wlog(Log::INFO, $string);
    }
    /**
     * log with warning level
     * @api log with warning level
     * @param string $string message text
     */
    public function warning($string)
    {
        $this->wlog(Log::WARNING, $string);
    }
    /**
     * log with error level
     * @api log with error level
     * @param string $string message text
     */
    public function error($string)
    {
        $this->wlog(Log::ERROR, $string);
    }
    /**
     * log with fatal level
     * @api log with fatal level
     * @param string $string message text
     */
    public function fatal($string)
    {
        $this->wlog(Log::FATAL, $string);
    }
    /**
     * log with deprecated level
     * add callstack
     * @api log with deprecated level
     * @see Log
     * @param string $string message text
     */
    public function deprecated($string)
    {
        $this->wlog(Log::DEPRECATED, $string);
    }
    /**
     * @param string $logLevel
     */
    public function setLogLevel($logLevel)
    {
        $this->logLevel = $logLevel;
    }
    /**
     * @return string
     */
    public function getLogLevel()
    {
        if ($this->logLevel === null) {
            $this->logLevel = getParam("CORE_LOGLEVEL", "IWEF");
        }
        return $this->logLevel;
    }
    /**
     * to set start time
     * @param string $text prefix text to set for next tic/end
     */
    public function start($text = "")
    {
        $deb = gettimeofday();
        $this->deb = $deb["sec"] + $deb["usec"] / 1000000;
        $this->tic = $this->deb;
        $this->ptext = $text; // prefix
        
    }
    /**
     * log partial time
     * @see start
     * @param string $text text to log
     */
    public function tic($text)
    {
        $tic = gettimeofday();
        $now = $tic["sec"] + $tic["usec"] / 1000000;
        $duree = round($now - $this->tic, 3);
        $this->info("CHRONO-INT [$this->ptext]/[$text] : $duree");
        $this->tic = $now;
    }
    /**
     * log end time from last start
     * @param string $text text to log
     */
    public function end($text)
    {
        $fin = gettimeofday();
        $this->fin = $fin["sec"] + $fin["usec"] / 1000000;
        $duree = round($this->fin - $this->deb, 3);
        $this->info("CHRONO [$this->ptext]/[$text] : $duree");
    }
    
    public function push($string)
    {
        if (is_int(strpos($this->getLogLevel() , "C"))) {
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
    
    public function pop()
    {
        if (is_int(strpos($this->getLogLevel() , "C"))) {
            global $call_ind, $call_stack, $call_pre, $call_reqid;
            $call_pre = substr($call_pre, 0, strlen($call_pre) - 1);
            $call_ind-= 1;
            $this->callstack("($call_reqid) $call_pre : exiting  {$call_stack[$call_ind]}");
        }
    }
    /**
     * main log function
     * @param string $sta log code (one character : IWEFDOT)
     * @param string $str message to log
     * @param null $args unused
     * @param int $facility syslog level
     */
    public function wlog($sta, $str, $args = NULL, $facility = LOG_LOCAL6)
    {
        global $_SERVER;
        
        if (!$str) return;
        if (is_array($str)) $str = implode(", ", $str);
        if ($sta == "S" || (is_int(strpos($this->getLogLevel() , $sta)))) {
            $addr = isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : '';
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
                    case Log::DEBUG:
                        $pri = LOG_DEBUG;
                        break;

                    case Log::DEPRECATED:
                        $class = (isset($td[4]["class"])) ? $td[4]["class"] : '';
                        $td = @debug_backtrace(false);
                        if ($str) {
                            $str.= ' ';
                        }
                        $str.= sprintf("%s called in %s%s%s(), file %s:%s", isset($td[3]["function"]) ? $td[3]["function"] : '', $class, $class ? '::' : '', isset($td[4]["function"]) ? $td[4]["function"] : '', isset($td[3]["file"]) ? $td[3]["file"] : '', isset($td[3]["line"]) ? $td[3]["line"] : '');
                        $pri = LOG_INFO;
                        break;

                    case Log::INFO:
                        $pri = LOG_INFO;
                        break;

                    case Log::WARNING:
                        $pri = LOG_WARNING;
                        break;

                    case Log::ERROR:
                        $pri = LOG_ERR;
                        break;

                    case Log::FATAL:
                        $pri = LOG_ALERT;
                        break;

                    case Log::TRACE:
                        $pri = LOG_DEBUG;
                        break;

                    default:
                        $pri = LOG_NOTICE;
                }
                if (empty($_SERVER['HTTP_HOST'])) {
                    error_log(sprintf("%s LOG::$appf %s", date("d/m/Y H:i:s", time()) , $str));
                }
                openlog("{$appf}", 0, $facility);
                syslog($pri, "[{$addr}] " . $str);
                closelog();
                
                if ($sta == "E") {
                    error_log($str); // use apache syslog also
                    
                }
            }
        }
    }
} // Class.Log

?>
