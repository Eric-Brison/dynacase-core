<?php
/**
 * Edition of virtual document
 *
 * @author Anakeen 2000 
 * @version $Id: freedom_iedit.php,v 1.3 2005/03/04 17:15:51 eric Exp $
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
function freedom_iedit(&$action) {
  // -----------------------------------
  global $action;


  // Get All Parameters
  $xml = GetHttpVars("xml");
  SetHttpVar("xml",$xml);

  $famid = GetHttpVars("famid");
  $action->lay->Set("famid",$famid);

  $type_attr=GetHttpVars("type_attr");
  $action->lay->Set("type_attr",$type_attr);

  $attrid=GetHttpVars("attrid");
  $action->lay->Set("attrid",$attrid);

     

}
?>
