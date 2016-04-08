<?php
/*
 * @author Anakeen
 * @package FDL
*/

class CheckWid extends CheckData
{
    protected $folderName;
    /**
     * @var Doc
     */
    protected $doc;
    /**
     * @param array $data
     * @param Doc $doc
     * @return CheckWid
     */
    function check(array $data, &$doc = null)
    {
        
        $this->folderName = isset($data[1]) ? $data[1] : null;
        $this->doc = $doc;
        $this->checkWorkflow();
        return $this;
    }
    /**
     * check id it is a search
     * @return void
     */
    protected function checkWorkflow()
    {
        if ($this->folderName) {
            try {
                $d = new_doc('', $this->folderName);
                if (!$d->isAlive()) {
                    $this->addError(ErrorCode::getError('WID0001', $this->folderName, $this->doc->name));
                } elseif (!is_a($d, "WDoc")) {
                    $this->addError(ErrorCode::getError('WID0002', $this->folderName, $this->doc->name));
                }
            }
            catch(Exception $e) {
                $this->addError(ErrorCode::getError('WID0003', $e->getMessage() , $this->doc->name));
            }
        }
    }
}
