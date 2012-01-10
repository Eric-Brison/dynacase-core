<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

class parseFamilyFunction
{
    
    public $functionName = '';
    public $appName = '';
    public $funcCall = '';
    public $inputString = '';
    public $outputString = '';
    /**
     * @var inputArgument[]
     */
    public $inputs = array();
    public $outputs = array();
    protected $error = '';
    protected $firstParenthesis;
    protected $lastParenthesis;
    protected $lastSemiColumn;
    
    public function getError()
    {
        return $this->error;
    }
    
    protected function setError($error)
    {
        return $this->error = $error;
    }
    
    protected function initParse($funcCall)
    {
        $this->funcCall = $funcCall;
        $this->firstParenthesis = strpos($funcCall, '(');
        $this->lastParenthesis = strrpos($funcCall, ')');
        $this->lastSemiColumn = strrpos($funcCall, ':');
    }
    
    protected function checkParenthesis()
    {
        if (($this->firstParenthesis === false) || ($this->lastParenthesis === false) || ($this->firstParenthesis >= $this->lastParenthesis)) {
            $this->setError(ErrorCode::getError('ATTR1201', $this->funcCall));
            return false;
        }
        
        if ($this->lastSemiColumn > $this->lastParenthesis) {
            $spaceUntil = $this->lastSemiColumn;
        } else {
            $spaceUntil = strlen($this->funcCall);
        }
        
        for ($i = $this->lastParenthesis + 1; $i < $spaceUntil; $i++) {
            $c = $this->funcCall[$i];
            if ($c != ' ') {
                $this->setError(ErrorCode::getError('ATTR1201', $this->funcCall));
                return false;
            }
        }
        
        return true;
    }
    /**
     * @static
     * @param $funcCall
     * @return parseFamilyFunction
     */
    public function parse($funcCall, $noOut = false)
    {
        
        $this->initParse($funcCall);
        $funcName = trim(substr($funcCall, 0, $this->firstParenthesis));
        if (strpos($funcName, ':')) {
            list($appName, $funcName) = explode(':', $funcName, 2);
        } else $appName = '';
        
        if ($this->checkParenthesis()) {
            if ((!$noOut) && ($this->lastSemiColumn < $this->lastParenthesis)) {
                $this->setError(ErrorCode::getError('ATTR1206', $funcCall));
            } else {
                
                if (!$this->isPHPName($funcName)) {
                    $this->setError(ErrorCode::getError('ATTR1202', $funcName));
                } elseif (!preg_match('/^[a-z0-9_]*$/i', $appName)) {
                    $this->setError(ErrorCode::getError('ATTR1202', $funcName));
                } else {
                    $this->functionName = $funcName;
                    $this->appName = $appName;
                    $inputString = substr($funcCall, $this->firstParenthesis + 1, ($this->lastParenthesis - $this->firstParenthesis - 1));
                    $this->inputString = $inputString;
                    
                    $this->parseArguments();
                    $this->parseOutput();
                }
            }
        }
        
        return $this;
    }
    
    protected function parseArguments()
    {
        $args = array();
        $types = array();
        $ak = 0;
        $bq = '';
        for ($i = 0; $i < strlen($this->inputString); $i++) {
            $c = $this->inputString[$i];
            
            if ($c == '"') {
                $this->parseDoubleQuote($i);
            } elseif ($c == "'") {
                $this->parseSimpleQuote($i);
            } elseif ($c == ',') {
            } elseif ($c == ' ') {
                // skip
                
            } else {
                $this->parseArgument($i);
            }
        }
    }
    
    protected function parseOutput()
    {
        if ($this->lastSemiColumn > $this->lastParenthesis) {
            $this->outputString = trim(substr($this->funcCall, $this->lastSemiColumn + 1));
        }
        if ($this->outputString) {
            $this->outputs = explode(',', $this->outputString);
            foreach ($this->outputs as & $output) {
                $output = trim($output);
                if (!$this->isAlphaNumOutAttribute($output)) {
                    $this->setError(ErrorCode::getError('ATTR1207', $this->funcCall));
                }
            }
        }
    }
    
    protected function isAlphaNum($s)
    {
        return preg_match('/^[a-z_][a-z0-9_]*$/i', $s);
    }
    
    protected function isAlphaNumOutAttribute($s)
    {
        return preg_match('/^[a-z_\?][a-z0-9_\[\]]*$/i', $s);
    }
    protected function isPHPName($s)
    {
        return preg_match('/^[a-z_][a-z0-9_]*$/i', $s);
    }
    
    private function gotoNextArgument(&$index)
    {
        for ($i = $index; $i < strlen($this->inputString); $i++) {
            
            $c = $this->inputString[$i];
            
            if ($c == ',') {
                break;
            } elseif ($c == " ") {
                //skip
                
            } else {
                $this->setError($this->setError(ErrorCode::getError('ATTR1204', strlen($this->functionName) + 1 + $i, $this->funcCall)));
            }
        }
        $index = $i;
    }
    /**
     * analyze single misc argument
     * @param int $index index to start analysis string
     * @return void
     */
    protected function parseArgument(&$index)
    {
        $arg = '';
        for ($i = $index; $i < strlen($this->inputString); $i++) {
            
            $c = $this->inputString[$i];
            
            if ($c == ',') {
                break;
            } else {
                $arg.= $c;
            }
        }
        $index = $i;
        $arg = trim($arg);
        
        if (preg_match('/^[a-z_][a-z0-9_]*$/i', $arg)) {
            $type = "any";
        } else {
            $type = "string";
        }
        
        $this->inputs[] = new inputArgument($arg, $type);
    }
    /**
     * analyze single double quoted text argument
     * @param int $index index to start analysis string
     * @return void
     */
    protected function parseDoubleQuote(&$index)
    {
        $arg = '';
        $doubleQuoteDetected = false;
        $c = $this->inputString[$index];
        if ($c != '"') {
            $this->setError($this->setError(ErrorCode::getError('ATTR1204', strlen($this->functionName) + 1 + $index, $this->funcCall)));
        }
        for ($i = $index + 1; $i < strlen($this->inputString); $i++) {
            $cp = $c;
            $c = $this->inputString[$i];
            
            if ($c == '"') {
                if ($cp == '\\') {
                    $arg = substr($arg, 0, -1);
                    $arg.= $c;
                } else {
                    $doubleQuoteDetected = true;
                    break;
                }
            } else {
                $arg.= $c;
            }
        }
        $index = $i;
        
        if (!$doubleQuoteDetected) $this->setError($this->setError(ErrorCode::getError('ATTR1204', strlen($this->functionName) + 1 + $index, $this->funcCall)));
        else {
            $index++;
            $this->gotoNextArgument($index);
        }
        $this->inputs[] = new inputArgument($arg, "string");;
    }
    /**
     * analyze single simple quoted text argument
     * @param int $index index to start analysis string
     * @return void
     */
    protected function parseSimpleQuote(&$index)
    {
        $arg = '';
        
        $c = $this->inputString[$index];
        if ($c != "'") {
            $this->setError($this->setError(ErrorCode::getError('ATTR1205', strlen($this->functionName) + 1 + $index, $this->funcCall)));
        }
        
        for ($i = $index + 1; $i < strlen($this->inputString); $i++) {
            $cp = $c;
            $c = $this->inputString[$i];
            
            if ($c == "'") {
                if ($cp == '\\') {
                    $arg = substr($arg, 0, -1);
                    $arg.= $c;
                } else {
                    $arg.= $c;
                }
            } elseif ($c == ',') {
                break;
            } else {
                $arg.= $c;
            }
        }
        $r = strlen($arg) - 1;
        $c = $arg[strlen($arg) - 1];
        while ($c == ' ') {
            $r--;
            $c = $arg[$r];
        }
        if ($c == "'") {
            $arg = substr($arg, 0, $r);
        }
        $index = $i;
        
        $this->inputs[] = new inputArgument($arg, "string");
    }
}

class inputArgument
{
    public $name = '';
    public $type = 'any';
    
    function __construct($name = '', $type = 'any')
    {
        $this->name = $name;
        $this->type = $type;
    }
}

