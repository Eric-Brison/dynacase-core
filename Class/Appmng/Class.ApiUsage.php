<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Verify arguments for wsh programs
 *
 * @class apiUsage
 * @brief Verify arguments for wsh programs
 * @code
 $usage = new ApiUsage();
 $usage->setText("Refresh documents ");
 $usage->addNeeded("famid", "the family filter");
 $usage->addOption("revision", "use all revision - default is no", array(
 "yes",
 "no"
 ));
 $usage->addOption("save", "use modify default is light", array(
 "complete",
 "light",
 "none"
 ));
 $usage->verify();
 * @endcode
 */
class ApiUsage
{
    /**
     * usage text
     *
     * @var string
     */
    private $text = '';
    /**
     * optionnals arguments
     *
     * @var array
     */
    private $optArgs = array();
    /**
     * needed arguments
     *
     * @var array
     */
    private $needArgs = array();
    /**
     * hidden arguments
     *
     * @var array
     */
    private $hiddenArgs = array();
    /**
     * current action
     *
     * @var Action
     */
    protected $action;
    /**
     * strict mode
     *
     * @var boolean
     */
    protected $strict = true;
    /**
     * force throw exception when verify fail instead of exitError
     *
     * @var boolean
     */
    protected $useException = false;
    /**
     * init action
     */
    public function __construct()
    {
        global $action;
        $this->action = & $action;
        $this->addHidden("api", "api file to use");
        $this->addOption('userid', "user system id to execute function - default is (admin)", array() , 1);
    }
    /**
     * add textual definition of program
     *
     * @param string $text usage text
     *
     * @return void
     */
    public function setText($text)
    {
        $this->text = $text;
    }
    /**
     * add hidden argument (private arg not see them in usage)
     *
     * @param string $argName argument name
     * @param string $argDefinition argument définition
     *
     * @return argument value
     */
    public function addHidden($argName, $argDefinition)
    {
        $this->hiddenArgs[] = array(
            "name" => $argName,
            "def" => $argDefinition
        );
        return $this->action->getArgument($argName);
    }
    /**
     * add needed argument
     *
     * @param string $argName argument name
     * @param string $argDefinition argument définition
     * @param array $restriction optionnal enumeration for argument
     *
     * @return argument value
     */
    public function addNeeded($argName, $argDefinition, array $restriction = null)
    {
        $this->needArgs[] = array(
            "name" => $argName,
            "def" => $argDefinition,
            "restriction" => $restriction
        );
        return $this->action->getArgument($argName);
    }
    /**
     * add optionnal argument
     *
     * @param string $argName argument name
     * @param string $argDefinition argument définition
     * @param array $restriction optionnal enumeration for argument
     * @param string $default default value if no value set
     *
     * @return argument value
     */
    public function addOption($argName, $argDefinition, array $restriction = null, $default = null)
    {
        $this->optArgs[] = array(
            "name" => $argName,
            "def" => $argDefinition,
            "default" => $default,
            "restriction" => $restriction
        );
        return $this->action->getArgument($argName, $default);
    }
    /**
     * get usage for a specific argument
     *
     * @param array $args argument
     *
     * @return string
     */
    private function getArgumentText(array $args)
    {
        $usage = '';
        foreach ($args as $arg) {
            $res = '';
            if ($arg["restriction"]) {
                $res = ' [' . implode('|', $arg["restriction"]) . ']';
            }
            $default = "";
            if ($arg["default"] !== null) {
                $default = sprintf(", default is '%s'", $arg["default"]);
            }
            $usage.= sprintf("\t--%s=<%s>%s%s\n", $arg["name"], $arg["def"], $res, $default);
        }
        return $usage;
    }
    /**
     * return usage text for the action
     *
     * @return string
     */
    public function getUsage()
    {
        $usage = $this->text;
        $usage.= "\nUsage :\n";
        $usage.= $this->getArgumentText($this->needArgs);
        $usage.= "   Options:\n";
        $usage.= $this->getArgumentText($this->optArgs);
        
        return $usage;
    }
    /**
     * exit when error
     *
     * @param string $error message error
     * @throw ApiUsageException
     * @return void
     */
    public function exitError($error = '')
    {
        if ($error != '') $error.= "\n";
        $usage = $this->getUsage();
        
        if ((!$this->useException)) {
            if ($_SERVER['HTTP_HOST'] != "") {
                $usage = str_replace('--', '&', $usage);
                $error.= '<pre>' . htmlspecialchars($usage) . '</pre>';
            } else {
                $error.= $usage;
            }
            $this->action->exitError($error);
        } else {
            // no usage when use exception mode
            throw new ApiUsageException($error, 0, null, $usage);
        }
    }
    /**
     * list hidden keys
     *
     * @return array
     */
    protected function getHiddenKeys()
    {
        $keys = array();
        foreach ($this->hiddenArgs as $v) {
            $keys[] = $v["name"];
        }
        return $keys;
    }
    /**
     * set strict mode
     *
     * @param boolean $strict strict mode
     * @brief if false additionnal arguments are ignored, default is true
     *
     * @return void
     */
    public function strict($strict = true)
    {
        $this->strict = $strict;
    }
    /**
     * verify if wsh program argument are valids. If not wsh exit
     *
     * @param boolean $useExcepion if true throw ApiUsageException when verify is not successful
     *
     * @return void
     */
    public function verify($useException = false)
    {
        $this->useException = $useException;
        foreach ($this->needArgs as $arg) {
            $value = $this->action->getArgument($arg["name"]);
            if ($value == '') {
                $error = sprintf("argument '%s' expected\n", $arg["name"]);
                
                $this->exitError($error);
            }
        }
        $allArgs = array_merge($this->needArgs, $this->optArgs);
        $argsKey = $this->getHiddenKeys();
        
        foreach ($allArgs as $arg) {
            $value = $this->action->getArgument($arg["name"], null);
            if ($value !== null && $arg["restriction"]) {
                if (!in_array($value, $arg["restriction"])) {
                    $error = sprintf("argument '%s' must be one of these values : %s\n", $arg["name"], implode(", ", $arg["restriction"]));
                    
                    $this->exitError($error);
                }
            }
            $argsKey[] = $arg["name"];
        }
        if ($this->strict) {
            foreach ($_GET as $k => $v) {
                if (!in_array($k, $argsKey)) {
                    $error = sprintf("argument '%s' is not defined\n", $k);
                    
                    $this->exitError($error);
                }
            }
        }
    }
}

class ApiUsageException extends Exception
{
    private $usage = '';
    public function __construct($message, $code, $previous = null, $usage = '')
    {
        parent::__construct($message, (int) $code);
        $this->usage = $usage;
    }
    
    public function getUsage()
    {
        return $this->usage;
    }
}
?>