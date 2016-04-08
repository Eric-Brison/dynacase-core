<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * get parameter value
 *
 * analyze sub-directories presents in STYLE directory
 * @subpackage WSH
 */
/**
 */
$usage = new ApiUsage();

$usage->setDefinitionText("get parameter value");
$parname = $usage->addOptionalParameter("param", "Parameter name");

$usage->verify();

print getParam($parname) . "\n";
?>