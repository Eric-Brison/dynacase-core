<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Dcp;

class ConsoleProgressOMeter
{
    protected $starttime = 0;
    protected $progress = 1;
    protected $max = 1;
    protected $interval = 1;
    protected $prevLineLen = 0;
    public function setMax($max)
    {
        $max = (int)$max;
        if ($max > 0) {
            $this->max = $max;
        }
        return $this;
    }
    public function setInterval($interval)
    {
        $interval = (int)$interval;
        if ($interval > 0) {
            $this->interval = $interval;
        }
        
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
        $this->progress($this->max);
        print "\n";
    }
    public function reset()
    {
        $this->start(0);
        return $this;
    }
    public function progress($p)
    {
        $p = (int)$p;
        if ($p <= 0) {
            return $this;
        }
        if ($p % $this->interval == 0 || $p == $this->max) {
            $line = sprintf("%3d%% (%d/%d) [elapsed: %d sec. | remaining: %d sec. | ETA: %s]", intval(100 * $p / $this->max) , $p, $this->max, (microtime(true) - $this->starttime) , $this->eta($p, false) , $this->eta($p));
            print "\r" . $line;
            if (strlen($line) < $this->prevLineLen) {
                print str_repeat(" ", $this->prevLineLen - strlen($line));
            }
            $this->prevLineLen = strlen($line);
        }
        
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
}
