<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Importation in batch mode
 *
 * @author Anakeen
 * @version $Id: freedom_bgimport.php,v 1.11 2008/11/12 13:24:01 eric Exp $
 * @package FDL
 * @subpackage GED
 */
/**
 */

include_once ("FDL/Class.Dir.php");
include_once ("FDL/sendmail.php");
// -----------------------------------
function freedom_bgimport(Action & $action)
{
    // -----------------------------------
    global $_FILES;
    // Get all the params
    $dirid = GetHttpVars("dirid");
    $policy = GetHttpVars("policy", "keep");
    $analyze = (GetHttpVars("analyze", "N") == "Y");
    $double = GetHttpVars("double"); // with double title document
    $to = GetHttpVars("to");
    
    if (isset($_FILES["file"])) {
        // importation
        $file = $_FILES["file"]["tmp_name"];
        $filename = $_FILES["file"]["name"];
    } else {
        error_log(sprintf("No file has been uploaded!"));
        return;
    }
    
    $wsh = getWshCmd(true);
    $destfile = dirname($file) . "/__" . uniqid() . "__" . preg_replace('/[^a-zA-Z0-9_.-]/', '_', $filename);
    if (!move_uploaded_file($file, $destfile)) {
        error_log(sprintf("Error moving uploaded file '%s' to '%s'.", $file, $destfile));
        $action->lay->set("text", sprintf(_("update of %s catalogue has failed,") , $filename));
        return;
    }
    
    $cmd[] = sprintf("%s --userid=%s --api=importDocuments --htmlmode=yes --dir=%s --policy=%s --to=%s --file=%s", $wsh, escapeshellarg($action->user->id) , escapeshellarg($dirid) , escapeshellarg($policy) , escapeshellarg($to) , escapeshellarg($destfile));
    $cmd[] = sprintf("rm -- %s", escapeshellarg($destfile));
    // $cmd[]="/bin/rm -f $file.?";
    bgexec($cmd, $result, $err);
    
    if ($err == 0) $action->lay->set("text", sprintf(_("Import %s is in progress. When update will be finished an email to &lt;%s&gt; will be sended with result rapport") , $filename, $to));
    else $action->lay->set("text", sprintf(_("update of %s catalogue has failed,") , $filename));
}
