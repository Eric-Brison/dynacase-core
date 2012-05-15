<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * History log for document
 *
 * @author Anakeen 2005
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
define("HISTO_NOTICE", 1);
define("HISTO_INFO", 2);
define("HISTO_MESSAGE", 4);
define("HISTO_WARNING", 8);
define("HISTO_ERROR", 16);

include_once ("Class.DbObj.php");
class DocHisto extends DbObj
{
    public $fields = array(
        "id", // doc id
        "initid", // doc initid
        "uid", // user what id
        "uname", // use name
        "date", // date of entry
        "level", // log level
        "code", // code log
        "comment"
        // comment text
        
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
    public $initid;
    /**
     * @var string history key code
     */
    public $code;
    /**
     * @var string history message
     */
    public $comment;
    
    public $id_fields = array(
        "id"
    );
    
    public $dbtable = "dochisto";
    
    public $sqlcreate = "
create table dochisto ( id int not null,   
                   initid int not null,                    
                   uid int not null,
                   uname text,
                   date timestamp,
                   level int,
                   code text,
                   comment text  );
create index i_dochisto on dochisto(id);
create index in_dochisto on dochisto(initid);
";
}
?>