<?php
/**
 * Display WHAT logo
 *
 * @author Anakeen 1999
 * @version $Id: logo.php,v 1.7 2004/03/22 15:21:40 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
/**
 */




/**
 * Display WHAT logo and clear object cache
 */
function logo(&$action) {
  global $_SERVER;
  global $CacheObj;

  $CacheObj=array();
 
  unset($_SESSION["CacheObj"]);// clearcache
  $action->lay->set("PHP_AUTH_USER",$_SERVER['PHP_AUTH_USER']);    

  $action->lay->set("navigator",$action->Read("navigator"));  
  $action->lay->set("navversion",$action->Read("navversion")); 
  global $zou;
  $zou="1";
  session_register("zou");
}
?>