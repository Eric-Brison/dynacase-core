<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 *  Reinit doc relations
 */

global $action;

$action->log->deprecated(sprintf(_("API %s is deprecated. You should use %s instead.") , "DocRelInit", "initializeDocrelTable"));

include_once ("API/initializeDocrelTable.php");
?>