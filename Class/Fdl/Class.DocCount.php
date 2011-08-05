<?php
/**
 * Cache table to memorize count doc of different sql filter
 *
 * @author Anakeen 2008
 * @version $Id: Class.DocCount.php,v 1.1 2008/08/13 15:17:07 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 */
/**
 */



include_once("Class.DbObj.php");

Class DocCount extends DbObj {
  public $fields = array ( "famid", // family identificator
                           "aid", // attribute identificator
			   "filter",  // sql filter
			   "c", // count
			   );

  /**
   * family identificator
   * @public string
   */
  public $famid;  
			  
  /**
   * attribute identificator
   * @public string
   */
  public $aid;		  
  /**
   * sql filter of the query
   * @public string
   */
  public $filter;		  
  /**
   * count result
   * @public int
   */
  public $c;		  
  


  public $id_fields = array ("famid","aid","filter");

  public $dbtable = "doccount";


  public $sqlcreate = "
create table doccount ( famid int not null,   
                   aid text not null,                    
                   filter text not null,
                   c int  );
create index i_doccount on dochisto(famid,aid);
";

  function deleteAll() {
    $sql = sprintf("delete from %s where famid = %s and aid = '%s'",
		   $this->dbtable,
		   $this->famid,pg_escape_string($this->aid));

    return  $this->exec_query($sql);    
  }
}
?>