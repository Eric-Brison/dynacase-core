<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

include_once ("ACCESS/user_access.php");
function role_access(Action & $action)
{
    user_access($action, "R");
}
?>
