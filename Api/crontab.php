<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * API script to manipulate user crontab
 *
 * @author Anakeen
 * @version $Id: crontab.php,v 1.2 2009/01/16 15:51:35 jerome Exp $
 * @package FDL
 * @subpackage
 */
/**
 */
global $action;

$action->log->deprecated(sprintf(_("API %s is deprecated. You should use %s instead.") , "crontab", "manageContextCrontab"));

include_once ("API/manageContextCrontab.php");
?>