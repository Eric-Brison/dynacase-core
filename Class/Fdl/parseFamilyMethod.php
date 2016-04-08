<?php
/*
 * @author Anakeen
 * @package FDL
*/

class parseFamilyMethod extends parseFamilyFunction
{
    
    public $methodName = '';
    public $className = '';
    /**
     * @static
     * @param $methCall
     * @return parseFamilyMethod
     */
    public function parse($methCall, $noOut = false)
    {
        
        $this->initParse($methCall);
        
        $methodName = trim(substr($methCall, 0, $this->firstParenthesis));
        if ($this->checkParenthesis()) {
            if (strpos($methodName, '::') === false) {
                $this->setError(ErrorCode::getError('ATTR1251', $methCall));
            } else {
                
                list($className, $methodName) = explode('::', $methodName, 2);
                $this->methodName = $methodName;
                $this->className = $className;
                
                if (!$this->isPHPName($methodName)) {
                    $this->setError(ErrorCode::getError('ATTR1252', $methodName));
                } elseif ($className && (!$this->isPHPClassName($className))) {
                    $this->setError(ErrorCode::getError('ATTR1253', $className));
                } else {
                    $inputString = substr($methCall, $this->firstParenthesis + 1, ($this->lastParenthesis - $this->firstParenthesis - 1));
                    $this->inputString = $inputString;
                    
                    $this->parseArguments();
                    $this->parseOutput();
                    if ($noOut) $this->limitOutputToZero();
                    else $this->limitOutputToOne();
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
    
    protected function limitOutputToZero()
    {
        if (count($this->outputs) > 0) {
            $this->setError(ErrorCode::getError('ATTR1255', $this->funcCall));
        } elseif ($this->lastSemiColumn > $this->lastParenthesis) {
            $this->setError(ErrorCode::getError('ATTR1255', $this->funcCall));
        }
    }
}
