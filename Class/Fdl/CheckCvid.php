<?php
/*
 * @author Anakeen
 * @package FDL
*/

class CheckCvid extends CheckData
{
    protected $folderName;
    /**
     * @var Doc
     */
    protected $doc;
    

    /**
     * @param array $data
     * @param Doc $doc
     * @return CheckCvid
     */
    function check(array $data, &$doc = null)
    {
        
        $this->folderName = $data[1];
        $this->doc = $doc;
        $this->checkCv();
        return $this;
    }
    /**
     * check id it is a search
     * @return void
     */
    protected function checkCv()
    {
        if ($this->folderName) {
            $d = new_doc('', $this->folderName);
            if (!$d->isAlive()) {
                $this->addError(ErrorCode::getError('CVID0001', $this->folderName, $this->doc->name));
            } elseif (!is_a($d, "\\Dcp\\Family\\CVDoc")) {
                $this->addError(ErrorCode::getError('CVID0002', $this->folderName, $this->doc->name));
            }
        }
    }
}
