<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Mapping Attributes between LDAP & FREEDOM
 *
 * @author Anakeen
 * @version $Id: Class.DocAttrLDAP.php,v 1.4 2006/04/03 14:56:26 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 */

include_once ("Class.DbObj.php");
class DocAttrLDAP extends DbObj
{
    public $fields = array(
        "famid", // family id
        "ldapname", //
        "ldapmap", //
        "ldapclass",
        "index"
    );
    /**
     * identifier of the family document
     * @public int
     */
    public $famid;
    /**
     * identifier of the LDAP attribute
     * @public string
     */
    public $ldapname;
    /**
     * map function
     * @public string
     */
    public $ldapmap;
    /**
     * LDAP class of attribute
     * @public string
     */
    public $ldapclass;
    /**
     * indice to indicate the card reference in case of multi-card LDAP for one document
     * @public character
     */
    public $index;
    
    public $id_fields = array(
        "famid",
        "ldapname",
        "index"
    );
    
    public $dbtable = "docattrldap";
    
    public $sqlcreate = "create table docattrldap (famid  int not null,                   
                    ldapname text not null,
                    ldapmap text,
                    ldapclass text,
                    index char);
create index i_docattrldap on docattrldap(famid,ldapname);";
}
?>