<?php
/**
 * get parameter value
 *
 * analyze sub-directories presents in STYLE directory
 * @author Anakeen 2002
 * @version $Id: get_param.php,v 1.1 2004/08/05 09:31:22 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage WSH
 */
/**
 */

$parname = GetHttpVars("param"); // familly filter
print getParam($parname)."\n";

?>