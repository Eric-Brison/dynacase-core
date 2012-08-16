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
 * @version $Id: vault_movefs.php,v 1.1 2006/12/06 12:25:58 eric Exp $
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
function vault_movefs(&$action)
{
    // GetAllParameters
    $idfs = GetHttpVars("idfs");
    $directory = GetHttpVars("directory");
    // Set the globals elements
    $dbaccess = $action->GetParam("FREEDOM_DB");
    $fs = new VaultDiskFsStorage($dbaccess, $idfs);
    
    if ($fs->isAffected()) {
        
        if (!is_dir($directory)) $action->AddWarningMsg(sprintf(_("%s directory not found") , $directory));
        else if (!is_writable($directory)) $action->AddWarningMsg(sprintf(_("%s directory not writable") , $directory));
        else {
            $action->AddWarningMsg(sprintf(_("new directory %s") , $directory));
            $fs->r_path = $directory;
            $err = $fs->modify();
        }
    }
    redirect($action, "VAULT", "VAULT_VIEW", $action->GetParam("CORE_STANDURL"));
}
?>
