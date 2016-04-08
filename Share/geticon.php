<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Retrieve icon file
 *
 * @author Anakeen
 * @version $Id: geticon.php,v 1.6 2006/08/01 15:31:43 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("../WHAT/Lib.Prefix.php");
include_once ("Lib.Common.php");
include_once ("Lib.Http.php");

include_once ("FDL/exportfile.php");

$vaultid = GetHttpVars("vaultid", 0);
$mimetype = GetHttpVars("mimetype", "image");

$dbaccess = getDbAccess();

$vf = newFreeVaultFile($dbaccess);

$info = new VaultFileInfo();
if ($vf->Retrieve($vaultid, $info) != "") {
} else {
    //Header("Location: $url");
    if (($info->public_access)) {
        Http_DownloadFile($info->path, $info->name, $mimetype, true);
    } else {
        Http_DownloadFile("Images/doc.png", "unknow", "image/png", true);
    }
}
