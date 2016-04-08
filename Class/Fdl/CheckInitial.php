<?php
/*
 * @author Anakeen
 * @package FDL
*/

class CheckInitial extends CheckData
{
    protected $InitialName;
    protected $InitialValue = '';
    /**
     * @var Doc
     */
    protected $doc;
    /**
     * @param array $data
     * @param Doc $doc
     * @return CheckInitial
     */
    function check(array $data, &$doc = null)
    {
        $this->InitialName = trim(strtolower($data[1]));
        if (isset($data[2])) $this->InitialValue = trim($data[2]);
        $this->doc = $doc;
        $this->checkInitialName();
        $this->checkInitialValue();
        return $this;
    }
    /**
     * check Initial name syntax
     * @return void
     */
    protected function checkInitialName()
    {
        if ($this->InitialName) {
            if (!CheckAttr::checkAttrSyntax($this->InitialName)) {
                $this->addError(ErrorCode::getError('INIT0001', $this->InitialName, $this->doc->name));
            }
        } else {
            $this->addError(ErrorCode::getError('INIT0002', $this->doc->name));
        }
    }
    /**
     * check Initial value if seems to be method
     * @return void
     */
    protected function checkInitialValue()
    {
        if (preg_match('/^[a-z_0-9]*::/i', $this->InitialValue)) {
            $oParse = new parseFamilyMethod();
            $strucFunc = $oParse->parse($this->InitialValue, true);
            if ($err = $strucFunc->getError()) {
                
                $this->addError(ErrorCode::getError('INIT0003', $this->InitialName, $this->InitialValue, $this->doc->name, $err));
            }
        }
    }
}
