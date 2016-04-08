<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Verify several point for the integrity of a workflow
 * @class CheckWorkflow
 * @brief Check worflow definition when importing definition
 * @see ErrorCodeWFL
 */
class CheckWorkflow
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
    /**
     * @var string
     */
    private $familyName;
    /**
     * max column for a table in postgresql
     */
    const maxSqlColumn = 1600;
    /**
     * number of attributes contructed by transition
     */
    const numberAttributeTransition = 4;
    /**
     * number of attributes contructed by state
     */
    const numberAttributeState = 12;
    /**
     * @var array
     */
    private $transitionModelProperties = array(
        'm0',
        'm1',
        'm2',
        'm3',
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
    /**
     * @param string $className workflow class name
     * @param string $famName workflow family name
     */
    public function __construct($className, $famName)
    {
        $this->className = $className;
        $this->familyName = $famName;
    }
    /**
     * @param $code
     * @param $msg
     * @deprecated use addCoreError instead
     */
    private function addError($code, $msg)
    {
        if ($msg) {
            $msg = sprintf("{%s} %s", $code, $msg);
            if (!in_array($msg, $this->terr)) {
                $this->terr[] = $msg;
            }
        }
    }
    /**
     * short cut to call ErrorCode::getError
     * @param $code
     * @param null $args
     */
    private function addCodeError($code, $args = null)
    {
        if ($code) {
            $tArgs = array(
                $code
            );
            $nargs = func_num_args();
            for ($ip = 1; $ip < $nargs; $ip++) {
                $tArgs[] = func_get_arg($ip);
            }
            
            $msg = call_user_func_array("ErrorCode::getError", $tArgs);
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
    /**
     * verify php workflow class name
     * @return array
     */
    public function verifyWorkflowClass()
    {
        $this->checkClassName();
        if (!$this->getErrorMessage()) {
            $this->checkIsAWorkflow();
            if (!$this->getErrorMessage()) {
                $this->checkPrefix();
                $this->checkTransitionModels();
                $this->checkTransitions();
                $this->checkActivities();
            }
        }
        return $this->getError();
    }
    /**
     * verify validity with attributes
     * @return string
     */
    public function verifyWorkflowComplete()
    {
        $this->verifyWorkflowClass();
        
        if (!$this->getErrorMessage()) {
            $this->checkAskAttributes();
        }
        return $this->getError();
    }
    public function checkActivities()
    {
        $activities = $this->wdoc->stateactivity;
        if (!is_array($activities)) {
            
            $this->addCodeError('WFL0051', $this->className);
        } else {
            $states = $this->wdoc->getStates();
            
            foreach ($activities as $state => $label) {
                if (!in_array($state, $states)) {
                    $this->addCodeError('WFL0052', $state, $label, $this->className);
                }
            }
        }
    }
    
    public function checkTransitions()
    {
        
        $cycle = $this->wdoc->cycle;
        if (!is_array($cycle)) {
            
            $this->addCodeError('WFL0200', $this->className);
        } else {
            foreach ($cycle as $k => $state) {
                if (!is_array($state)) {
                    $this->addCodeError('WFL0203', $k, $this->className, gettype($state));
                    continue;
                }
                $props = array_keys($state);
                $diff = array_diff($props, $this->transitionProperties);
                if (count($diff) > 0) {
                    
                    $this->addCodeError('WFL0201', implode(',', $diff) , $k, $this->className, implode(',', $this->transitionProperties));
                }
                if (!empty($state["e1"])) $this->checkTransitionStateKey($state["e1"]);
                if (!empty($state["e2"])) $this->checkTransitionStateKey($state["e2"]);
                if (!empty($state["t"])) {
                } else {
                    $this->addCodeError('WFL0202', $k, $this->className);
                }
            }
        }
    }
    
    public function checkTransitionModels()
    {
        
        $transitions = $this->wdoc->transitions;
        if (!is_array($transitions)) {
            $this->addCodeError('WFL0100', $this->className);
        } else {
            $columnNumber = count($transitions) * self::numberAttributeTransition + count($this->wdoc->getStates()) * self::numberAttributeState + count($this->wdoc->fields) + count($this->wdoc->sup_fields);
            
            if ($columnNumber > self::maxSqlColumn) {
                $this->addCodeError('WFL0102', $this->className, $columnNumber, self::maxSqlColumn);
            }
            $index = 0;
            foreach ($transitions as $tkey => $transition) {
                if (!is_array($transition)) {
                    $this->addCodeError('WFL0110', $tkey, $index, $this->className, gettype($transition));
                    continue;
                }
                $this->checkTransitionStateKey($tkey);
                
                $props = array_keys($transition);
                $diff = array_diff($props, $this->transitionModelProperties);
                if (count($diff) > 0) {
                    $this->addCodeError('WFL0101', implode(',', $diff) , $tkey, $this->className, implode(',', $this->transitionModelProperties));
                }
                
                if (isset($transition["ask"]) && (!is_array($transition["ask"]))) {
                    $this->addCodeError('WFL0103', $tkey, $this->className);
                }
                
                if (!empty($transition["m0"])) {
                    if (!method_exists($this->wdoc, $transition["m0"])) {
                        
                        $this->addCodeError('WFL0108', $transition["m0"], $tkey, $this->className);
                    }
                }
                if (!empty($transition["m1"])) {
                    if (!method_exists($this->wdoc, $transition["m1"])) {
                        
                        $this->addCodeError('WFL0105', $transition["m1"], $tkey, $this->className);
                    }
                }
                if (!empty($transition["m2"])) {
                    if (!method_exists($this->wdoc, $transition["m2"])) {
                        
                        $this->addCodeError('WFL0106', $transition["m2"], $tkey, $this->className);
                    }
                }
                if (!empty($transition["m3"])) {
                    if (!method_exists($this->wdoc, $transition["m3"])) {
                        
                        $this->addCodeError('WFL0109', $transition["m3"], $tkey, $this->className);
                    }
                }
                if (in_array("nr", $props)) {
                    if (!is_bool($transition["nr"])) {
                        $this->addCodeError('WFL0107', $tkey, $this->className);
                    }
                }
                $index++;
            }
        }
    }
    
    public function checkAskAttributes()
    {
        $transitions = $this->wdoc->transitions;
        if (!is_array($transitions)) {
            $this->addCodeError('WFL0100', $this->className);
        } else {
            
            foreach ($transitions as $tkey => $transition) {
                $this->checkTransitionStateKey($tkey);
                $askes = isset($transition["ask"]) ? $transition["ask"] : null;
                if ($askes) {
                    if (!is_array($askes)) {
                        $this->addCodeError('WFL0103', $tkey, $this->className);
                    } else {
                        
                        $wi = createTmpDoc($this->wdoc->dbaccess, $this->familyName);
                        $aids = array_keys($wi->getAttributes());
                        foreach ($askes as $aid) {
                            if (!in_array(strtolower($aid) , $aids)) {
                                $this->addCodeError('WFL0104', $aid, $this->className);
                            }
                        }
                    }
                }
            }
        }
    }
    
    private function checkTransitionStateKey($key)
    {
        $limit = 49 - strlen($this->wdoc->attrPrefix);
        if (!preg_match("/^[a-zA-Z_][a-zA-Z0-9_:]{0,$limit}$/", $key)) {
            $this->addCodeError('WFL0050', $key, $this->className, $limit + 1);
        }
    }
    
    public function checkIsAWorkflow()
    {
        // Sort out the formatting of the filename
        $fileName = realpath($this->getWorkflowClassFile());
        if (CheckClass::phpLintFile($fileName, $output) === false) {
            $this->addCodeError('WFL0003', implode("\n", $output));
        } else {
            include_once ($this->getWorkflowClassFile());
            if (!class_exists($this->className)) {
                $this->addCodeError('WFL0004', $this->className);
            } else {
                $class = $this->className;
                $this->wdoc = new $class();
                
                if (!is_a($this->wdoc, "WDoc")) {
                    $this->addCodeError('WFL0006', $this->className);
                }
            }
        }
    }
    
    public function checkPrefix()
    {
        if (!$this->wdoc->attrPrefix) {
            $this->addCodeError('WFL0007', $this->className);
        } elseif (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]{0,14}$/', $this->wdoc->attrPrefix)) {
            $this->addCodeError('WFL0008', $this->className);
        }
    }
    
    public function checkClassName()
    {
        if (empty($this->className)) {
            $this->addCodeError('WFL0001');
        } elseif (!self::checkPhpClass($this->className)) {
            $this->addCodeError('WFL0002', $this->className);
        } else {
            $this->checkFileName();
        }
    }
    
    public function checkFileName()
    {
        $fileName = $this->getWorkflowClassFile();
        if (!file_exists($fileName)) {
            $this->addCodeError('WFL0005', $fileName, $this->className);
        }
    }
    
    public static function checkPhpClass($name)
    {
        if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_\\\\]+$/', $name)) {
            return true;
        } else {
            return false;
        }
    }
    
    public function getWorkflowClassFile()
    {
        $classFile = \Dcp\DirectoriesAutoloader::instance(null, null)->getClassFile($this->className);
        
        if ($classFile === null) {
            \Dcp\DirectoriesAutoloader::instance(null, null)->forceRegenerate($this->className);
            $classFile = \Dcp\DirectoriesAutoloader::instance(null, null)->getClassFile($this->className);
        }
        
        if (!$classFile) {
            $classFile = sprintf("FDL/Class.%s.php", $this->className);
        }
        return $classFile;
    }
}
?>
