<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: usercard_iuser.php,v 1.18 2007/03/21 15:32:57 eric Exp $
 * @package FDL
 * @subpackage
 */
global $action;

$action->log->deprecated(sprintf(_("API %s is deprecated. You should use %s instead.") , "usercard_iuser", "refreshUserAccount"));

include_once ("API/refreshUserAccount.php");
