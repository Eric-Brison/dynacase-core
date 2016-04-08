<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp;

class ConsoleProgressOMeter
{
    protected $starttime = 0;
    protected $progress = 1;
    protected $max = 1;
    protected $interval = 1;
    protected $timeInterval = 0;
    protected $prevLineLen = 0;
    protected $prevLineTime = 0;
    protected $isInteractive = true;
    protected $updateProcessTitle = false;
    protected $prefix = '';
    
    public function __construct()
    {
        if (function_exists('posix_isatty')) {
            $this->setInteractive(posix_isatty(STDOUT));
        }
        
        return $this;
    }
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
        return $this;
    }
    public function setMax($max)
    {
        $max = (int)$max;
        if ($max >= 0) {
            $this->max = $max;
        }
        return $this;
    }
    public function setInterval($interval)
    {
        $interval = (int)$interval;
        if ($interval > 0) {
            $this->timeInterval = 0;
            $this->interval = $interval;
        }
        
        return $this;
    }
    public function setTimeInterval($interval)
    {
        $interval = (int)$interval;
        if ($interval > 0) {
            $this->timeInterval = $interval;
            $this->interval = 0;
        }
        
        return $this;
    }
    public function setInteractive($bool)
    {
        $this->isInteractive = ($bool === true);
        
        return $this;
    }
    public function setUpdateProcessTitle($prefix)
    {
        $this->updateProcessTitle = $prefix;
        
        return $this;
    }
    public function start($at = 0)
    {
        $at = (int)$at;
        if ($at >= 0) {
            $this->progress = $at;
        } else {
            $this->progress = 0;
        }
        $this->starttime = microtime(true);
        return $this->progress(0);
    }
    public function finish()
    {
        if ($this->progress < $this->max) {
            $this->progress($this->max);
        }
        if ($this->isInteractive) {
            print "\n";
        }
    }
    public function reset()
    {
        $this->start(0);
        return $this;
    }
    protected function isTimeToUpdateProgress($p)
    {
        if ($p == $this->max) {
            return true;
        }
        if ($this->interval > 0 && ($p % $this->interval == 0)) {
            return true;
        }
        if ($this->timeInterval > 0 && ((microtime(true) - $this->prevLineTime) > $this->timeInterval)) {
            return true;
        }
        return false;
    }
    public function progress($p)
    {
        $p = (int)$p;
        if ($p <= 0) {
            return $this;
        }
        if ($this->isTimeToUpdateProgress($p)) {
            $ratio = (($this->max == 0) ? 0 : $p / $this->max);
            $line = sprintf("%s%3d%% (%d/%d) [elapsed: %d sec. | remaining: %d sec. | ETA: %s]", ($this->prefix != '' ? $this->prefix . ' ' : '') , intval(100 * $ratio) , $p, $this->max, (microtime(true) - $this->starttime) , $this->eta($p, false) , $this->eta($p));
            if ($this->isInteractive) {
                print "\r" . $line;
                if (strlen($line) < $this->prevLineLen) {
                    print str_repeat(" ", $this->prevLineLen - strlen($line));
                }
            } else {
                print $line . "\n";
            }
            $this->updateProcessTitle($line);
            $this->prevLineLen = strlen($line);
            $this->prevLineTime = microtime(true);
        }
        $this->progress = $p;
        
        return $this;
    }
    protected function eta($done, $eta = true)
    {
        $now = microtime(true);
        $rate = ($now - $this->starttime) / $done;
        $remainingtime = ($this->max - $done) * $rate;
        if ($eta) {
            return date(DATE_RFC2822, $now + $remainingtime);
        }
        return $remainingtime;
    }
    protected function isProcessTitleEnabled()
    {
        if (!function_exists('cli_set_process_title')) {
            return false;
        }
        return ($this->updateProcessTitle !== false && $this->updateProcessTitle != '');
    }
    private function updateProcessTitle($suffix = '')
    {
        if ($this->isProcessTitleEnabled()) {
            $title = $this->updateProcessTitle;
            if ($suffix != '') {
                $title.= " - " . $suffix;
            }
            cli_set_process_title($title);
        }
        return $this;
    }
}
