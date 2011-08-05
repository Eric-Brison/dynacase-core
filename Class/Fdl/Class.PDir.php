<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 * Profile for folders
 *
 * @author Anakeen 2000
 * @version $Id: Class.PDir.php,v 1.12 2007/10/11 12:35:10 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 */
/**
 */

include_once ("FDL/Class.DocCollection.php");

Class PDir extends DocCollection
{
    // --------------------------------------------------------------------
    //---------------------- OBJECT CONTROL PERMISSION --------------------
    var $acls = array(
        "view",
        "edit",
        "delete",
        "open",
        "modify",
        "send",
        "unlock",
        "confidential",
        "forum"
    );
    // --------------------------------------------------------------------
    
    var $defDoctype = 'P';
    var $defProfFamId = FAM_ACCESSDIR;
    
    function __construct($dbaccess = '', $id = '', $res = '', $dbid = 0)
    {
        // don't use Doc constructor because it could call this constructor => infinitive loop
        DocCtrl::__construct($dbaccess, $id, $res, $dbid);
    }
}
?>