<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Add, modify or delete WHAT application
 *
 *
 * @param string $appname internal name of the application
 * @param string $method may be "init","reinit","update","delete"
 * @author Anakeen
 * @version $Id: appadmin.php,v 1.8 2008/05/21 07:27:02 marc Exp $
 * @package FDL
 * @subpackage WSH
 */
/**
 */
global $action;

$action->log->deprecated(sprintf(_("API %s is deprecated. You should use %s instead.") , "appadmin", "manageApplications"));

include_once ("API/manageApplications.php");
