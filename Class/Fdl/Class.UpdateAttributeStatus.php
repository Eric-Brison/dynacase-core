<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * update attribut status management
 *
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 * Format document list to be easily used in
 * @class UpdateAttributeStatus
 * @code
 $newValue=4;
 $attrid='TST_ENUM';
 $s = new \SearchDoc('', 'TST_UPDTATTR');
 $s->setObjectReturn();
 $s->setSlice(34);
 $dl = new \DocumentList($s);
 $ua = new \UpdateAttribute();
 $ua->useCollection($dl);
 $comment = "coucoux";
 $ua->addHistoryComment($comment);
 $ua->addRevision('REvX');
 $statusFile= $ua->bgSetValue($attrid, $newValue);
 $sua= new UpdateAttributeStatus($statusFile);
 while (! $sua->isFinished()) {
 print $sua->getStatus();
 print ".";
 sleep(1);
 }
 * @endcode
 */
class UpdateAttributeStatus
{
    
    private $statusFile = '';
    private $content = null;
    const statusRunning = 1;
    const StatusFinished = 2;
    const statusUnknown = 3;
    
    public function __construct($statusFile)
    {
        $this->statusFile = $statusFile;
        if (!file_exists($this->statusFile)) throw new Exception(ErrorCode::getError("UPAT0003", $statusFile));
    }
    
    private function readStatus()
    {
        $this->content = file($this->statusFile);
    }
    /**
     * return file status content
     * one line by array items
     * @return string[]
     */
    public function getContent()
    {
        if ($this->content === null) $this->readStatus();
        return $this->content;
    }
    /**
     * return global status
     * update file status content
     * @return int StatusFinished, StatusRunning or statusUnknown
     */
    public function getStatus()
    {
        $this->readStatus();
        if ($this->content) {
            $last = end($this->content);
            if (strpos($last, 'END') > 0) return self::StatusFinished;
            $first = $this->content[0];
            if (strpos($first, 'BEGIN') > 0) return self::statusRunning;
        }
        return self::statusUnknown;
    }
    /**
     * get lines which match code
     * @param string $code
     * @return string[]
     */
    public function getCodeLines($code)
    {
        if ($this->content === null) $this->readStatus();
        $lines = array();
        foreach ($this->content as $line) {
            if (preg_match(sprintf("/^[0-9T:-]{19} [\w-]* ?%s/u", preg_quote($code)) , $line)) $lines[] = $line;
        }
        return $lines;
    }
    /**
     * get last message from file status
     * @return UpdateAttributeStatusLine
     */
    public function getLastMessage()
    {
        $l = new UpdateAttributeStatusLine();
        list($l->date, $l->processCode, $l->message) = explode(' ', trim(end($this->content)) , 3);
        
        return $l;
    }
    /**
     * return error messages
     * empty string if not
     * @return string error messages
     */
    public function getError()
    {
        $r = $this->getCodeLines("ERROR");
        if ($r) {
            
            return implode("\n", $r);
        }
        return '';
    }
    /**
     * get all status for each documents
     * return null is no processing doduments
     * @return null|UpdateAttributeResults[]
     */
    public function getResults()
    {
        $r = $this->getCodeLines("logStatusReport PHP");
        if ($r) {
            $pos = mb_strpos($r[0], ':', 20);
            $s = mb_substr($r[0], $pos + 1);
            return unserialize($s);
        }
        return null;
    }
    /**
     * return true is status file contains message that indicates end of processing
     * @return bool
     */
    public function isFinished()
    {
        return $this->getStatus() == self::StatusFinished;
    }
}
class UpdateAttributeStatusLine
{
    public $date;
    public $processCode;
    public $message;
    public function __toString()
    {
        return $this->date . " " . $this->processCode . " " . $this->message;
    }
}
