<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Delete family document and its documents
 *
 * @author Anakeen
 * @version $Id: fdl_adoc.php,v 1.20 2008/10/30 17:34:31 eric Exp $
 * @package FDL
 * @subpackage
 */
global $action;

$action->log->deprecated(sprintf(_("API %s is deprecated. You should use %s instead.") , "fdl_deletefamily", "destroyFamily"));

include_once ("API/destroyFamily.php");
?>