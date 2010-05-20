<?php
/**
 * to cache rseult one hour
 *
 * @author Anakeen 2005
 * @version $Id: cacheone.php,v 1.3 2008/08/12 12:42:17 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



function cacheone(&$action) {
  if (substr($action->lay->file,-3)==".js") $mime="text/javascript";
  else $mime="";
  setHeaderCache($mime);
}
?>