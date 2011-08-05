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
 * @version $Id: Class.DocUTag.php,v 1.2 2006/08/01 15:25:55 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 */
/**
 */

include_once ("Class.DbObj.php");
Class DocUtag extends DbObj
{
    public $fields = array(
        "id", // doc id
        "initid", // doc initid
        "uid", // user what id
        "uname", // use name
        "date", // date of entry
        "tag", // tag code
        "fromuid", // user what id of the user which has set the tag
        "fixed",
        "comment"
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
    public $tag;
    /**
     * identificator system of the author user
     * @public int
     */
    public $fromuid;
    
    public $fixed = 'false';
    
    public $id_fields = array(
        "id",
        "uid",
        "tag"
    );
    
    public $dbtable = "docutag";
    
    public $sqlcreate = "
create table docutag ( id int not null,   
                   initid int not null,                    
                   uid int not null,
                   uname text,
                   date timestamp,
                   tag text,
                   fromuid int,
                   fixed boolean default false,
                   comment text);
create index i_docutag on docutag(id);
create index in_docutag on docutag(initid);
";
}
?>