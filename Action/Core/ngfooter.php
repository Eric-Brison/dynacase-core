<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: ngfooter.php,v 1.1 2005/10/25 08:39:35 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
function ngfooter(&$action) {
  global $_SERVER; // PHP_AUTH_USER
  $action->lay->set("PHP_AUTH_USER",$_SERVER["PHP_AUTH_USER"]);
}
?>

