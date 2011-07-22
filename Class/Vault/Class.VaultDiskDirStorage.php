<?php

/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */

// ---------------------------------------------------------------
// $Id: Class.VaultDiskDirStorage.php,v 1.5 2006/12/06 11:12:13 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/vault/Class/Class.VaultDiskDirStorage.php,v $
// ---------------------------------------------------------------

//
//
// ---------------------------------------------------------------
include_once("VAULT/Class.VaultDiskDir.php");

class VaultDiskDirStorage extends VaultDiskDir {

  function __construct($dbaccess, $id_dir='') {
    parent::__construct($dbaccess, $id_dir, "storage" );
  }

}
?>