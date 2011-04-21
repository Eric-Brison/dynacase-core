<?php
/**
 * Waiting documents
 * Temporary saving
 *
 * @author Anakeen 
 * @version $Id:  $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 */
/**
 */



include_once ("Class.DbObj.php");
class DocWait extends DbObj
{
    public $fields = array(
        "refererid", // doc id
        "refererinitid", // doc initid
        "title", // doc title
        "fromid", // family
        "values", // values of document (serialized object)  
        "uid", // user id
        "domain", // domain id
        "status", // status
        "transaction", // transaction id
        "date"
    );
    

    /**
     * identificator of referer document
     * @public int
     */
    public $refererid;
    
    /**
     * identificator system of the user
     * @public int
     */
    public $uid;
    /**
     * document title
     * @public string
     */
    public $title;
    /**
     * date record
     * @public date
     */
    public $date;
    /**
     * document status : ok|constraint|obsolete
     * @public string
     */
    public $status;
    
    /**
     * arg of code
     * @public text serialize object
     */
    public $arg;
    
    public $id_fields = array(
        "refererinitid", "uid"
    );
    public $dbtable = "docwait";
    
    public $sqlcreate = "
create table docwait ( refererid int not null,   
                   refererinitid int not null,
                   fromid int,
                   title text,
                   uid int not null,
                   values text,
                   date timestamp default now(),
                   domain int,
                   transaction int,
                   status text );
create index i_docwait on docwait(transaction);
create unique index iu_docwait on docwait(refererinitid, uid);
create sequence seq_waittransaction start 1;
";

}
?>