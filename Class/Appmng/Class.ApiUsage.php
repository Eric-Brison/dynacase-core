<?php


class apiUsage
{

    private $text='';
    private $optArgs=array();
    private $needArgs=array();
    public function setText($text) {
        $this->text=$text;
    }

    public function addNeeded($argName, $argDefinition, array $restriction=null) {
        $this->needArgs[]=array("name"=>$argName,
                              "def"=>$argDefinition,
                              "restriction"=>$restriction);
    }
    public function addOption($argName, $argDefinition, array $restriction=null) {
        $this->optArgs[]=array("name"=>$argName,
                              "def"=>$argDefinition,
                              "restriction"=>$restriction);
    }

    private function getArgumentText($args)
    {
        $usage='';
        foreach ($args as $arg) {
            $res='';
            if ($arg["restriction"]) {
                $res=' ['.implode('|',$arg["restriction"]).']';
            }
            $usage.=sprintf("\t--%s : %s%s\n",$arg["name"], $arg["def"], $res);
        }
        return $usage;
    }
    public function getUsage() {
        $usage=$this->text;
        $usage.="\nUsage :\n";
        $usage.=$this->getArgumentText($this->needArgs);
        $usage.="   Options:\n";
        $usage.=$this->getArgumentText($this->optArgs);

        return $usage;
    }

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
        }
    }
}
?>