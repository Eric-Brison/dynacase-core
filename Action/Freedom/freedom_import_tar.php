<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Import document descriptions
 *
 * @author Anakeen
 * @version $Id: freedom_import_tar.php,v 1.3 2006/01/16 16:11:39 eric Exp $
 * @package FDL
 * @subpackage GED
 */
/**
 */

include_once ("FDL/import_tar.php");

function freedom_import_tar(Action & $action)
{
    
    global $_FILES;
    
    $dirid = GetHttpVars("dirid"); // directory to place imported doc
    $famid = GetHttpVars("famid"); // default import family
    $onlycsv = (GetHttpVars("onlycsv") != ""); // only files described in fdl.csv files
    $analyze = (GetHttpVars("analyze", "N") == "Y"); // just analyze
    $uploaddir = getTarUploadDir($action);
    
    $err = '';
    $fname = '';
    $report = '';
    $extract = '';
    if ($_FILES['tar']['error'] != UPLOAD_ERR_OK) {
        switch ($_FILES['tar']['error']) {
            case UPLOAD_ERR_INI_SIZE:
                $err = sprintf("The uploaded file exceeds the upload_max_filesize [%s bytes] directive in php.ini", ini_get('upload_max_filesize'));
                break;

            case UPLOAD_ERR_FORM_SIZE:
                $err = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.";
                break;

            case UPLOAD_ERR_PARTIAL:
                $err = "The uploaded file was only partially uploaded.";
                break;

            case UPLOAD_ERR_NO_FILE:
                $err = "No file was uploaded.";
                break;
        }
        if ($err != "") $action->exitError($err);
    } else {
        
        system("mkdir -p " . escapeshellarg($uploaddir));
        $fname = get_magic_quotes_gpc() ? stripslashes($_FILES['tar']['name']) : $_FILES['tar']['name'];
        $uploadfile = $uploaddir . $fname;
        if (move_uploaded_file($_FILES['tar']['tmp_name'], "$uploadfile")) {
            $report = sprintf(_("File %s is valid, and was successfully uploaded.") , $fname);
            
            $untardir = getTarExtractDir($action, $fname);
            
            $status = extractTar($uploadfile, $untardir, $_FILES['tar']['type']);
            if ($status === '') {
                $extract = sprintf(_("The file %s has been correctly extracted") , $fname);
            } else {
                $extract = sprintf(_("The file %s cannot be extracted: %s") , $fname, $status);
            }
        } else {
            $report = _("Possible file upload attack!  Here's some debugging info:\n");
            print_r2($_FILES);
        }
    }
    
    $action->lay->eset("filename", $fname);
    $action->lay->eset("report", $report);
    $action->lay->eset("extract", $extract);
    $action->lay->eset("dirid", $dirid);
}

function extractTar($tar, $untardir, $mime = "")
{
    $tar = realpath($tar);
    $mime = trim(shell_exec(sprintf("file -b %s", escapeshellarg($tar))));
    $mime = substr($mime, 0, strpos($mime, " "));
    
    $err = '';
    try {
        switch ($mime) {
            case "gzip":
            case "application/x-compressed-tar":
            case "application/x-gzip":
                exec(sprintf("rm -rf %s 2>&1", escapeshellarg($untardir)) , $output, $status);
                if ($status !== 0) {
                    throw new Exception(sprintf(_("Error deleting directory '%s': %s") , $untardir, join("\n", $output)));
                }
                exec(sprintf("mkdir -p %s 2>&1", escapeshellarg($untardir)) , $output, $status);
                if ($status !== 0) {
                    throw new Exception(sprintf(_("Error creating directory '%s': %s") , $untardir, join("\n", $output)));
                }
                exec(sprintf("tar -C %s -zxf %s 2>&1", escapeshellarg($untardir) , escapeshellarg($tar)) , $output, $status);
                if ($status !== 0) {
                    throw new Exception(sprintf(_("Error extracting archive '%s' in '%s': %s") , $tar, $untardir, join("\n", $output)));
                }
                break;

            case "bzip2":
                exec(sprintf("rm -rf %s 2>&1", escapeshellarg($untardir)) , $output, $status);
                if ($status !== 0) {
                    throw new Exception(sprintf(_("Error deleting directory '%s': %s") , $untardir, join("\n", $output)));
                }
                exec(sprintf("mkdir -p %s 2>&1", escapeshellarg($untardir)) , $output, $status);
                if ($status !== 0) {
                    throw new Exception(sprintf(_("Error creating directory '%s': %s") , $untardir, join("\n", $output)));
                }
                exec(sprintf("tar -C %s -jxf %s 2>&1", escapeshellarg($untardir) , escapeshellarg($tar)) , $output, $status);
                if ($status !== 0) {
                    throw new Exception(sprintf(_("Error extracting archive '%s' in '%s': %s") , $tar, $untardir, join("\n", $output)));
                }
                break;

            case "Zip":
            case "application/x-zip-compressed":
            case "application/x-zip":
                exec(sprintf("rm -rf %s 2>&1", escapeshellarg($untardir)) , $output, $status);
                if ($status !== 0) {
                    throw new Exception(sprintf(_("Error deleting directory '%s': %s") , $untardir, join("\n", $output)));
                }
                exec(sprintf("mkdir -p %s 2>&1", escapeshellarg($untardir)) , $output, $status);
                if ($status !== 0) {
                    throw new Exception(sprintf(_("Error creating directory '%s': %s") , $untardir, join("\n", $output)));
                }
                exec(sprintf("unzip -d %s %s 2>&1", escapeshellarg($untardir) , escapeshellarg($tar)) , $output, $status);
                if ($status !== 0) {
                    throw new Exception(sprintf(_("Error extracting archive '%s' in '%s': %s") , $tar, $untardir, join("\n", $output)));
                }
                break;

            default:
                throw new Exception(sprintf(_("Unsupported archive format '%s' for archive '%s'.") , $mime, $tar));
        }
    }
    catch(Exception $e) {
        $err = $e->getMessage();
    }
    return $err;
}
