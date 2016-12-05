<?php
/*
 * @author Anakeen
 * @package FDL
*/

class CheckDocATag extends CheckData
{
    protected $tagAction;
    protected $docid;
    /**
     * @var Doc
     */
    protected $doc;
    protected $firstATag = '';
    /**
     * @param array $data
     * @param Doc $doc
     * @return CheckDocATag
     */
    function check(array $data, &$doc = null)
    {
        if (!isset($data[3])) {
            $this->addError(ErrorCode::getError('ATAG0004'));
        } else {
            $this->tagAction = $data[3];
            $this->docid = $data[1];
            if (isset($data[4])) {
                $this->firstATag = $data[4];
            }
            $this->checkDocid();
            if (!$this->errors) {
                $this->checkAction();
                $this->checkTagContent($data);
            }
        }
        return $this;
    }
    /**
     * check if doc is alive
     * @return void
     */
    protected function checkDocid()
    {
        if (!$this->docid) {
            $this->addError(ErrorCode::getError('ATAG0002', $this->firstATag));
        }
        
        $this->doc = new_doc("", $this->docid);
        if (!$this->doc->isAlive()) {
            
            $this->addError(ErrorCode::getError('ATAG0003', $this->docid));
        }
    }
    /**
     * check if action is available
     * @return void
     */
    protected function checkAction()
    {
        $allowedAction = ["ADD", "DELETE", "SET"];
        if ($this->tagAction && !in_array($this->tagAction, $allowedAction)) {
            $this->addError(ErrorCode::getError('ATAG0001', $this->tagAction, $this->doc->getTitle() , implode(",", $allowedAction)));
        }
    }
    
    protected function checkTagContent($data)
    {
        $i = 4;
        while (!empty($data[$i])) {
            if (strpos($data[$i], "\n") !== false) {
                $this->addError(ErrorCode::getError('ATAG0005', $this->doc->getTitle() , $data[$i]));
            }
            $i++;
        }
    }
}
