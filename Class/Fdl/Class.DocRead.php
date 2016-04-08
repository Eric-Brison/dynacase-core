<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * History log for document
 *
 * @author Anakeen
 * @version $Id: Class.DocRead.php,v 1.6 2007/10/16 10:22:57 eric Exp $
 * @package FDL
 */
/**
 */

include_once ("Class.Doc.php");
class DocRead extends DbObj
{
    /**
     * identifier of document
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
                   icon text,
                   lmodify char,
                   profid int ,
                   usefor text,
                   revdate int, 
                   version text,
                   cdate timestamp,  
                   adate timestamp,  
                   comment text,
                   classname text,
                   state text,
                   wid int ,  
                   values text,  
                   attrids text,
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
        "domainid",
        "lockdomainid",
        "cvid",
        "name",
        "dprofid",
        "views",
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