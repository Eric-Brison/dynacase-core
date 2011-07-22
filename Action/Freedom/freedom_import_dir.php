<?php
/**
 * Import directory with document descriptions
 *
 * @author Anakeen 2000 
 * @version $Id: freedom_import_dir.php,v 1.5 2007/01/19 16:23:32 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage GED
 */
 /**
 */


include_once("FDL/import_tar.php");






function freedom_import_dir(&$action) {

  $to = GetHttpVars("to"); 
  $filename = GetHttpVars("filename"); 
  
 
  $wsh=getWshCmd(true);

  global $_GET,$_POST;
  $targs=array_merge($_GET,$_POST);
  $args="";
  foreach ($targs  as $k=>$v) {
    if (($k != "action") && ($k != "app"))
      $args .= " --$k=\"$v\"";
  }
 

  $subject=sprintf(_("result of archive import  %s"), $filename);

  $cmd[] = "$wsh --userid={$action->user->id} --app=FREEDOM --action=FREEDOM_ANA_TAR --htmlmode=Y $args | ( $wsh --userid={$action->user->id} --api=fdl_sendmail --subject=\"$subject\" --htmlmode=Y --file=stdin --to=\"$to\" )";

  


  bgexec($cmd, $result, $err);  
 


  if ($err == 0) 
    $action->lay->set("text", sprintf(_("Import %s is in progress. When update will be finished an email to &lt;%s&gt; will be sended with result rapport"), $filename , $to));
  else
    $action->lay->set("text", sprintf(_("update of %s catalogue has failed,"), $filename ));
		      

  
}




?>
