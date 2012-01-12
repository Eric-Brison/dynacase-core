<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

class CheckReset extends CheckData
{
    protected $value;
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
     * @return CheckReset
     */
    function check(array $data, &$doc = null)
    {
        
        $this->value = strtolower($data[1]);
        $this->doc = $doc;
        $this->checkValue();
        return $this;
    }
    /**
     * check reset values
     * @return void
     */
    protected function checkValue()
    {
        if ($this->value) {
            if (!in_array($this->value, $this->authorizedKeys)) {
                $this->addError(ErrorCode::getError('RESE0001', $this->value, $this->doc->name));
            }
        }
    }
}
