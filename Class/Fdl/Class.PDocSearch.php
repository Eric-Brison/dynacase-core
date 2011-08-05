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
 * @version $Id: Class.PDocSearch.php,v 1.9 2006/04/03 14:56:26 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 */

$CLASS_CONTACT_PHP = '$Id: Class.PDocSearch.php,v 1.9 2006/04/03 14:56:26 eric Exp $';

include_once ("FDL/Class.DocCollection.php");

class PDocSearch extends DocCollection
{
    // --------------------------------------------------------------------
    //---------------------- OBJECT CONTROL PERMISSION --------------------
    var $acls = array(
        "view",
        "edit",
        "delete",
        "execute",
        "unlock",
        "confidential"
    );
    // --------------------------------------------------------------------
    var $defDoctype = 'P';
    var $defProfFamId = FAM_ACCESSSEARCH;
    
    function __construct($dbaccess = '', $id = '', $res = '', $dbid = 0)
    {
        // don't use Doc constructor because it could call this constructor => infinitive loop
        DocCtrl::__construct($dbaccess, $id, $res, $dbid);
    }
}
?>