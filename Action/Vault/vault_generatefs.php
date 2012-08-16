<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Generate new vault file system
 *
 * @author Anakeen
 * @version $Id: vault_generatefs.php,v 1.1 2006/11/28 18:28:16 eric Exp $
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
function vault_generatefs(&$action)
{
    // GetAllParameters
    $docid = GetHttpVars("id", 0);
    $classid = GetHttpVars("classid");
    $arrayid = strtolower(GetHttpVars("arrayid"));
    $vid = GetHttpVars("vid"); // special controlled view
    // Set the globals elements
    $dbaccess = $action->GetParam("FREEDOM_DB");
    
    $q = new QueryDb($dbaccess, "VaultDiskFsStorage");
    $q->dbaccess = $dbaccess;
    $q->basic_elem->dbaccess = $dbaccess; // correct for special constructor
    // SELECT count(id_file), sum(size) from vaultdiskstorage where id_file in (select vaultid from docvaultindex where docid in (select id from doc where doctype='Z')); // trash files
    // SELECT count(id_file), sum(size) from vaultdiskstorage where id_file not in (select vaultid from docvaultindex); //Orphean
    $l = $q->Query(0, 0, "TABLE");
    
    foreach ($l as $k => $fs) {
        
        $q = new QueryDb($dbaccess, "VaultDiskStorage");
        $q->dbaccess = $dbaccess;
        $q->basic_elem->dbaccess = $dbaccess; // correct for special constructor
        $nf = $q->Query(0, 0, "TABLE", "select count(id_file),sum(size) from vaultdiskstorage where id_fs='" . $fs["id_fs"] . "'");
        $used_size = $nf[0]["sum"];
        $q = new QueryDb($dbaccess, "VaultDiskFsStorage");
        $q->dbaccess = $dbaccess;
        $q->basic_elem->dbaccess = $dbaccess; // correct for special constructor
        $no = $q->Query(0, 0, "TABLE", "SELECT count(id_file), sum(size) from vaultdiskstorage where id_file not in (select vaultid from docvaultindex)"); //Orphean
        $nt = $q->Query(0, 0, "TABLE", "SELECT count(id_file), sum(size) from vaultdiskstorage where id_file in (select vaultid from docvaultindex where docid in (select id from doc where doctype='Z'))"); //trash files
        $free = intval($fs["free_size"]);
        $max = intval($fs["max_size"]);
        $free = $max - $used_size;
        $pci_used = (($max - $free) / $max * 100);
        $pcused = humanreadpc($pci_used);
        $effused = ($max - $free - $no[0]["sum"] - $nt[0]["sum"]);
        $realused = ($max - $free);
        $pci_realused = ($realused / $max * 100);
        $pci_effused = ($effused / $max * 100);
        $pceffused = sprintf("%d%%", $pci_effused);
        $pci_free = (100 - $pci_used);
        $pcfree = humanreadpc($pci_free);
        $tfs[$k] = array(
            "pcoccuped" => $pceffused,
            "pcfree" => $pcfree,
            "fsid" => $fs["id_fs"],
            "free" => humanreadsize($free) ,
            "total" => humanreadsize($max) ,
            "used" => humanreadsize($effused) ,
            "realused" => humanreadsize($realused) ,
            "pcrealused" => humanreadpc($pci_realused) ,
            "path" => $fs["r_path"]
        );
        
        $tfs[$k]["count"] = sprintf(_("%d stored files") , $nf[0]["count"]);
        $tfs[$k]["orphean_count"] = $no[0]["count"];
        $tfs[$k]["orphean_size"] = humanreadsize($no[0]["sum"]);
        $pci_orphean = (($no[0]["sum"] / $max) * 100);
        //if (($pci_orphean<1) && ($no[0]["count"]>0)) $pci_orphean=1;
        $tfs[$k]["trash_count"] = $nt[0]["count"];
        $tfs[$k]["trash_size"] = humanreadsize($nt[0]["sum"]);
        $pci_trash = (($nt[0]["sum"] / $max) * 100);
        $tfs[$k]["pctrash"] = humanreadpc($pci_trash);
        $tfs[$k]["pcminfree"] = ($pci_free > 1) ? sprintf("%.02f%%", $pci_free) : 1;
        $tfs[$k]["pcminoccuped"] = ($pci_effused > 1) ? sprintf("%.02f%%", $pci_effused) : 1;
        $tfs[$k]["pcmintrash"] = ($pci_trash > 1) ? sprintf("%.02f%%", $pci_trash) : 1;
        $tfs[$k]["pcorphean"] = humanreadpc($pci_orphean);
        $tfs[$k]["pcminorphean"] = ($pci_orphean > 1) ? sprintf("%.02f%%", $pci_orphean) : 1;
        $tfs[$k]["pcoccupedandpctrash"] = sprintf("%.02f%%", ($pci_free + $pci_orphean));
    }
    $action->lay->setBlockData("FS", $tfs);
}

function humanreadsize($bytes)
{
    if ($bytes < 1024) return sprintf(_("%d bytes") , $bytes);
    if ($bytes < 1048576) return sprintf(_("%d Kb") , $bytes / 1024);
    return sprintf(_("%d Mb") , $bytes / 1048576);
}
function humanreadpc($pc)
{
    if ($pc < 1) return sprintf("%.02f%%", $pc);
    return sprintf("%d%%", $pc);
}
?>
