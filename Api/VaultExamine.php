<?php
/*
 * Examine vault files
 *
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

global $action;

$action->log->deprecated(sprintf(_("API %s is deprecated. You should use %s instead.") , "VaultExamine", "checkVault"));

include_once ("API/checkVault.php");
?>
