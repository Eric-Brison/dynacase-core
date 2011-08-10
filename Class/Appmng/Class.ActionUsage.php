<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

/**
 * Verify arguments for action function
 * 
 * @brief Verify arguments for action function
 * @class ActionUsage
 * @code
 $usage = new ActionUsage();
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
class ActionUsage extends ApiUsage
{
    /**
     * init current action
     * 
     * @param Action &$action current action
     */
    public function __construct(Action &$action)
    {
        $this->action = $action;
        $this->setText(_($action->short_name));
        $this->addNeeded('app', "application name");
        $this->addNeeded('action', "action name");
        $this->addHidden('sole', "display mode (deprecated)");
    }
    public function getUsage() {
        $usage=parent::getUsage();
        $usage=str_replace('--app=', '--app='.$this->action->parent->name.' : ' ,$usage);
        $usage=str_replace('--action=', '--action='.$this->action->name.' : ',$usage);
        return $usage;
    }
    
  
}
?>