<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 *  Reinit doc relations
 */

global $action;

$action->log->deprecated(sprintf(_("API %s is deprecated. You should use %s instead.") , "DocRelInit", "initializeDocrelTable"));

include_once ("API/initializeDocrelTable.php");
?>