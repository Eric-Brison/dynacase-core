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
 * @version $Id: Class.TaskRequest.php,v 1.1 2007/05/31 16:14:57 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 */
/**
 */

include_once ("Class.DbObj.php");
Class TaskRequest extends DbObj
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
    /**
     * identificator of task
     * @public int
     */
    public $tid;
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
    
    public $id_fields = array(
        "tid"
    );
    
    public $dbtable = "taskrequest";
    
    public $sqlcreate = "
create table taskrequest ( tid int not null primary key,   
                   fkey text,            
                   uid int not null,
                   uname text,
                   status char,
                   date timestamp default now(),
                   comment text  );
";
}
?>