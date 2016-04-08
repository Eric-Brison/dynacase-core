<?php
/*
 * Examine vault files
 *
 * @author Anakeen
 * @package FDL
*/

global $action;

$action->log->deprecated(sprintf(_("API %s is deprecated. You should use %s instead.") , "VaultExamine", "checkVault"));

include_once ("API/checkVault.php");
?>
