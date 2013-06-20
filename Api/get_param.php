<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * get parameter value
 *
 * analyze sub-directories presents in STYLE directory
 * @author Anakeen
 * @version $Id: get_param.php,v 1.1 2004/08/05 09:31:22 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage WSH
 */
global $action;

$action->log->deprecated(sprintf(_("API %s is deprecated. You should use %s instead.") , "get_param", "getApplicationParameter"));

include_once ("API/getApplicationParameter.php");
?>