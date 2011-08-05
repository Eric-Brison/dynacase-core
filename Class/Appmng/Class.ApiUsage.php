<?php
/**
 * Vérify arguments for wsh programs
 * @author anakeen 
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @class apiUsage
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
class apiUsage
{

    private $text='';
    private $optArgs=array();
    private $needArgs=array();
    public function __construct() {
        global $action;
        $this->action=&$action;
        $u=1;
        $this->addOption('userid', "user system id to execute function - default is (admin)",$u,array(),1);
    }
    /**
     * add textual definition of program
     * @param string $text
     */
    public function setText($text) {
        $this->text=$text;
    }

     /**
     * add needed argument
     * @param string $argName argument name 
     * @param string $argDefinition argument définition 
     * @param string $argument variable will be set 
     * @param array $restriction optionnal enumeration for argument  
     * @return void
     */
    public function addNeeded($argName, $argDefinition,&$argument=null, array $restriction=null) {
        $this->needArgs[]=array("name"=>$argName,
                              "def"=>$argDefinition,
                              "default"=>$default,
                              "restriction"=>$restriction);
        $argument=$this->action->getArgument($argName);
    }
    /**
     * add optionnal argument
     * @param string $argName argument name 
     * @param string $argDefinition argument définition 
     * @param string $argument variable will be set 
     * @param array $restriction optionnal enumeration for argument 
     * @param string $default default value if no value set 
     * @return void
     */
    public function addOption($argName, $argDefinition,&$argument=null, array $restriction=null,$default=null) {
        $this->optArgs[]=array("name"=>$argName,
                              "def"=>$argDefinition,
                              "default"=>$default,
                              "restriction"=>$restriction);
        $argument=$this->action->getArgument($argName,$default);
    }

    private function getArgumentText($args)
    {
        $usage='';
        foreach ($args as $arg) {
            $res='';
            if ($arg["restriction"]) {
                $res=' ['.implode('|',$arg["restriction"]).']';
            }
            $default="";
            if ($arg["default"]!==null) {
                $default=sprintf(", default is '%s'", $arg["default"]);
            }
            $usage.=sprintf("\t--%s=<%s>%s%s\n",$arg["name"], $arg["def"], $res,$default);
        }
        return $usage;
    }
    /**
     * return usage text for the action
     * @return string
     */
    public function getUsage() {
        $usage=$this->text;
        $usage.="\nUsage :\n";
        $usage.=$this->getArgumentText($this->needArgs);
        $usage.="   Options:\n";
        $usage.=$this->getArgumentText($this->optArgs);

        return $usage;
    }

    /**
     * verify if wsh program argument are valids
     * if not wsh exit 
     * @return void
     */
    public function verify() {
        global $action;
        foreach ($this->needArgs as $arg) {
            $value=$action->getArgument($arg["name"]);
            if ($value=='') {
                $error=sprintf("argument '%s' expected\n", $arg["name"]);
                $error.=$this->getUsage();
                $action->exitError($error);
            }
        }
        $allArgs=array_merge($this->needArgs, $this->optArgs);
        $argsKey=array("api");
        foreach ($allArgs as $arg) {
            $value=$action->getArgument($arg["name"]);
            if ($value && $arg["restriction"]) {
                if (! in_array($value, $arg["restriction"])) {
    
                $error=sprintf("argument '%s' must be one of these values : %s\n", $arg["name"],
                                  implode(", ",$arg["restriction"]));
                $error.=$this->getUsage();
                $action->exitError($error);
                }
            }
            $argsKey[]=$arg["name"];
        }
        foreach ($_GET as $k=>$v) {
            if (! in_array($k, $argsKey)) {
                $error=sprintf("argument '%s' is not defined\n", $k);
                $error.=$this->getUsage();
                $action->exitError($error);
            }
        }
    }
}
?>