<?php
/**
 * Cache session date of validated
 *
 * @author Anakeen 2000 
 * @version $Id: Class.SessionCache.php,v 1.3 2005/03/01 17:23:08 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
 /**
 */

include_once('Class.DbObj.php');

Class SessionCache extends DbObj
{

var $fields = array (  "index", "lasttime");

var $id_fields = array ("index");

var $dbtable = "session_cache";

var $sqlcreate = "create table session_cache ( index varchar(100), 
			    lasttime	    int);";
 var $isCacheble= false;


 function SessionCache($dbaccess='', $id='',$res='',$dbid=0) {
   DbObj::DbObj($dbaccess, $id,$res,$dbid);
   if ((! $this->isAffected()) && ($id != '')) {
     $this->index = $id;
     
     $date = gettimeofday();
     $this->lasttime = $date['sec'];
     $this->Add();
     
   }
 }
 // modify with current date
 function setTime() {
      $date = gettimeofday();
      $this->lasttime = $date['sec'];
      $this->Modify();
 }



}
?>
