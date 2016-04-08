<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * get parameter value
 *
 * analyze sub-directories presents in STYLE directory
 * @author Anakeen
 * @version $Id: get_param.php,v 1.1 2004/08/05 09:31:22 eric Exp $
 * @package FDL
 * @subpackage WSH
 */
global $action;

$action->log->deprecated(sprintf(_("API %s is deprecated. You should use %s instead.") , "get_param", "getApplicationParameter"));

include_once ("API/getApplicationParameter.php");
?>