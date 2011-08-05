<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 * Importation in batch mode
 *
 * @author Anakeen 2000
 * @version $Id: freedom_bgimport.php,v 1.11 2008/11/12 13:24:01 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage GED
 */
/**
 */

include_once ("FDL/Class.Dir.php");
include_once ("FDL/sendmail.php");
// -----------------------------------
function freedom_bgimport(&$action)
{
    // -----------------------------------
    global $_FILES;
    // Get all the params
    $dirid = GetHttpVars("dirid");
    $dbaccess = $action->GetParam("FREEDOM_DB");
    $policy = GetHttpVars("policy", "keep");
    $analyze = (GetHttpVars("analyze", "N") == "Y");
    $double = GetHttpVars("double"); // with double title document
    $to = GetHttpVars("to");
    
    if (isset($_FILES["file"])) {
        // importation
        $file = $_FILES["file"]["tmp_name"];
        $filename = $_FILES["file"]["name"];
    }
    
    $wsh = getWshCmd(true);
    $destfile = dirname($file) . "/__" . uniqid() . "__" . preg_replace('/[^a-zA-Z0-9_.-]/', '_', $filename);
    if (!move_uploaded_file($file, $destfile)) {
        error_log(sprintf("Error moving uploaded file '%s' to '%s'.", $file, $destfile));
        $action->lay->set("text", sprintf(_("update of %s catalogue has failed,") , $filename));
        return;
    }
    
    $cmd[] = "$wsh --userid={$action->user->id} --api=freedom_import --htmlmode=Y --dirid=$dirid --double=$double --policy=$policy --to=$to --file=$destfile";
    $cmd[] = "/bin/rm $destfile ";
    // $cmd[]="/bin/rm -f $file.?";
    
    bgexec($cmd, $result, $err);
    
    if ($err == 0) $action->lay->set("text", sprintf(_("Import %s is in progress. When update will be finished an email to &lt;%s&gt; will be sended with result rapport") , $filename, $to));
    else $action->lay->set("text", sprintf(_("update of %s catalogue has failed,") , $filename));
}
?>
