<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: usercard_vcard.php,v 1.10 2005/11/23 14:03:50 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage USERCARD
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: usercard_vcard.php,v 1.10 2005/11/23 14:03:50 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Usercard/usercard_vcard.php,v $
// ---------------------------------------------------------------



include_once("FDL/Class.Doc.php");
include_once("FDL/Class.UsercardVcard.php");




// -----------------------------------
function usercard_vcard(&$action) {
  // -----------------------------------

  

  // Get all the params      
  $docid=GetHttpVars("id"); // dccument to export

  $dbaccess = $action->GetParam("FREEDOM_DB");

  $doc = new_Doc($dbaccess, $docid);
  
  $doc->GetValues();
  
  $vcard= new UsercardVcard();
  $export_file = uniqid(getTmpDir()."/export");


  $vcard->Open($export_file,"w");
  $vcard->WriteCard($doc->title, $doc->getvalues());
  $vcard->close();

 
  

  
  http_DownloadFile($export_file, chop($doc->title).".".$vcard->ext, $vcard->mime_type);

  
  unlink($export_file);
  exit;
}


?>