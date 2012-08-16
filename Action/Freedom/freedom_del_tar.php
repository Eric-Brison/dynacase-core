<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Delete imported tar
 *
 * @author Anakeen
 * @version $Id: freedom_del_tar.php,v 1.1 2004/03/16 15:37:09 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage GED
 */
/**
 */

include_once ("FREEDOM/freedom_import_tar.php");

function freedom_del_tar(&$action)
{
    
    global $_FILES;
    
    $filename = GetHttpVars("filename"); // the select filename
    $ldir = getTarUploadDir($action);
    if ($handle = opendir($ldir)) {
        while (false !== ($file = readdir($handle))) {
            if ($file[0] != ".") {
                $ttar[] = array(
                    "filename" => $file,
                    "selected" => ($file == $filename) ? "selected" : ""
                );
                if ($file == $filename) {
                    $selfile = $file;
                }
            }
        }
    }
    if ($selfile == "") {
        // try the first
        $action->exitError(sprintf(_("archive %s not found and cannot be removed") , $filename));
    }
    
    $untardir = getTarExtractDir($action, $selfile);
    
    if (is_dir($untardir)) {
        // suppress directory
        exec("/bin/rm -fr " . escapeshellarg($untardir) , $msg, $status);
        
        if ($status != 0) $action->exitError(sprintf(_("cannot suppress extract directory for archive file %s") , $selfile));
    }
    $tar = $ldir . $selfile;
    
    if (is_file($tar)) if (!unlink($tar)) $action->exitError(sprintf(_("cannot suppress archive file %s") , $selfile));
    
    $action->AddWarningMsg(sprintf(_("archive file %s has been deleted") , $selfile));
    
    redirect($action, "FREEDOM", "FREEDOM_VIEW_TAR", $action->GetParam("CORE_STANDURL"));
}
?>
