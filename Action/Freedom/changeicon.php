<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 * Change icon of a document
 *
 * @author Anakeen 2000
 * @version $Id: changeicon.php,v 1.8 2006/11/16 16:41:19 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage GED
 */
/**
 */

include_once ("FDL/Class.Doc.php");
include_once ("FDL/Class.DocAttr.php");
include_once ("FREEDOM/freedom_mod.php");
include_once ("VAULT/Class.VaultFile.php");

function changeicon(&$action)
{
    global $_FILES;
    
    $destdir = "./" . GetHttpVars("app") . "/Upload/";
    
    $dbaccess = $action->GetParam("FREEDOM_DB");
    $docid = GetHttpVars("id", 0);
    
    $action->lay->Set("docid", $docid);
    
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
                if ($vid > 0) {
                    $doc->ChangeIcon("image|" . $vid);
                } else {
                    $action->addWarningMsg(_("no file specified : change icon aborted"));
                }
            } else {
                if (!is_uploaded_file($fileinfo['tmp_name'])) $action->ExitError(_("file not expected : possible attack : update aborted"));
                
                preg_match("/(.*)\.(.*)$/", $fileinfo['name'], $reg);
                $ext = $reg[2];
                // move to add extension
                $destfile = str_replace(" ", "_", getTmpDir() . "/" . $fileinfo['name']);
                move_uploaded_file($fileinfo['tmp_name'], $destfile);
                
                $vf = newFreeVaultFile($dbaccess);
                
                $err = $vf->Store($destfile, true, $vid);
                if ($err != "") $action->ExitError($err);
                
                $doc->ChangeIcon($fileinfo['type'] . "|" . $vid);
                
                unlink($destfile);
            }
        }
    }
    
    redirect($action, "FDL", "FDL_CARD&sole=Y&id=" . $doc->id);
}
?>
