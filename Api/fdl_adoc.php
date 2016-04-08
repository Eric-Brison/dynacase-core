<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generate Php Document Classes
 *
 * @author Anakeen
 * @version $Id: fdl_adoc.php,v 1.20 2008/10/30 17:34:31 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */
global $action;

$action->log->deprecated(sprintf(_("API %s is deprecated. You should use %s instead.") , "fdl_adoc", "generateDocumentClass"));

include_once ("API/generateDocumentClass.php");
?>