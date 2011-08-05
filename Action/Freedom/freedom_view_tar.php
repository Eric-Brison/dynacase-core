<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * View imported tar
 *
 * @author Anakeen 2004
 * @version $Id: freedom_view_tar.php,v 1.3 2006/06/14 16:24:31 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage GED
 */
/**
 */

include_once ("FREEDOM/freedom_import_tar.php");

function freedom_view_tar(&$action)
{
    
    global $_FILES;
    
    $analyze = GetHttpVars("analyze", "Y"); // just analyze
    $filename = GetHttpVars("filename"); // the select filename
    $dirid = GetHttpVars("dirid"); // directory to place imported doc
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
        $ttar[0]["selected"] = "selected";
        $selfile = $ttar[0]["filename"];
    }
    if ($selfile != "") {
        foreach ($ttar as $k => $v) {
            // need suppress slashes
            $ttar[$k]["filename"] = stripslashes($v["filename"]);
        }
        $action->lay->setBlockData("SELECTFILE", $ttar);
        
        $untardir = getTarExtractDir($action, $selfile);
        
        if (!is_dir($untardir)) {
            // need extract first
            $uploaddir = getTarUploadDir($action);
            $tar = $uploaddir . $selfile;
            $status = extractTar($tar, $untardir);
            if ($status == - 2) $action->AddWarningMsg(sprintf(_("cannot extract archive file %s.\nType %s not recognized as archive file") , $selfile, trim(shell_exec(sprintf("file -b %s", escapeshellarg($tar))))));
            else if ($status != 0) $action->AddWarningMsg(sprintf(_("cannot extract archive file %s") , $selfile));
        }
        
        $nbdoc = see_directory($action, $untardir, $tfile);
    }
    if ($nbdoc > 0) $action->lay->SetBlockData("ANALYZE", array(
        array(
            "zou"
        )
    ));
    
    $action->lay->Set("selfile", stripslashes($selfile));
    
    $action->lay->set("huge", ($action->Read("navigator", "") == "EXPLORER") ? "" : "huge");
    
    $action->lay->Set("dirid", $dirid);
    $action->lay->Set("nbdoc", "$nbdoc");
}

function see_directory(&$action, $ldir, &$tfile, $level = 0)
{
    
    if ($level == 0) $tfile = array();
    if (is_dir($ldir)) {
        $handle = opendir($ldir);
        while (false !== ($file = readdir($handle))) {
            
            if ($file[0] != ".") {
                $afile = "$ldir/$file";
                $sfile = $file;
                
                if (!isUTF8($sfile)) $sfile = utf8_encode($sfile);
                if (is_file($afile)) {
                    $tfile[] = array(
                        "name" => $sfile,
                        "level" => $level * 20,
                        "type" => "file"
                    );
                } else if (is_dir($afile)) {
                    $tfile[] = array(
                        "name" => $sfile,
                        "level" => $level * 20,
                        "type" => "dir"
                    );
                    see_directory($action, $afile, $tfile, $level + 1);
                }
            }
        }
        
        closedir($handle);
    }
    if ($level == 0) {
        $action->lay->setBlockData("DIR", $tfile);
        return count($tfile);
    }
}
?>
