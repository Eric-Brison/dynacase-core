<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

/**
 * Document attribute enumerate
 * @class DocEnum
 */

class DocEnum extends DbObj
{
    
    public $fields = array(
        "famid",
        "attrid",
        "key",
        "label",
        "parentkey",
        "eorder"
    );
    /**
     * identifier of family of enum
     * @public int
     */
    public $famid;
    /**
     * identifier of family attribute which used enum
     * @public text
     */
    public $attrid;
    /**
     * enum value
     * @public string
     */
    public $key;
    /**
     * default label key
     * @public string
     */
    public $label;
    /**
     * order to display list enum items
     * @public string
     */
    public $eorder;
    /**
     * key of parent enum
     * @public int
     */
    public $parentkey;
    
    public $id_fields = array(
        "famid",
        "attrid",
        "key"
    );
    
    public $dbtable = "docenum";
    
    public $sqlcreate = '
create table docenum (
                   famid int not null,
                   attrid text not null,
                   key text,
                   label text,
                   parentkey text,
                   eorder int);
create index if_docenum on docenum(famid, attrid);
create unique index i_docenum on docenum(famid, attrid,  key);
';
    
    public function exists()
    {
        if ($this->famid && $this->attrid && $this->key !== null) {
            simpleQuery($this->dbaccess, sprintf("select true from docenum where famid='%s' and attrid='%s' and key='%s'", pg_escape_string($this->famid) , pg_escape_string($this->attrid) , pg_escape_string($this->key)) , $r, true, true);
            return $r;
        }
        return false;
    }
}
?>