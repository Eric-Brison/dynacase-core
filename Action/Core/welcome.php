<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

function welcome(Action &$action)
{
    
    $action->parent->AddCssRef("CORE:welcome.css", true);
    $action->lay->set("thisyear", strftime("%Y", time()));
    $action->lay->set("version", $action->GetParam("VERSION"));
    $action->lay->set("userRealName", $action->user->firstname . " " . $action->user->lastname);
    $action->lay->set("userDomain", getParam("CORE_CLIENT"));
    $action->lay->set("isAdmin", (file_exists('admin.php') && $action->canExecute("CORE_ADMIN_ROOT", "CORE_ADMIN") === ''));

}
