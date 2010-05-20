<?php
/**
 * History log for document
 *
 * @author Anakeen 2005
 * @version $Id: Class.DocHisto.php,v 1.1 2006/06/08 16:03:13 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 */
/**
 */

/**#@+
 * constant for log level history
 * 
 */
define ("LOG_NOTICE", 1);
define ("LOG_INFO", 2);
define ("LOG_NOTIFY", 4);
define ("LOG_WARNING", 8);
define ("LOG_ERROR", 16);

include_once("Class.DbObj.php");
Class DocLog extends DbObj {
  public $fields = array ( "id", // doc id
                           "initid", // doc initid
			   "title", // doc title
			   "uid",  // user what id
			   "uname", // use name			  
			   "level", // log level
			   "code", // code log
			   "arg", // arg serialized object
			   "comment"// comment text
			   );

  public $sup_fields = array (
			   "date", // date of entry
			   );
  /**
   * identificator of document
   * @public int
   */
  public $id;  
			  
  /**
   * identificator system of the user
   * @public int
   */
  public $uid;		  
  /**
   * firstname and last name of the user
   * @public string
   */
  public $uname;		  
  /**
   * comment date record
   * @public date
   */
  public $date;		  
  /**
   * level of comment
   * @public int
   */
  public $level;
	  
  /**
   * arg of code
   * @public text serialize object
   */
  public $arg;

  public $id_fields = array ("id");
  public $dbtable = "doclog";

  public $sqlcreate = "
create table doclog ( id int not null,   
                   initid int not null,
                   title text,
                   uid int not null,
                   uname text,
                   date timestamp default now(),
                   level int,
                   code text not null,
                   arg text,
                   comment text  );
create index i_doclog on doclog(id);
create index date_doclog on doclog(date);
";


}
?>