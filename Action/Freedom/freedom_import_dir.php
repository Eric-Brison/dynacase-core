<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Import directory with document descriptions
 *
 * @author Anakeen
 * @version $Id: freedom_import_dir.php,v 1.5 2007/01/19 16:23:32 eric Exp $
 * @package FDL
 * @subpackage GED
 */
/**
 */

include_once ("FDL/import_tar.php");

function freedom_import_dir(Action & $action)
{
    
    $to = GetHttpVars("to");
    $filename = GetHttpVars("filename");
    
    $wsh = getWshCmd(true);
    
    global $_GET, $_POST;
    $targs = array_merge($_GET, $_POST);
    $args = "";
    foreach ($targs as $k => $v) {
        if ($k == "action" || $k == "app") {
            continue;
        }
        $args.= " " . escapeshellarg("--$k=$v");
    }
    
    $subject = sprintf(_("result of archive import  %s") , $filename);
    
    $cmd[] = sprintf("%s --userid=%s --app=FREEDOM --action=FREEDOM_ANA_TAR --htmlmode=Y %s | ( %s --userid=%s --api=fdl_sendmail --subject=%s --htmlmode=Y --file=stdin --to=%s )", $wsh, escapeshellarg($action->user->id) , $args, $wsh, escapeshellarg($action->user->id) , escapeshellarg($subject) , escapeshellarg($to));
    
    bgexec($cmd, $result, $err);
    
    if ($err == 0) $action->lay->set("text", sprintf(_("Import %s is in progress. When update will be finished an email to &lt;%s&gt; will be sended with result rapport") , $filename, $to));
    else $action->lay->set("text", sprintf(_("update of %s catalogue has failed,") , $filename));
}
