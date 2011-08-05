<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000
 * @version $Id: Class.DocAttr.php,v 1.24 2007/02/27 10:05:17 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 */
// ---------------------------------------------------------------
// $Id: Class.DocAttr.php,v 1.24 2007/02/27 10:05:17 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Class/Fdl/Class.DocAttr.php,v $
// ---------------------------------------------------------------
$CLASS_CONTACTATTR_PHP = '$Id: Class.DocAttr.php,v 1.24 2007/02/27 10:05:17 eric Exp $';
include_once ('Class.DbObj.php');
include_once ('Class.QueryDb.php');
include_once ('Class.Log.php');
/**
 * Database Attribute document
 * @package FDL
 *
 */
class DocAttr extends DbObj
{
    var $fields = array(
        "id",
        "docid",
        "frameid",
        "labeltext",
        "title",
        "abstract",
        "type",
        "ordered",
        "visibility", // W, R, H, O, M, C
        "needed",
        "link",
        "phpfile",
        "phpfunc",
        "elink",
        "phpconstraint",
        "usefor",
        "options"
    );
    
    var $id_fields = array(
        "docid",
        "id"
    );
    
    var $dbtable = "docattr";
    
    var $order_by = "ordered";
    
    var $fulltextfields = array(
        "labeltext"
    );
    
    var $sqlcreate = "
create table docattr ( id  name,
                     docid int not null,
                     frameid  name,
                     labeltext text,
                     title  char,
                     abstract  char,
                     type  text,
                     ordered int,
                     visibility char,
                     needed char,
                     link text,
                     phpfile text,
                     phpfunc text,
                     elink text,
                     phpconstraint text,
                     usefor char DEFAULT 'N',
                     options text
                   );
create sequence seq_id_docattr start 1000;
create unique index idx_iddocid on docattr(id, docid)";
    // possible type of attributes
    var $deftype = array(
        "text",
        "longtext",
        "image",
        "file",
        "frame",
        "enum",
        "date",
        "integer",
        "double",
        "money",
        "password"
    );
    var $isCacheble = false;
    
    function PreInsert()
    {
        // compute new id
        if ($this->id == "") {
            $res = pg_exec($this->dbid, "select nextval ('seq_id_docattr')");
            $arr = pg_fetch_array($res, 0);
            $this->id = "auto_" . $arr[0]; // not a number must be alphanumeric begin with letter
            
        }
        $this->id = strtolower($this->id);
        if ($this->id[0] != ':') {
            if ($this->type == "") $this->type = "text";
            if ($this->abstract == "") $this->abstract = 'N';
            if ($this->title == "") $this->title = 'N';
            if ($this->usefor == "") $this->usefor = 'N';
            if ($this->visibility == "") $this->visibility = 'W';
        }
    }
    
    function CVisibility()
    {
        
        if ((isset($this->fieldSet)) && ($this->fieldSet->visibility == "H")) return "H";
        else if (($this->fieldSet->visibility == "R") && ($this->visibility != "H")) return "R";
        return $this->visibility;
    }
}
?>
