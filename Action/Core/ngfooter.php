<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: ngfooter.php,v 1.2 2005/10/31 14:05:56 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
include_once("CORE/Lib.Ng.php");
function ngfooter(&$action) {
  global $_SERVER; // PHP_AUTH_USER
  $action->lay->set("PHP_AUTH_USER",$_SERVER["PHP_AUTH_USER"]);
  $p = ng_myportal();
  $action->lay->set("idportal", $p->id);
}
?>

