<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Refresh groups to recompute members and mail attributes
 *
 * @author Anakeen
 * @version $Id: accountRefreshGroup.php,v 1.1 2006/04/07 08:00:14 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */
global $action;

$action->log->deprecated(sprintf(_("API %s is deprecated. You should use %s instead.") , "accountRefreshGroup", "refreshGroups"));

include_once ("API/refreshGroups.php");
?>
