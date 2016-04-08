<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Change icon of a document
 *
 * @author Anakeen
 * @version $Id: changeicon.php,v 1.8 2006/11/16 16:41:19 eric Exp $
 * @package FDL
 * @subpackage GED
 */
/**
 */

include_once ("FDL/Class.Doc.php");
include_once ("FDL/Class.DocAttr.php");
include_once ("FREEDOM/freedom_mod.php");
include_once ("VAULT/Class.VaultFile.php");

function changeicon(Action & $action)
{
    global $_FILES;
    
    $dbaccess = $action->dbaccess;
    $docid = GetHttpVars("id", 0);
    
    $doc = new_Doc($dbaccess, $docid);
    $err = $doc->canEdit();
    if ($err != "") $action->AddWarningMsg($err);
    else {
        //print_r($_FILES);
        $fileinfo = $_FILES["ifile"];
        if (is_array($fileinfo)) {
            // if no file specified, keep current file
            if (($fileinfo['tmp_name'] == "none") || ($fileinfo['tmp_name'] == "") || ($fileinfo['size'] == 0)) {
                $vid = getHttpVars("vid");
                
                $fileVaultInfo = \Dcp\VaultManager::getFileInfo($vid);
                
                if ($fileVaultInfo) {
                    if ($fileVaultInfo->public_access !== "t") {
                        $action->exitError("File cannot be used as icon");
                    }
                    $doc->changeIcon(sprintf("%s|%s|%s", $fileVaultInfo->mime_s, $fileVaultInfo->id_file, $fileVaultInfo->name));
                } else {
                    $action->addWarningMsg(_("no file specified : change icon aborted"));
                }
            } else {
                if (!is_uploaded_file($fileinfo['tmp_name'])) $action->ExitError(_("file not expected : possible attack : update aborted"));
                
                $imageSize = getimagesize($fileinfo['tmp_name']);
                if (!$imageSize) {
                    $action->exitError("File is not recognized like an image");
                }
                
                $vid = \Dcp\VaultManager::storeFile($fileinfo['tmp_name'], $fileinfo['name'], true);
                $fileVaultInfo = \Dcp\VaultManager::getFileInfo($vid);
                
                $doc->changeIcon(sprintf("%s|%s|%s", $fileVaultInfo->mime_s, $fileVaultInfo->id_file, $fileVaultInfo->name));
            }
        }
    }
    
    redirect($action, "FDL", "FDL_CARD&sole=Y&id=" . $doc->id);
}
