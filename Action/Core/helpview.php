<?php
/**
 * Return Help Files
 *
 * @author Anakeen 2000 
 * @version $Id: helpview.php,v 1.5 2004/08/24 13:37:34 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
 /**
 */

include_once("Lib.Http.php");

function helpview(&$action) {
  
  $appname = strtoupper (GetHttpVars("appname"));

  $pdffile=$action->GetParam("CORE_PUBDIR")."/Docs/$appname.pdf";
  if (file_exists($pdffile)) {
    Http_DownloadFile($pdffile,"$appname.pdf","application/pdf");
  } else {
    $errtext=sprintf( _("file for %s not found."),$appname);
    $action->ExitError($errtext);
  }
}
?>