<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

class CheckBegin extends CheckData
{
    protected $parentName;
    protected $famId;
    protected $famClass;
    protected $famTitle;
    protected $famName;
    /**
     * @var Doc
     */
    protected $doc;
    /**
     * @param array $data
     * @param Doc $doc
     * @return CheckBegin
     */
    function check(array $data, &$doc = null)
    {
        
        $this->parentName = ($data[1] == "--" || $data[1] == "-") ? "" : $data[1];
        $this->famId = isset($data[3]) ? $data[3] : 0;
        $this->famTitle = isset($data[2]) ? $data[2] : null;
        $this->famClass = isset($data[4]) ? $data[4] : null;
        $this->famName = isset($data[5]) ? $data[5] : '';
        $this->checkName();
        $this->checkInheritance();
        $this->checkTitle();
        
        return $this;
    }
    /**
     * check class file when it is a workflow
     * @param array $data
     * @param Doc $doc
     * @return CheckBegin
     */
    function checkClass(array $data, &$doc = null)
    {
        $this->doc = $doc;
        if (strstr($doc->usefor, 'W')) {
            $checkW = new CheckWorkflow($doc->classname, $doc->name);
            $checkCr = $checkW->verifyWorkflowClass();
            if (count($checkCr) > 0) {
                $this->addError(implode("\n", $checkCr));
            }
        } elseif ($this->famClass) {
            $this->checkClassFile($this->famClass);
        }
        
        return $this;
    }
    
    private function getClassFile($className)
    {
        return sprintf('FDL/Class.%s.php', $className);
    }
    private function checkClassFile($phpfile)
    {
        if ($phpfile) {
            $fileName = realpath($this->getClassFile($phpfile));
            if ($fileName) {
                // Get the shell output from the syntax check command
                exec(sprintf('php -n -l %s 2>&1', escapeshellarg($fileName)) , $output, $status);
                if ($status != 0) {
                    $this->addError(ErrorCode::getError('FAM0400', $this->getClassFile($phpfile) , $this->famName, implode("\n", $output)));
                }
            } else {
                $this->addError(ErrorCode::getError('FAM0401', $this->getClassFile($phpfile) , $this->famName));
            }
        }
    }
    
    protected function checkName()
    {
        if (!$this->famName) {
            $this->addError(ErrorCode::getError('FAM0500', $this->famTitle));
        } elseif (!preg_match('/^[a-z][a-z0-9_]{1,63}$/i', $this->famName)) {
            $this->addError(ErrorCode::getError('FAM0501', $this->famName));
        } else {
            $f = getTDoc('', $this->famName);
            if ($f && $f["doctype"] != 'C') {
                $this->addError(ErrorCode::getError('FAM0502', $this->famName, $f["title"]));
            }
        }
    }
    
    protected function checkTitle()
    {
        if ($this->famTitle) {
            if (mb_strlen($this->famTitle) > 255) {
                $this->addError(ErrorCode::getError('FAM0200', $this->famTitle, $this->famName));
            } elseif (preg_match("/\n|\t|\r/", $this->famTitle)) {
                $this->addError(ErrorCode::getError('FAM0201', $this->famTitle, $this->famName));
            }
        }
    }
    
    protected function checkInheritance()
    {
        if ($this->parentName) {
            if ($this->famName == $this->parentName) {
                $this->addError(ErrorCode::getError('FAM0101', $this->famName));
            } else {
                $p = getTdoc('', $this->parentName);
                if (!$p) {
                    $this->addError(ErrorCode::getError('FAM0100', $this->parentName, $this->famName));
                } elseif ($p["doctype"] != 'C') {
                    $this->addError(ErrorCode::getError('FAM0104', $this->parentName, $this->famName));
                } else {
                    
                    $me = getTdoc('', $this->famName);
                    if ($me) {
                        $fromId = $me['fromid'];
                        $fromName = getNameFromId(getDbAccess() , $fromId);
                        if (($fromName != $this->parentName) && ($fromId != $this->parentName)) {
                            //print_r($p);
                            $this->addError(ErrorCode::getError('FAM0102', $fromName, $this->parentName, $this->famName));
                        }
                    }
                }
            }
        }
    }
}
