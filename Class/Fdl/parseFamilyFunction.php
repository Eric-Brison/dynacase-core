<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

class parseFamilyFunction
{
    
    public $name = '';
    public $appName = '';
    public $funcCall = '';
    public $inputString = '';
    public $outputString = '';
    public $inputs = array();
    public $outputs = array();
    protected $error = '';
    
    public function getError()
    {
        return $this->error;
    }
    
    protected function setError($error)
    {
        return $this->error = $error;
    }
    /**
     * @static
     * @param $funcCall
     * @return parseFamilyFunction
     */
    public function parse($funcCall, $noOut = false)
    {
        
        $this->funcCall = $funcCall;
        $firstParenthesis = strpos($funcCall, '(');
        $lastParenthesis = strrpos($funcCall, ')');
        $lastSemiColumn = strrpos($funcCall, ':');
        
        $funcName = trim(substr($funcCall, 0, $firstParenthesis));
        if (strpos($funcName, ':')) {
            list($appName, $funcName) = explode(':', $funcName, 2);
        } else $appName = '';
        
        if (($firstParenthesis === false) || ($lastParenthesis === false) || ($firstParenthesis >= $lastParenthesis)) {
            $this->setError(ErrorCode::getError('ATTR1201', $funcCall));
        } elseif ((!$noOut) && ($lastSemiColumn < $lastParenthesis)) {
            $this->setError(ErrorCode::getError('ATTR1206', $funcCall));
        } else {
            
            if (!preg_match('/^[a-z_][a-z0-9_]*$/i', $funcName)) {
                $this->setError(ErrorCode::getError('ATTR1202', $funcName));
            } elseif (!preg_match('/^[a-z0-9_]*$/i', $appName)) {
                $this->setError(ErrorCode::getError('ATTR1202', $funcName));
            } else {
                $this->name = $funcName;
                $this->appName = $appName;
                $inputString = substr($funcCall, $firstParenthesis + 1, ($lastParenthesis - $firstParenthesis - 1));
                $this->inputString = $inputString;
                if ($lastSemiColumn > $lastParenthesis) {
                    $this->outputString = trim(substr($funcCall, $lastSemiColumn + 1));
                }
                $this->parseArguments();
                $this->parseOutput();
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
        if ($this->outputString) {
            $this->outputs = explode(',', $this->outputString);
            foreach ($this->outputs as & $output) {
                $output = trim($output);
                if (!$this->isAlphaNum($output)) {
                    $this->setError(ErrorCode::getError('ATTR1207', $this->funcCall));
                }
            }
        }
    }
    
    protected function isAlphaNum($s)
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
                $this->setError($this->setError(ErrorCode::getError('ATTR1204', strlen($this->name) + 1 + $i, $this->funcCall)));
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
            $this->setError($this->setError(ErrorCode::getError('ATTR1204', strlen($this->name) + 1 + $index, $this->funcCall)));
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
        
        if (!$doubleQuoteDetected) $this->setError($this->setError(ErrorCode::getError('ATTR1204', strlen($this->name) + 1 + $index, $this->funcCall)));
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
            $this->setError($this->setError(ErrorCode::getError('ATTR1205', strlen($this->name) + 1 + $index, $this->funcCall)));
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

