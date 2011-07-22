<?php
/**
 * Return Help Files
 *
 * @author Anakeen 2000 
 * @version $Id: helpview.php,v 1.6 2004/10/05 10:26:12 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage CORE
 */
 /**
 */

include_once("Lib.Http.php");

function helpview(&$action) {
  
  $appname =  (GetHttpVars("appname"));
  $filename =  (GetHttpVars("filename"));
  
  if ($filename == "") $filename=strtoupper($appname).".pdf";

  $pdffile=$action->GetParam("CORE_PUBDIR")."/Docs/$filename";
  if (file_exists($pdffile)) {
    Http_DownloadFile($pdffile,"$filename","application/pdf");
  } else {
    $errtext=sprintf( _("file for %s not found."),$appname);
    $action->ExitError($errtext);
  }
}
?>