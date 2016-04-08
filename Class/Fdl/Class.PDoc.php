<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: Class.PDoc.php,v 1.15 2008/08/05 15:16:58 eric Exp $
 * @package FDL
 */
/**
 */

include_once ("FDL/Class.Doc.php");

class PDoc extends Doc
{
    // --------------------------------------------------------------------
    //---------------------- OBJECT CONTROL PERMISSION --------------------
    var $acls = array(
        "view",
        "edit",
        "delete",
        "send",
        "unlock",
        "confidential",
        "wask"
    );
    // --------------------------------------------------------------------
    // ------------
    var $defDoctype = 'P';
    var $defProfFamId = FAM_ACCESSDOC;
    
    function __construct($dbaccess = '', $id = '', $res = '', $dbid = 0)
    {
        // don't use Doc constructor because it could call this constructor => infinitive loop
        DocCtrl::__construct($dbaccess, $id, $res, $dbid);
    }
}
?>