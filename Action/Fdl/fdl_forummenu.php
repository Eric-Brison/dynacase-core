<?php
/**
 * Context menu view in folder list for a document
 *
 * @author Anakeen 2006
 * @version $Id: fdl_forummenu.php,v 1.1 2007/10/16 14:47:42 marc Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage 
 */
 /**
 */


include_once("FDL/popupdoc.php");
include_once("FDL/popupdocdetail.php");
// -----------------------------------
function fdl_forummenu(&$action) {
  // -----------------------------------
  // define accessibility
  $docid = GetHttpVars("id");
  $abstract = (GetHttpVars("abstract",'N') == "Y");
  $zone = GetHttpVars("zone"); // special zone

  $dbaccess = $action->GetParam("FREEDOM_DB");
  $doc = new_Doc($dbaccess, $docid);


  $tsubmenu=array();
  // -------------------- Menu menu ------------------

  $surl=$action->getParam("CORE_STANDURL");

  $tlink=array( "headers"=>array("descr"=>_("Properties"),
                                "url"=>"$surl&app=FDL&action=IMPCARD&zone=FDL:VIEWPROPERTIES:T&id=$docid",
                                "confirm"=>"false",
                                "control"=>"false",
                                "tconfirm"=>"",
                                "target"=>"headers",
                                "visibility"=>POPUP_ACTIVE,
                                "submenu"=>"",
                                "barmenu"=>"false"),
		"openforum"=>array( "descr"=>_("open forum"),
				    "url"=>"$surl&app=FDL&action=FDL_FORUMOPEN&docid=$docid",
				    "confirm"=>"false",
				    "control"=>"false",
				    "tconfirm"=>"",
				    "target"=>"_self",
				    "visibility"=>POPUP_INVISIBLE,
				    "submenu"=>"",
				    "barmenu"=>"false"),
		"closeforum"=>array( "descr"=>_("close forum"),
				     "url"=>"$surl&app=FDL&action=FDL_FORUMCLOSE&docid=$docid",
				     "confirm"=>"false",
				     "control"=>"false",
				     "tconfirm"=>"",
				     "target"=>"_self",
				     "visibility"=>POPUP_INVISIBLE,
				     "submenu"=>"",
				     "barmenu"=>"false"),
		);


  //  addFamilyPopup($tlink,$doc);
  popupdoc($action,$tlink,$tsubmenu);
}


?>