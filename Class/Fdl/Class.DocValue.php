<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: Class.DocValue.php,v 1.8 2003/08/18 15:47:04 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 */
// ---------------------------------------------------------------
// $Id: Class.DocValue.php,v 1.8 2003/08/18 15:47:04 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Class/Fdl/Class.DocValue.php,v $
// ---------------------------------------------------------------
$CLASS_CONTACTVALUE_PHP = '$Id: Class.DocValue.php,v 1.8 2003/08/18 15:47:04 eric Exp $';
include_once ('Class.DbObj.php');
include_once ('Class.QueryDb.php');
include_once ('Class.Log.php');
/**
 * Values of Attribute document
 * @package FDL
 * @deprecated
 *
 */
class DocValue extends DbObj
{
    var $fields = array(
        "docid",
        "attrid",
        "value"
    );
    
    var $id_fields = array(
        "docid",
        "attrid"
    );
    
    var $dbtable = "docvalue";
    
    var $order_by = "docid";
    
    var $fulltextfields = array(
        "docid",
        "attrid",
        "value"
    );
    
    var $sqlcreate = "
create table docvalue ( docid  int not null,
                        attrid name not null,
                        value  text
                   ); 
create unique index idx_docvalue on docvalue (docid, attrid);";
    // --------------------------------------------------------------------
    function PreUpdate()
    {
        // modify need to add before if not exist
        $query = new QueryDb($this->dbaccess, "Docvalue");
        
        $query->basic_elem->sup_where = array(
            "attrid = '" . $this->attrid . "'",
            "docid = " . $this->docid
        );
        
        $query->Query();
        if ($query->nb == 0) $this->Add();
    }
    // return docs where text is in value
    function GetDocids($text)
    {
        
        $query = new QueryDb($this->dbaccess, get_class($this));
        
        $query->basic_elem->sup_where = array(
            "value ~* '.*$text.*'"
        );
        
        $table1 = $query->Query();
        
        $title = array();
        
        if ($query->nb > 0) {
            while (list($k, $v) = each($table1)) {
                $title[$k] = $v->docid;
            }
            unset($table1);
        }
        return $title;
    }
    // delete all values for a document
    function DeleteValues($docid)
    {
        
        $query = new QueryDb($this->dbaccess, get_class($this));
        
        $query->basic_elem->sup_where = array(
            "docid = $docid"
        );
        
        $table1 = $query->Query();
        
        if ($query->nb > 0) {
            
            while (list($k, $v) = each($table1)) {
                $table1[$k]->delete();
            }
            unset($table1);
        }
    }
}
?>
