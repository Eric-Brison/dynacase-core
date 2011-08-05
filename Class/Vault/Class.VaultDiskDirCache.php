<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package VAULT
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

Class VaultDiskDirCache extends VaultDiskDir
{
    
    function __construct($dbaccess, $id_dir = '')
    {
        parent::__construct($dbaccess, $id_dir, "cache");
    }
}
?>