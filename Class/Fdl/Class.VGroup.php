<?php
/**
 * Virtual groups
 *
 * @author Anakeen 2004
 * @version $Id: Class.VGroup.php,v 1.2 2004/02/12 10:32:09 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 */
 /**
 */




include_once('Class.DbObj.php');
include_once('Class.QueryDb.php');
include_once('Class.Log.php');
define("STARTIDVGROUP",1000000);
/**
 * Virtual groups
 * @package FREEDOM
 *
 */
Class VGroup extends DbObj
{
  var $fields = array ("id",
		       "num");

  var $id_fields = array ("id");

  var $dbtable = "vgroup";

  var $order_by="id";


  var $sqlcreate = "
create table vgroup ( id  text primary key,
                      num int not null);
create sequence seq_id_docvgroup start 1000000";


  var $isCacheble= false;
		            
  function PreInsert()
    {

      // compute new id

  
      if ($this->num == "") {
	$res = pg_exec($this->dbid, "select nextval ('seq_id_docvgroup')");
	$arr = pg_fetch_array ($res, 0);
	$this->num = $arr[0];  // not a number must be alphanumeric begin with letter
      }

    } 


    
}
?>