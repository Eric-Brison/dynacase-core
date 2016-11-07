<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * The classic phpinfo page to see PHP configuration and parameters
 *
 * @author Anakeen
 * @version $Id: phpinfo.php,v 1.3 2009/01/16 13:33:00 jerome Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ('../../../WHAT/Lib.Prefix.php');
include_once ('../../../WHAT/Lib.Phpini.php');

\Dcp\Core\LibPhpini::applyLimits();

phpinfo();
?>