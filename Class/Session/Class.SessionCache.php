<?php
/**
 * Cache session date of validated
 *
 * @author Anakeen 2000 
 * @version $Id: Class.SessionCache.php,v 1.4 2005/06/28 13:53:24 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
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


 function __construct($dbaccess='', $id='',$res='',$dbid=0) {
   parent::__construct($dbaccess, $id,$res,$dbid);
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
