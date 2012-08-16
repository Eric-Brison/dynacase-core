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
/**
 */
$usage = new ApiUsage();

$usage->setText("get parameter value");
$parname = $usage->addOption("param", "Parameter name");

$usage->verify();

print getParam($parname) . "\n";
?>