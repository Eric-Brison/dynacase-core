<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

class parseFamilyMethod extends parseFamilyFunction
{
    
    public $methodName = '';
    public $className = '';
    /**
     * @static
     * @param $funcCall
     * @return parseFamilyMethod
     */
    public function parse($funcCall, $noOut = false)
    {
        
        $this->initParse($funcCall);
        
        $methodName = trim(substr($funcCall, 0, $this->firstParenthesis));
        if ($this->checkParenthesis()) {
            if (strpos($methodName, '::') === false) {
                $this->setError(ErrorCode::getError('ATTR1251', $funcCall));
            } else {
                
                list($className, $methodName) = explode('::', $methodName, 2);
                $this->methodName = $methodName;
                $this->className = $className;
                
                if (!$this->isPHPName($methodName)) {
                    $this->setError(ErrorCode::getError('ATTR1252', $methodName));
                } elseif ($className && (!$this->isPHPName($className))) {
                    $this->setError(ErrorCode::getError('ATTR1253', $className));
                } else {
                    $inputString = substr($funcCall, $this->firstParenthesis + 1, ($this->lastParenthesis - $this->firstParenthesis - 1));
                    $this->inputString = $inputString;
                    
                    $this->parseArguments();
                    $this->parseOutput();
                    $this->limitOutputToOne();
                }
            }
        }
        
        return $this;
    }
    
    protected function limitOutputToOne()
    {
        if (count($this->outputs) > 1) {
            $this->setError(ErrorCode::getError('ATTR1254', $this->funcCall));
        }
    }
}
