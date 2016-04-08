<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Virtual groups
 *
 * @author Anakeen
 * @version $Id: Class.VGroup.php,v 1.2 2004/02/12 10:32:09 eric Exp $
 * @package FDL
 */
/**
 */

include_once ('Class.DbObj.php');
include_once ('Class.QueryDb.php');
include_once ('Class.Log.php');
define("STARTIDVGROUP", 1000000);
/**
 * Virtual groups
 * @package FDL
 *
 */
class VGroup extends DbObj
{
    var $fields = array(
        "id",
        "num"
    );
    
    var $id_fields = array(
        "id"
    );
    
    public $id;
    public $num;
    var $dbtable = "vgroup";
    
    var $order_by = "id";
    
    var $sqlcreate = "
create table vgroup ( id  text primary key,
                      num int not null);
create sequence seq_id_docvgroup start 1000000;";
    
    function PreInsert()
    {
        // compute new id
        if ($this->num == "") {
            $res = pg_query($this->dbid, "select nextval ('seq_id_docvgroup')");
            $arr = pg_fetch_array($res, 0);
            $this->num = $arr[0]; // not a number must be alphanumeric begin with letter
            
        }
    }
}
?>