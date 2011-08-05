<?php

/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */

// ---------------------------------------------------------------
// $Id: Class.VaultDiskFsCache.php,v 1.6 2006/12/06 11:12:13 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/vault/Class/Class.VaultDiskFsCache.php,v $
// ---------------------------------------------------------------

//
//
// ---------------------------------------------------------------
include_once("VAULT/Class.VaultDiskFs.php");

class VaultDiskFsCache extends VaultDiskFs {

  function __construct($dbaccess, $id_fs='') {
    $this->specific = "cache";
    parent::__construct($dbaccess, $id_fs);
  }

}
?>