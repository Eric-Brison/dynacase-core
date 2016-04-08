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
// $Id: Class.VaultDiskDirCache.php,v 1.5 2006/12/06 11:12:13 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/vault/Class/Class.VaultDiskDirCache.php,v $
// ---------------------------------------------------------------
//
//
//
// ---------------------------------------------------------------
include_once ("VAULT/Class.VaultDiskDir.php");

class VaultDiskDirCache extends VaultDiskDir
{
    
    function __construct($dbaccess, $id_dir = '')
    {
        parent::__construct($dbaccess, $id_dir, "cache");
    }
}
?>