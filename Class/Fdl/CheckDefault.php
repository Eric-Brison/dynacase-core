<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

class CheckDefault extends CheckData
{
    protected $defaultName;
    protected $defaultValue = '';
    /**
     * @var Doc
     */
    protected $doc;
    /**
     * @param array $data
     * @param Doc $doc
     * @return CheckDefault
     */
    function check(array $data, &$doc = null)
    {
        $this->defaultName = trim(strtolower($data[1]));
        if (isset($data[2])) $this->defaultValue = trim($data[2]);
        $this->doc = $doc;
        $this->checkDefaultName();
        $this->checkDefaultValue();
        return $this;
    }
    /**
     * check default name syntax
     * @return void
     */
    protected function checkDefaultName()
    {
        if ($this->defaultName) {
            if (!CheckAttr::checkAttrSyntax($this->defaultName)) {
                $this->addError(ErrorCode::getError('DFLT0001', $this->defaultName, $this->doc->name));
            }
        } else {
            
            $this->addError(ErrorCode::getError('DFLT0002', $this->doc->name));
        }
    }
    /**
     * check default value if seems to be method
     * @return void
     */
    protected function checkDefaultValue()
    {
        if (preg_match('/^[a-z_0-9]*::/i', $this->defaultValue)) {
            $oParse = new parseFamilyMethod();
            $strucFunc = $oParse->parse($this->defaultValue, true);
            if ($err = $strucFunc->getError()) {
                
                $this->addError(ErrorCode::getError('DFLT0003', $this->defaultName, $this->defaultValue, $this->doc->name, $err));
            }
        }
    }
}
