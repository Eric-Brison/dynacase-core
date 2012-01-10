<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
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
     * @deprecated
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
     */
    public function verifyWorkflowComplete()
    {
        $this->checkIsAWorkflow();
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
                $props = array_keys($state);
                $diff = array_diff($props, $this->transitionProperties);
                if (count($diff) > 0) {
                    
                    $this->addCodeError('WFL0201', implode(',', $diff) , $k, $this->className, implode(',', $this->transitionProperties));
                }
                if ($state["e1"]) $this->checkTransitionStateKey($state["e1"]);
                if ($state["e2"]) $this->checkTransitionStateKey($state["e2"]);
                if ($state["t"]) {
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
            if (count($transitions) > self::maxTransitionModel) {
                $this->addCodeError('WFL0102', $this->className, count($transitions) , self::maxTransitionModel);
            }
            foreach ($transitions as $tkey => $transition) {
                $this->checkTransitionStateKey($tkey);
                
                $props = array_keys($transition);
                $diff = array_diff($props, $this->transitionModelProperties);
                if (count($diff) > 0) {
                    $this->addCodeError('WFL0101', implode(',', $diff) , $tkey, $this->className, implode(',', $this->transitionModelProperties));
                }
                
                if ($transition["ask"] && (!is_array($transition["ask"]))) {
                    $this->addCodeError('WFL0103', $tkey, $this->className);
                }
                if ($transition["m1"]) {
                    if (!method_exists($this->wdoc, $transition["m1"])) {
                        
                        $this->addCodeError('WFL0105', $transition["m1"], $tkey, $this->className);
                    }
                }
                if ($transition["m2"]) {
                    if (!method_exists($this->wdoc, $transition["m2"])) {
                        
                        $this->addCodeError('WFL0106', $transition["m2"], $tkey, $this->className);
                    }
                }
                if (in_array("nr", $props)) {
                    if (!is_bool($transition["nr"])) {
                        $this->addCodeError('WFL0107', $tkey, $this->className);
                    }
                }
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
                $askes = $transition["ask"];
                if ($askes) {
                    if (!is_array($askes)) {
                        $this->addCodeError('WFL0103', $tkey, $this->className);
                    } else {
                        
                        $wi = createTmpDoc($this->wdoc->dbaccess, $this->familyName);
                        $aids = array_keys($wi->getAttributes());
                        foreach ($askes as $aid) {
                            if (!in_array($aid, $aids)) {
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
        // Get the shell output from the syntax check command
        exec(sprintf('php -n -l %s 2>&1', escapeshellarg($fileName)) , $output, $status);
        if ($status != 0) {
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
        } elseif (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]{0,9}$/', $this->wdoc->attrPrefix)) {
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
        if (!file_exists($this->getWorkflowClassFile())) {
            $this->addCodeError('WFL0005', $this->className);
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
