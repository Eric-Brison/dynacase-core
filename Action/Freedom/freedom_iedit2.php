<?php
/**
 * Edition of virtual document
 *
 * @author Anakeen 2000 
 * @version $Id: freedom_iedit2.php,v 1.6 2005/03/08 17:53:56 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage GED
 */
 /**
 */

include_once("FDL/Class.Doc.php");
include_once("FDL/Class.WDoc.php");
include_once("Class.QueryDb.php");
include_once("FDL/freedom_util.php");
include_once("FDL/Lib.Dir.php");
include_once("VAULT/Class.VaultFile.php");


// -----------------------------------
function freedom_iedit2(&$action) {
  // -----------------------------------
  global $action;


  // Get All Parameters
  $xml = GetHttpVars("xml");
 
  $famid = GetHttpVars("famid");
  //printf($famid);
  $type_attr=GetHttpVars("type_attr");
  $action->lay->Set("type_attr",$type_attr);

  $mod=GetHttpVars("mod");
  $action->lay->Set("mod",$mod);


  $attrid=GetHttpVars("attrid");
  $action->lay->Set("attrid",$attrid);

  $action->lay->Set("xml_initial",$xml);

	


 
  $famid = GetHttpVars("famid");
 
  $dbaccess = $action->GetParam("FREEDOM_DB");
 



  $idoc=fromxml($dbaccess,$xml,$famid,true);
  SetHttpVar("id",$idoc->id);
  $idoc->SetTitle($idoc->title);

  $action->lay->Set("docid",$idoc->id);
  $action->lay->Set("TITLE",$idoc->title);
  $action->lay->Set("STITLE",addslashes($idoc->title));
  $action->lay->Set("iconsrc", $idoc->geticon()); 
  $action->lay->Set("famid", $famid);
  $action->lay->Set("id", $idoc->id);

  // $xml_initial=addslashes(htmlentities($xml));


    

}
?>
