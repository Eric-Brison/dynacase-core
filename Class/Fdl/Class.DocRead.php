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
 * @version $Id: Class.DocRead.php,v 1.6 2007/10/16 10:22:57 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 */

include_once ("Class.Doc.php");
class DocRead extends DbObj
{
    /**
     * identificator of document
     * @public int
     */
    public $id;
    
    public $id_fields = array(
        "id"
    );
    
    public $dbtable = "docread";
    public $sqlcreate = "
create table docread ( id int not null,
                   primary key (id),
                   owner int,
                   title varchar(256),
                   revision int ,
                   initid int,
                   fromid int,
                   doctype char,
                   locked int ,
                   allocated int ,
                   archiveid int,
                   icon varchar(256),
                   lmodify char,
                   profid int ,
                   usefor text,
                   revdate int, 
                   version text,
                   cdate timestamp,  
                   adate timestamp,  
                   comment text,
                   classname varchar(64),
                   state varchar(64),
                   wid int ,  
                   values text,  
                   attrids text,   
                   forumid int,
                   domainid text,
                   lockdomainid int,
                   postitid text,
                   cvid int,
                   name text,
                   dprofid int,
                   views int[],
                   prelid int,
                   atags text,
                   confidential int,
                   ldapdn text,
                   fulltext tsvector,
                   svalues text
                   );
create index fromid_docread on docread(fromid);
create index initid_docread on docread(initid);
create index title_docread on docread(title);
create index docty_docread on docread(doctype);
create index full_docread on docread using gist(fulltext);";
    
    public $fields = array(
        "id",
        "owner",
        "title",
        "revision",
        "version",
        "initid",
        "fromid",
        "doctype",
        "locked",
        "allocated",
        "archiveid",
        "icon",
        "lmodify",
        "profid",
        "usefor",
        "cdate",
        "adate",
        "revdate",
        "comment",
        "classname",
        "state",
        "wid",
        "postitid",
        "forumid",
        "domainid",
        "lockdomainid",
        "cvid",
        "name",
        "dprofid",
        "atags",
        "prelid",
        "confidential",
        "ldapdn"
    );
    
    public $sup_fields = array(
        "values",
        "svalues",
        "attrids"
    ); // not be in fields else trigger error
    
}
?>