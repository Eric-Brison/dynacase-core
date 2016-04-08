<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * @author Anakeen
 * @package FDL
 */
// ---------------------------------------------------------------
// $Id: Class.VaultDiskFsCache.php,v 1.6 2006/12/06 11:12:13 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/vault/Class/Class.VaultDiskFsCache.php,v $
// ---------------------------------------------------------------
//
//
// ---------------------------------------------------------------
include_once ("VAULT/Class.VaultDiskFs.php");

class VaultDiskFsCache extends VaultDiskFs
{
    
    function __construct($dbaccess, $id_fs = '')
    {
        $this->specific = "cache";
        parent::__construct($dbaccess, $id_fs);
    }
}
?>