<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Profil for family document
 *
 * @author Anakeen
 * @version $Id: Class.PFam.php,v 1.6 2008/06/03 12:57:28 eric Exp $
 * @package FDL
 */
/**
 */

include_once ("FDL/Class.Doc.php");

class PFam extends Doc
{
    // --------------------------------------------------------------------
    //---------------------- OBJECT CONTROL PERMISSION --------------------
    var $acls = array(
        "view",
        "edit",
        "create",
        "icreate"
    );
    
    var $defDoctype = 'P';
    var $defProfFamId = FAM_ACCESSFAM;
    
    function __construct($dbaccess = '', $id = '', $res = '', $dbid = 0)
    {
        // don't use Doc constructor because it could call this constructor => infinitive loop
        DocCtrl::__construct($dbaccess, $id, $res, $dbid);
    }
    
    function preImport(array $extra = array())
    {
        if ($this->getRawValue("dpdoc_famid")) {
            return ErrorCode::getError('PRFL0202', $this->getRawValue('ba_title'));
        }
        return '';
    }
}
?>