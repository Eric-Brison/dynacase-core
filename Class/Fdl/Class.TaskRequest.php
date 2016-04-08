<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * History log for document
 *
 * @author Anakeen
 * @version $Id: Class.TaskRequest.php,v 1.1 2007/05/31 16:14:57 eric Exp $
 * @package FDL
 */
/**
 */

include_once ("Class.DbObj.php");
class TaskRequest extends DbObj
{
    public $fields = array(
        "tid", // task id
        "fkey", // foreign key
        "uid", // user what id
        "uname", // use name
        "status", // status of entry
        "comment"
        // comment text
        
    );
    public $sup_fields = array(
        "date", // date of entry
        
    );
    public $comment;
    public $fkey;
    public $status;
    /**
     * identifier of task
     * @public int
     */
    public $tid;
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
    
    public $id_fields = array(
        "tid"
    );
    
    public $dbtable = "taskrequest";
    
    public $sqlcreate = <<< 'SQL'
CREATE TABLE taskrequest (
    tid TEXT NOT NULL PRIMARY KEY,
    fkey TEXT,
    uid INT NOT NULL,
    uname TEXT,
    status CHAR,
    date TIMESTAMP DEFAULT NOW(),
    comment TEXT
);
SQL;
}
?>