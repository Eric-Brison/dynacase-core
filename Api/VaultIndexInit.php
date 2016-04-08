<?php
/*
 * Reinit vault files
 *
 * @author Anakeen
 * @package FDL
*/
global $action;

$action->log->deprecated(sprintf(_("API %s is deprecated. You should use %s instead.") , "VaultIndexInit", "refreshVaultIndex"));

include_once ("API/refreshVaultIndex.php");
?>
