<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Increase size of vault file system
 *
 * @author Anakeen
 * @version $Id: vault_clean.php,v 1.1 2006/12/08 17:53:48 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("VAULT/Class.VaultDiskStorage.php");
include_once ("VAULT/Class.VaultDiskFsStorage.php");
include_once ("FDL/Class.DocVaultIndex.php");
// -----------------------------------
function vault_clean(&$action)
{
    // GetAllParameters
    $idfs = GetHttpVars("idfs");
    $clean = GetHttpVars("clean", "orphan");
    // Set the globals elements
    $dbaccess = $action->GetParam("FREEDOM_DB");
    $fs = new VaultDiskFsStorage($dbaccess, $idfs);
    
    if ($fs->isAffected()) {
        
        $q = new QueryDb($dbaccess, "VaultDiskFsStorage");
        
        $no = $q->Query(0, 0, "TABLE", "SELECT * from vaultdiskstorage where $sqlfs id_file not in (select vaultid from docvaultindex)"); //Orphean
        //print_r2($no);
        $wsh = getWshCmd(true);
        $cmd = $wsh . " --api=VaultExamine --cmd=clean-unref";
        
        $cmd.= " >/dev/null";
        
        system($cmd, $status);
        if ($status == 0) AddWarningMsg(sprintf(_("Orphan Cleaned in %s directory") , $fs->r_path));
        else AddWarningMsg(sprintf(_("Error : Cleaning %s  status %d") , $fs->r_path, $status));
    }
    redirect($action, "VAULT", "VAULT_VIEW", $action->GetParam("CORE_STANDURL"));
}
?>
