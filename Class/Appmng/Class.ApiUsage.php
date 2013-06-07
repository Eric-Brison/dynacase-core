<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
namespace {
    /**
     * Verify arguments for wsh programs
     *
     * @class ApiUsage
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
        const THROW_EXITHELP = 1988;
        /**
         * usage text
         *
         * @var string
         */
        private $text = '';
        /**
         * optionals arguments
         *
         * @var array
         */
        private $optArgs = array();
        /**
         * empty arguments
         *
         * @var array
         */
        private $emptyArgs = array();
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
            $this->addHiddenParameter("api", "api file to use");
            $this->addOptionalParameter('userid', "user system id or login name to execute function - default is (admin)", array() , 1);
            $this->addEmptyParameter('help', "Show usage");
        }
        /**
         * add textual definition of program
         *
         * @param string $text usage text
         *
         * @return void setText
         */
        public function setText($text)
        {
            deprecatedFunction();
            $this->setDefinitionText($text);
        }
        /**
         * add textual definition of program
         *
         * @api add textual definition of program
         * @param string $text usage text
         *
         * @return void
         */
        public function setDefinitionText($text)
        {
            $this->text = $text;
        }
        /**
         * add hidden argument (private arg not see them in usage)
         *
         * @see ApiUsage::addHiddenParameter
         *
         * @deprecated use { @link Application::addHiddenParameter } instead
         *
         * @param string $argName argument name
         * @param string $argDefinition argument définition
         *
         * @return string argument value
         */
        public function addHidden($argName, $argDefinition)
        {
            deprecatedFunction();
            return $this->addHiddenParameter($argName, $argDefinition);
        }
        /**
         * add hidden argument (private arg not see them in usage)
         *
         * @api add an empty parameter
         *
         * @param string $argName argument name
         * @param string $argDefinition argument définition
         *
         * @return string argument value
         */
        public function addHiddenParameter($argName, $argDefinition)
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
         * @see ApiUsage::addRequiredParameter
         *
         * @deprecated use { @link Application::addRequiredParameter } instead
         *
         * @param string $argName argument name
         * @param string $argDefinition argument définition
         * @param array $restriction optional enumeration for argument
         *
         * @return string argument value
         */
        public function addNeeded($argName, $argDefinition, array $restriction = null)
        {
            deprecatedFunction();
            return $this->addRequiredParameter($argName, $argDefinition, $restriction);
        }
        /**
         * add needed argument
         *
         * @api add needed argument
         *
         * @param string $argName argument name
         * @param string $argDefinition argument définition
         * @param array $restriction optional enumeration for argument
         *
         * @return string argument value
         */
        public function addRequiredParameter($argName, $argDefinition, array $restriction = null)
        {
            $this->needArgs[] = array(
                "name" => $argName,
                "def" => $argDefinition,
                "default" => null,
                "restriction" => $restriction
            );
            return $this->action->getArgument($argName);
        }
        /**
         * add optional argument
         *
         * @see addOptionalParameter::addOptionParameter
         *
         * @deprecated use { @link Application::addOptionParameter } instead
         * @param string $argName argument name
         * @param string $argDefinition argument définition
         * @param array $restriction optional enumeration for argument
         * @param string $default default value if no value set
         *
         * @return string argument value
         */
        public function addOption($argName, $argDefinition, array $restriction = null, $default = null)
        {
            deprecatedFunction();
            return $this->addOptionalParameter($argName, $argDefinition, $restriction, $default);
        }
        /**
         * add optional argument
         *
         * @api add optional argument
         *
         * @param string $argName argument name
         * @param string $argDefinition argument definition
         * @param array $restriction optional enumeration for argument
         * @param string $default default value if no value set
         *
         * @return string argument value
         */
        public function addOptionalParameter($argName, $argDefinition, array $restriction = null, $default = null)
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
         * add empty argument (argument with boolean value)
         *
         * @see ApiUsage::addEmptyParameter
         *
         * @deprecated use { @link Application::addEmptyParameter } instead
         *
         * @param string $argName argument name
         * @param string $argDefinition argument definition
         *
         * @return string argument value
         */
        public function addEmpty($argName, $argDefinition = "")
        {
            deprecatedFunction();
            return $this->addEmptyParameter($argName, $argDefinition);
        }
        /**
         * add empty argument (argument with boolean value)
         *
         * @api add empty argument (argument with boolean value)
         *
         * @param string $argName argument name
         * @param string $argDefinition argument definition
         *
         * @return string argument value
         */
        public function addEmptyParameter($argName, $argDefinition = "")
        {
            $this->emptyArgs[] = array(
                "name" => $argName,
                "def" => $argDefinition,
                "default" => null,
                "restriction" => null
            );
            return $this->action->getArgument($argName, false);
        }
        /**
         * get usage for a specific argument
         *
         * @param array $args argument
         * @param bool $empty flag to see if argument array as values or not
         *
         * @return string
         */
        private function getArgumentText(array $args, $empty = false)
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
                $string = "\t--" . $arg["name"] . ($empty ? " (%s) " : "=<%s>");
                
                $usage.= sprintf("$string%s%s\n", $arg["def"], $res, $default);
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
            $usage.= $this->getArgumentText($this->emptyArgs, true);
            return $usage;
        }
        /**
         * exit when error
         *
         * @param string $error message error
         * @throws Dcp\ApiUsage\Exception
         * @return void
         */
        public function exitError($error = '')
        {
            if ($error != '') $error.= "\n";
            $usage = $this->getUsage();
            
            if ((!$this->useException)) {
                if (!empty($_SERVER['HTTP_HOST'])) {
                    $usage = str_replace('--', '&', $usage);
                    $error.= '<pre>' . htmlspecialchars($usage) . '</pre>';
                } else {
                    $error.= $usage;
                }
                if ($this->action->getArgument("help") == true) {
                    throw new \Dcp\ApiUsage\Exception("CORE0003", $error, $usage);
                }
                $this->action->exitError($error);
            } else {
                // no usage when use exception mode
                throw new \Dcp\ApiUsage\Exception("CORE0002", $error, $usage);
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
         * @see ApiUsage::setStrictMode
         *
         * @deprecated use { @link Application::setStrictMode } instead
         *
         * @param boolean $strict strict mode
         * @brief if false additionnal arguments are ignored, default is true
         *
         * @return void
         */
        public function strict($strict = true)
        {
            deprecatedFunction();
            $this->setStrictMode($strict);
        }
        /**
         * set strict mode
         *
         * @api set strict mode
         *
         * @param boolean $strict strict mode
         * @brief if false additionnal arguments are ignored, default is true
         *
         * @return void
         */
        public function setStrictMode($strict = true)
        {
            $this->strict = $strict;
        }
        /**
         * verify if wsh program argument are valids. If not wsh exit
         *
         * @api Verify if wsh's program arguments are valid
         * @param bool $useException if true throw ApiUsageException when verify is not successful
         *
         * @return void
         */
        public function verify($useException = false)
        {
            $this->useException = $useException;
            if ($this->action->getArgument("help") == true) {
                $this->exitError();
            }
            foreach ($this->needArgs as $arg) {
                $value = $this->action->getArgument($arg["name"]);
                if ($value == '') {
                    $error = sprintf("argument '%s' expected\n", $arg["name"]);
                    
                    $this->exitError($error);
                }
            }
            $allArgs = array_merge($this->needArgs, $this->optArgs, $this->emptyArgs);
            $argsKey = $this->getHiddenKeys();
            
            foreach ($allArgs as $arg) {
                $value = $this->action->getArgument($arg["name"], null);
                if ($value !== null && $arg["restriction"]) {
                    $values=(!is_array($value))?array($value):$value;
                        foreach ($values as $aValue) {
                    if (!in_array($aValue, $arg["restriction"])) {
                        $error = sprintf("argument '%s' must be one of these values : %s\n", $arg["name"], implode(", ", $arg["restriction"]));
                        
                        $this->exitError($error);
                    }
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
}

namespace Dcp\ApiUsage {
    class Exception extends \Dcp\Exception
    {
        private $usage = '';
        
        public function __construct($code, $text, $usage = '')
        {
            parent::__construct($code, $text);
            $this->usage = $usage;
        }
        
        public function getUsage()
        {
            if ($this->usage) return $this->usage;
            return null;
        }
    }
}
