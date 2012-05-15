<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Submit all edit parameters zone
 * @param Action $action
 */
function editsubmit(Action & $action)
{
    $usage = new ActionUsage($action);
    $label = $usage->addOption("label", "Label of submit button", array() , _("Submit"));
    $usage->strict();
    $usage->verify();
    
    $action->lay->set("submit_label", $label);
}
