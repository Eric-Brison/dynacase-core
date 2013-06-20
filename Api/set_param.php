<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * set applicative parameter value
 *
 * analyze sub-directories presents in STYLE directory
 * @author Anakeen
 * @version $Id: set_param.php,v 1.3 2006/04/28 14:31:49 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage WSH
 */
global $action;

$action->log->deprecated(sprintf(_("API %s is deprecated. You should use %s instead.") , "set_param", "setApplicationParameter"));

include_once ("API/setApplicationParameter.php");
