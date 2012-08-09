<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

class CheckOrder extends CheckData
{
    /**
     * @var string family reference
     */
    protected $famName;
    /**
     * @var array
     */
    protected $attrIds;
    /**
     * @var DocFam
     */
    protected $family;
    /**
     * @param array $data
     * @param Doc $doc
     * @return CheckDoc
     */
    function check(array $data, &$extra = null)
    {
        
        $this->famName = isset($data[1]) ? trim($data[1]) : null;
        $this->attrIds = getOrder($data);
        
        $this->CheckOrderFamily();
        if (!$this->hasErrors()) $this->CheckOrderAttribute();
        
        return $this;
    }
    /**
     * check
     * check
     * @return void
     */
    protected function CheckOrderFamily()
    {
        if ($this->famName) {
            if (!$this->checkName($this->famName)) {
                $this->addError(ErrorCode::getError('ORDR0001', $this->famName));
            } else {
                try {
                    $this->family = new_doc(getDbAccess() , $this->famName);
                    if (!$this->family->isAlive()) {
                        $this->addError(ErrorCode::getError('ORDR0002', $this->famName));
                    } else {
                        if ($this->family->doctype != 'C') {
                            $this->addError(ErrorCode::getError('ORDR0003', $this->famName));
                        } else {
                            $canCreateError = $this->family->control('create');
                            if ($canCreateError) $this->addError(ErrorCode::getError('ORDR0004', $this->famName));
                        }
                    }
                }
                catch(Exception $e) {
                    $this->addError(ErrorCode::getError('ORDR0005', $this->famName, $e->getMessage()));
                }
            }
        } else {
            
            $this->addError(ErrorCode::getError('ORDR0006'));
        }
    }
    /**
     * check logical name
     * @return void
     */
    protected function CheckOrderAttribute()
    {
        if ($this->family) {
            foreach ($this->attrIds as $aid) {
                if ($aid && (!$this->family->getAttribute($aid)) && (strpos($aid, "extra:") !== 0)) {
                    $this->addError(ErrorCode::getError('ORDR0100', $aid, $this->family->name));
                }
            }
        }
    }
    
    private function checkName($name)
    {
        if ($name && (!is_numeric($name))) {
            if (!preg_match('/^[a-z][a-z0-9_]*$/i', $name)) {
                return false;
            }
        }
        return true;
    }
}
