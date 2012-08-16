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
 * @version $Id: vault_diskimage.php,v 1.2 2008/11/24 16:10:23 eric Exp $
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
function vault_diskimage(&$action)
{
    // GetAllParameters
    $idfs = GetHttpVars("idfs");
    $dbaccess = $action->GetParam("FREEDOM_DB");
    
    $q = new QueryDb($dbaccess, "VaultDiskFsStorage");
    $q->AddQuery("id_fs=" . intval($idfs));
    $l = $q->Query(0, 0, "TABLE");
    
    $fs = $l[0];
    
    $nf = $q->Query(0, 0, "TABLE", "select count(id_file),sum(size) from vaultdiskstorage where id_fs='" . pg_escape_string($idfs) . "'");
    $sqlfs = "id_fs=" . intval($idfs) . " and ";
    $used_size = $nf[0]["sum"];
    $q = new QueryDb($dbaccess, "VaultDiskFsStorage");
    
    $no = $q->Query(0, 0, "TABLE", "SELECT count(id_file), sum(size) from vaultdiskstorage where $sqlfs id_file not in (select vaultid from docvaultindex where docid>0)"); //Orphean
    $nt = $q->Query(0, 0, "TABLE", "SELECT count(id_file), sum(size) from vaultdiskstorage where $sqlfs id_file in (select vaultid from docvaultindex where docid in (select id from docread where doctype='Z'))"); //trash files
    $free = doubleval($fs["free_size"]);
    $max = doubleval($fs["max_size"]);
    $free = $max - $used_size;
    $pci_used = (($max - $free) / $max * 100);
    $effused = ($max - $free - $no[0]["sum"] - $nt[0]["sum"]);
    $realused = ($max - $free);
    $pci_realused = ($realused / $max * 100);
    $pci_effused = ($effused / $max * 100);
    $pci_free = (100 - $pci_used);
    $pci_trash = (($nt[0]["sum"] / $max) * 100);
    creatediskimage($pci_free, $pci_effused, $pci_trash, 10);
}

function creatediskimage($pcfree, $pcused, $pctrash, $pcorphan)
{
    // create image
    $w = 200;
    $w2 = $w / 2;
    $x3 = $w / 2;
    $y3 = $w / 3;
    
    if ($pcfree < 0) $pcfree = 0;
    if ($pcused > 100) $pcused = 100;
    
    $afree = 360 - ($pcfree * 3.6);
    $aused = $afree - ($pcused * 3.6);
    $atrash = $aused - ($pctrash * 3.6);
    
    $image = imagecreatetruecolor($w, $w / 1.5);
    imagecolortransparent($image, 0);
    // allocate some solors
    $orange = imagecolorallocate($image, 0xb5, 0x94, 0x12); #b59412
    $darkorange = imagecolorallocate($image, 0xb5, 0x74, 0x12);
    $brown = imagecolorallocate($image, 0xb5, 0x4f, 0x12); #b54f12
    $darkbrown = imagecolorallocate($image, 0xa5, 0x2f, 0x02);
    $red = imagecolorallocate($image, 0xFF, 0x00, 0x00);
    $darkred = imagecolorallocate($image, 0x90, 0x00, 0x00);
    $green = imagecolorallocate($image, 0x00, 0x90, 0x00);
    $darkgreen = imagecolorallocate($image, 0x00, 0x50, 0x00);
    // make the 3D effect
    for ($i = $y3 + 10; $i > $y3; $i--) {
        imagefilledarc($image, $x3, $i, $w, $w2, 0, $atrash, $darkorange, IMG_ARC_PIE);
        imagefilledarc($image, $x3, $i, $w, $w2, $atrash, $aused, $darkbrown, IMG_ARC_PIE);
        imagefilledarc($image, $x3, $i, $w, $w2, $aused, $afree, $darkred, IMG_ARC_PIE);
        imagefilledarc($image, $x3, $i, $w, $w2, $afree, 360, $darkgreen, IMG_ARC_PIE);
    }
    
    imagefilledarc($image, $x3, $y3, $w, $w2, 0, $atrash, $orange, IMG_ARC_PIE);
    imagefilledarc($image, $x3, $y3, $w, $w2, $atrash, $aused, $brown, IMG_ARC_PIE);
    imagefilledarc($image, $x3, $y3, $w, $w2, $aused, $afree, $red, IMG_ARC_PIE);
    imagefilledarc($image, $x3, $y3, $w, $w2, $afree, 360, $green, IMG_ARC_PIE);
    // flush image
    header('Content-type: image/png');
    imagepng($image);
    imagedestroy($image);
    exit;
}
