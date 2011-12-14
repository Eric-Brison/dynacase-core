<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Verify several point for the integrity of a workflow
 *
 * @author Anakeen 2007
 * @version $Id: checklist.php,v 1.8 2008/12/31 14:37:26 jerome Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage CORE
 */
/**
 */

class checkWorkflow
{
    /**
     * @var array
     */
    private $terr = array();
    /**
     * @var Wdoc
     */
    private $wdoc;
    /**
     * @var string
     */
    private $className;
    
    const maxTransitionModel = 20;
    /**
     * @var array
     */
    private $transitionModelProperties = array(
        'm1',
        'm2',
        'ask',
        'nr'
    );
    /**
     * @var array
     */
    private $transitionProperties = array(
        'e1',
        'e2',
        't'
    );
    
    public function __construct($className)
    {
        $this->className = $className;
    }
    
    private function addError($code, $msg)
    {
        if ($msg) {
            $msg = sprintf("{%s} %s", $code, $msg);
            if (!in_array($msg, $this->terr)) {
                $this->terr[] = $msg;
            }
        }
    }
    
    public function getErrorMessage()
    {
        return implode("\n", $this->terr);
    }
    
    public function getError()
    {
        return $this->terr;
    }
    
    public function verifyWorflow()
    {
        $this->checkClassName();
        if (!$this->getErrorMessage()) {
            $this->checkIsAWorkflow();
            if (!$this->getErrorMessage()) {
                $this->checkPrefix();
                $this->checkTransitionModels();
                $this->checkTransitions();
            }
        }
        return $this->getError();
    }
    
    public function checkTransitions()
    {
        
        $cycle = $this->wdoc->cycle;
        if (!is_array($cycle)) {
            $this->addError('W0001', sprintf("workflow transition is not an array for class %s", $this->className));
        } else {
            foreach ($cycle as $k => $state) {
                $props = array_keys($state);
                $diff = array_diff($props, $this->transitionProperties);
                if (count($diff) > 0) {
                    $this->addError('W0002', sprintf("workflow transition unknow property %s for transition #%d in class %s (must be one of %s)", implode(',', $diff) , $k, $this->className, implode(',', $this->transitionProperties)));
                }
                if ($state["e1"]) $this->checkTransitionStateKey($state["e1"]);
                if ($state["e2"]) $this->checkTransitionStateKey($state["e2"]);
                if ($state["t"]) {
                } else {
                    $this->addError('W0003', sprintf("workflow transition #%d property 't' is mandatory in class %s", $k, $this->className));
                }
            }
        }
    }
    
    public function checkTransitionModels()
    {
        
        $transitions = $this->wdoc->transitions;
        if (!is_array($transitions)) {
            $this->addError('W0004', sprintf("workflow transition is not an array for class %s", $this->className));
        } else {
            if (count($transitions) > self::maxTransitionModel) {
                $this->addError('W0005', sprintf("workflow %s number of transition model (found %d) exceed limit (max is %s)", $this->className, count($transitions) , self::maxTransitionModel));
            }
            foreach ($transitions as $tkey => $transition) {
                $this->checkTransitionStateKey($tkey);
                
                $props = array_keys($transition);
                $diff = array_diff($props, $this->transitionModelProperties);
                if (count($diff) > 0) {
                    $this->addError('W0006', sprintf("workflow transition unknow property %s for transition model %s in class %s (must be one of %s)", implode(',', $diff) , $tkey, $this->className, implode(',', $this->transitionModelProperties)));
                }
                
                if ($transition["ask"] && (!is_array($transition["ask"]))) {
                    $this->addError('W0007', sprintf("workflow transition ask is not an array for transition model %s in class %s", $tkey, $this->className));
                }
                if ($transition["m1"]) {
                    if (!method_exists($this->wdoc, $transition["m1"])) {
                        
                        $this->addError('W0008', sprintf("workflow unknow m1 method %s for transition model %s in class %s", $transition["m1"], $tkey, $this->className));
                    }
                }
                if ($transition["m2"]) {
                    if (!method_exists($this->wdoc, $transition["m2"])) {
                        
                        $this->addError('W0009', sprintf("workflow unknow m2 method %s for transition model %s in class %s", $transition["m2"], $tkey, $this->className));
                    }
                }
                if (in_array("nr", $props)) {
                    if (!is_bool($transition["nr"])) {
                        $this->addError('W0010', sprintf("workflow transition nr property is not a boolean for transition model %s in class %s", implode(',', $diff) , $tkey, $this->className));
                    }
                }
            }
        }
    }
    
    private function checkTransitionStateKey($key)
    {
        $limit = 45 - strlen($this->wdoc->attrPrefix);
        if (!preg_match("/^[a-zA-Z_][a-zA-Z0-9_:]{0,$limit}$/", $key)) {
            $this->addError('W0011', sprintf("workflow transition or state key %s syntax error for %s (limit to %d alpha characters)", $key, $this->className, $limit + 1));
        }
    }
    
    public function checkIsAWorkflow()
    {
        /* set_error_handler(array(
            $this,
            "errorHandler"
        ));
        */
        // Sort out the formatting of the filename
        $fileName = realpath($this->getWorkflowClassFile());
        // Get the shell output from the syntax check command
        exec(sprintf('php -n -l %s 2>&1', escapeshellarg($fileName)) , $output, $status);
        if ($status != 0) {
            $this->addError('W0012', implode("\n", $output));
        } else {
            include_once ($this->getWorkflowClassFile());
            if (!class_exists($this->className)) {
                $this->addError('W0013', sprintf("workflow class %s not found", $this->className));
            } else {
                $class = $this->className;
                $this->wdoc = new $class();
            }
        }
        //restore_error_handler();
    }
    
    public function errorHandler($errno, $errstr, $errfile, $errline)
    {
        
        $this->addError('W0000', "handler:" . $errstr);
    }
    
    public function checkPrefix()
    {
        if (!$this->wdoc->attrPrefix) {
            $this->addError('W0014', sprintf("workflow : missing attrPrefix definition for %s class", $this->className));
        } elseif (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]{0,9}$/', $this->wdoc->attrPrefix)) {
            $this->addError('W0015', sprintf("workflow : syntax error attrPrefix for %s class (limit to 10 alpha characters)", $this->className));
        }
    }
    
    public function checkClassName()
    {
        if (empty($this->className)) {
            $this->addError('W0016', sprintf("workflow class name is empty"));
        } elseif (!self::checkPhpClass($this->className)) {
            $this->addError('W0017', sprintf("class name %s not valid", $this->className));
        } else {
            $this->checkFileName();
        }
    }
    
    public function checkFileName()
    {
        if (!file_exists($this->getWorkflowClassFile())) {
            $this->addError('W0018', sprintf("file name for %s not found", $this->className));
        }
    }
    
    public static function checkPhpClass($name)
    {
        if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]+$/', $name)) {
            return true;
        } else {
            return false;
        }
    }
    
    public function getWorkflowClassFile()
    {
        return sprintf('FDL/Class.%s.php', $this->className);
    }
}
?>
