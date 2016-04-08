<?php
/*
 * @author Anakeen
 * @package FDL
*/

class CheckCprofid extends CheckData
{
    protected $profilName;
    /**
     * @var Doc
     */
    protected $doc;
    
    protected $authorizedKeys = array(
        "attributes"
    );
    /**
     * @param array $data
     * @param Doc $doc
     * @return CheckCprofid
     */
    function check(array $data, &$doc = null)
    {
        
        $this->profilName = $data[1];
        $this->doc = $doc;
        $this->checkProfil();
        return $this;
    }
    /**
     * check id it is a search
     * @return void
     */
    protected function checkProfil()
    {
        if ($this->profilName) {
            $d = new_doc('', $this->profilName);
            if (!$d->isAlive()) {
                $this->addError(ErrorCode::getError('CPRF0001', $this->profilName, $this->doc->name));
            } elseif (!is_a($d, "Doc")) {
                $this->addError(ErrorCode::getError('CPRF0002', $this->profilName, $this->doc->name));
            }
        }
    }
}
