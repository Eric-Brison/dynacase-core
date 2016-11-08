<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Verify arguments for action function
 *
 * @brief Verify arguments for action function
 * @class ActionUsage
 * @code
 $usage = new ActionUsage();
 $usage->setDescriptionText("Refresh documents ");
 $usage->addRequiredParameter("famid", "the family filter");
 $usage->addOptionalParameter("revision", "use all revision - default is no", array(
 "yes",
 "no"
 ));
 $usage->addOptionalParameter("save", "use modify default is light", array(
 "complete",
 "light",
 "none"
 ));
 $usage->verify();
 * @endcode
 */
class ActionUsage extends ApiUsage
{
    /**
     * init current action
     *
     * @param Action &$action current action
     */
    public function __construct(Action & $action)
    {
        $this->action = $action;
        $this->setDefinitionText(_($action->short_name));
        $this->addRequiredParameter('app', "application name");
        $this->addOptionalParameter('action', "action name");
        $this->addHiddenParameter('sole', "display mode (deprecated)");
        $authType = $this->addHiddenParameter('authtype', "authentication type");
        $this->addHiddenParameter(\openAuthenticator::openGetId, "authentication token");
        if("open" === $authType) {
            $this->addHiddenParameter("privateid", "token");
        }
    }
    /**
     * @api Get usage for action funtion
     * @return mixed|string
     */
    public function getUsage()
    {
        $usage = parent::getUsage();
        $usage = str_replace('--app=', '--app=' . $this->action->parent->name . ' : ', $usage);
        $usage = str_replace('--action=', '--action=' . $this->action->name . ' : ', $usage);
        return $usage;
    }
    /**
     * Return value of argument key
     * @param string $key the identifier
     * @param string $defaultValue value to return if value is empty
     * @return mixed|string
     */
    protected function getArgumentValue($key, $defaultValue = '')
    {
        $value = parent::getArgumentValue($key, null);
        if ($value === null) {
            if (isset($_FILES[$key])) {
                return $_FILES[$key];
            }
        }
        return ($value === null) ? $defaultValue : $value;
    }

    /**
     * Restriction callback to verify a file array value
     * @param string $argVal argument value
     * @param string $argName argument name
     * @param ApiUsage $apiUsage current apiUsage object
     * @return string
     */
    public static function isFile($argVal, $argName, $apiUsage)
    {
        $err = "";
        if (!is_array($argVal) || !isset($argVal["name"]) || !isset($argVal["tmp_name"])) {
            $err = sprintf("Value type isn't authorized for argument, must be a file description array");
        }
        return $err;
    }
}
?>