<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: dynacaseDbCleaner.php,v 1.8 2008/04/25 09:18:15 jerome Exp $
 * @package FDL
 * @subpackage
 */
/**
 */
global $action;

$action->log->deprecated(sprintf(_("API %s is deprecated. You should use %s instead.") , "dynacaseDbCleaner", "cleanContext"));

include_once ("API/cleanContext.php");
?>
