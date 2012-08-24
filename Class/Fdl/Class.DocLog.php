<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * History log for document
 *
 * @author Anakeen
 * @version $Id: Class.DocHisto.php,v 1.1 2006/06/08 16:03:13 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 */
/**#@+
 * constant for log level history
 *
*/

include_once ("Class.DbObj.php");
class DocLog extends DbObj
{
    const LOG_NOTICE = 1;
    const LOG_INFO = 2;
    const LOG_NOTIFY = 4;
    const LOG_WARNING = 8;
    const LOG_ERROR = 16;
    
    public $fields = array(
        "id", // doc id
        "initid", // doc initid
        "title", // doc title
        "uid", // user what id
        "uname", // use name
        "level", // log level
        "code", // code log
        "arg", // arg serialized object
        "comment"
        // comment text
        
    );
    
    public $sup_fields = array(
        "date"
        // date of entry
        
    );
    /**
     * identifier of document
     * @public int
     */
    public $id;
    /**
     * identifier system of the user
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
    
    public $initid;
    /**
     * @var string document title
     */
    public $title;
    /**
     * @var string log message
     */
    public $comment;
    public $code;
    public $id_fields = array(
        "id"
    );
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
create index in_doclog on doclog(initid);
create index date_doclog on doclog(date);
";
}
?>