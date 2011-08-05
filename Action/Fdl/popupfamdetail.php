<?php
/**
 * Specific menu for family
 *
 * @author Anakeen 2000 
 * @version $Id: popupfamdetail.php,v 1.10 2008/11/27 14:18:33 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage 
 */
 /**
 */


include_once("FDL/popupdoc.php");

function popupfamdetail(&$action) {
  $docid = GetHttpVars("id");
  if ($docid == "") $action->exitError(_("No identificator"));
  $popup=getpopupfamdetail($action,$docid);

  
  popupdoc($action,$popup);
  
}



function getpopupfamdetail(&$action,$docid) {

  

  $dbaccess = $action->GetParam("FREEDOM_DB");
  $doc = new_Doc($dbaccess, $docid);

  if ($doc->isAffected()) $docid=$doc->id;



  $tsubmenu=array();

  // -------------------- Menu menu ------------------

  $surl=$action->getParam("CORE_STANDURL");

  $tlink=array("headers"=>array("descr"=>_("Properties"),
				"url"=>"$surl&app=FDL&action=IMPCARD&zone=FDL:VIEWPROPERTIES:T&id=$docid",
				"confirm"=>"false",
				"control"=>"false",
				"tconfirm"=>"",
				"target"=>"headers",
				"visibility"=>POPUP_CTRLACTIVE,
				"submenu"=>"",
				"barmenu"=>"false"),
	       


	       "create"=>array( "descr"=>sprintf(_("Create %s"),$doc->gettitle()),
				  "url"=>"$surl&app=GENERIC&action=GENERIC_EDIT&classid=$docid",
				  "confirm"=>"false",
				  "control"=>"false",
				  "tconfirm"=>"",
				  "target"=>"",
				  "visibility"=>POPUP_ACTIVE,
				  "submenu"=>"",
				  "barmenu"=>"false"),

	       "chicon"=>array( "descr"=>_("Change icon"),
				  "url"=>"$surl&app=FREEDOM&action=QUERYFILE&id=$docid",
				  "confirm"=>"false",
				  "control"=>"false",
				  "tconfirm"=>"",
				  "target"=>"_self",
				  "visibility"=>POPUP_ACTIVE,
				  "submenu"=>"",
				  "barmenu"=>"false"),

	       "editattr"=>array( "descr"=>_("Edit attributes"),
				  "url"=>"$surl&app=FREEDOM&action=DEFATTR&id=$docid",
				  "confirm"=>"false",
				  "control"=>"false",
				  "tconfirm"=>"",
				  "target"=>"",
				  "visibility"=>POPUP_ACTIVE,
				  "submenu"=>"",
				  "barmenu"=>"false"),
	       "editenum"=>array( "descr"=>_("Edit enum attributes"),
				  "url"=>"$surl&app=GENERIC&action=GENERIC_EDITFAMCATG&famid=$docid",
				  "confirm"=>"false",
				  "control"=>"false",
				  "tconfirm"=>"",
				  "target"=>"",
				  "visibility"=>POPUP_ACTIVE,
				  "submenu"=>"",
				  "barmenu"=>"false"),
	       "defval"=>array( "descr"=>_("Set default values"),
				  "url"=>"$surl&app=GENERIC&action=GENERIC_EDIT&usefor=D&classid=$docid",
				  "confirm"=>"false",
				  "control"=>"false",
				  "tconfirm"=>"",
				  "target"=>"",
				  "visibility"=>POPUP_ACTIVE,
				  "submenu"=>"",
				  "barmenu"=>"false"),
	       "param"=>array( "descr"=>_("Parameters values"),
				  "url"=>"$surl&app=GENERIC&action=GENERIC_EDIT&usefor=Q&classid=$docid",
				  "confirm"=>"false",
				  "control"=>"false",
				  "tconfirm"=>"",
				  "target"=>"",
				  "visibility"=>POPUP_ACTIVE,
				  "submenu"=>"",
				  "barmenu"=>"false"),
	       "chgtitle"=>array( "descr"=>_("Rename"),
				  "url"=>"$surl&app=FREEDOM&action=QUERYTITLE&id=$docid",
				  "confirm"=>"false",
				  "control"=>"false",
				  "tconfirm"=>"",
				  "target"=>"_self",
				  "visibility"=>POPUP_ACTIVE,
				  "submenu"=>"",
				  "barmenu"=>"false"),
	       "editprof"=>array( "descr"=>_("Change profile of family document"),
				  "url"=>"$surl&app=FREEDOM&action=EDITPROF&id=$docid",
				  "confirm"=>"false",
				  "control"=>"false",
				  "tconfirm"=>"",
				  "target"=>"",
				  "visibility"=>POPUP_ACTIVE,
				  "submenu"=>"security",
				  "barmenu"=>"false"),
	       "editcprof"=>array( "descr"=>_("Change profile for new documents"),
				  "url"=>"$surl&app=FREEDOM&action=EDITPROF&create=1&id=$docid",
				  "confirm"=>"false",
				  "control"=>"false",
				  "tconfirm"=>"",
				  "target"=>"_self",
				  "visibility"=>POPUP_ACTIVE,
				  "submenu"=>"security",
				  "barmenu"=>"false"),
	       "editdfld"=>array( "descr"=>_("Change root folder"),
				  "url"=>"$surl&app=FREEDOM&action=EDITDFLD&id=$docid",
				  "confirm"=>"false",
				  "control"=>"false",
				  "tconfirm"=>"",
				  "target"=>"_self",
				  "visibility"=>POPUP_ACTIVE,
				  "submenu"=>"",
				  "barmenu"=>"false"),
	       "editcfld"=>array( "descr"=>_("Change default search"),
				  "url"=>"$surl&app=FREEDOM&action=EDITDFLD&current=Y&id=$docid",
				  "confirm"=>"false",
				  "control"=>"false",
				  "tconfirm"=>"",
				  "target"=>"_self",
				  "visibility"=>POPUP_INACTIVE,
				  "submenu"=>"",
				  "barmenu"=>"false"),
	       "editwdoc"=>array( "descr"=>_("Choose workflow"),
				  "url"=>"$surl&app=FREEDOM&action=EDITWDOC&current=Y&id=$docid",
				  "confirm"=>"false",
				  "control"=>"false",
				  "tconfirm"=>"",
				  "target"=>"_self",
				  "visibility"=>POPUP_ACTIVE,
				  "submenu"=>"",
				  "barmenu"=>"false"),

	       "histo"=>array( "descr"=>_("History"),
			       "url"=>"$surl&app=FREEDOM&action=HISTO&id=$docid",
			       "confirm"=>"false",
			       "control"=>"false",
			       "tconfirm"=>"",
			       "target"=>"",
			       "visibility"=>POPUP_CTRLACTIVE,
			       "submenu"=>"",
			       "barmenu"=>"false"),
	       "access"=>array( "descr"=>_("goaccess"),
				"url"=>"$surl&app=FREEDOM&action=FREEDOM_GACCESS&id=".$doc->profid,
				"confirm"=>"false",
				"control"=>"false",
				"tconfirm"=>"",
				"target"=>"",
				"mwidth"=>800,
				"mheight"=>300,
				"visibility"=>POPUP_ACTIVE,
				"submenu"=>"security",
				"barmenu"=>"false"),
	       "tobasket"=>array( "descr"=>_("Add to basket"),
				  "url"=>"$surl&app=FREEDOM&action=ADDDIRFILE&docid=$docid&dirid=".$action->getParam("FREEDOM_IDBASKET"),
				  "confirm"=>"false",
				  "control"=>"false",
				  "tconfirm"=>"",
				  "target"=>"",
				  "visibility"=>POPUP_CTRLACTIVE,
				  "submenu"=>"",
				  "barmenu"=>"false"),
	       "addpostit"=>array( "descr"=>_("Add postit"),
				   "jsfunction"=>"postit('$surl&app=GENERIC&action=GENERIC_EDIT&classid=27&pit_title=&pit_idadoc=$docid',50,50,300,200)",
				   "confirm"=>"false",
				   "control"=>"false",
				   "tconfirm"=>"",
				   "target"=>"",
				   "visibility"=>POPUP_CTRLACTIVE,
				   "submenu"=>"",
				   "barmenu"=>"false"),
	       "forumenabled"=>array( "descr"=>_("enable forum"),
				  "url"=>"$surl&app=FREEDOM&action=FORUM_SETDEFAULT&id=$docid&st=Y",
				   "confirm"=>"false",
				   "control"=>"false",
				   "tconfirm"=>"",
				   "target"=>"_self",
				   "visibility"=>POPUP_INACTIVE,
				   "submenu"=>"",
				   "barmenu"=>"false"),
	       "forumdisabled"=>array( "descr"=>_("disable forum"),
				  "url"=>"$surl&app=FREEDOM&action=FORUM_SETDEFAULT&id=$docid&st=N",
				   "confirm"=>"false",
				   "control"=>"false",
				   "tconfirm"=>"",
				   "target"=>"_self",
				   "visibility"=>POPUP_INACTIVE,
				   "submenu"=>"",
				       "barmenu"=>"false"),
	       "exportpref"=>array( "descr"=>_("export preferences"),
				    "url"=>"$surl&app=FREEDOM&action=EDITEXPORTCHOOSECOLS&id=$docid",
				   "confirm"=>"false",
				   "control"=>"false",
				   "tconfirm"=>"",
				   "target"=>"exportpref",
				   "visibility"=>POPUP_CTRLACTIVE,
				   "submenu"=>"",
				   "barmenu"=>"false"),
               "edithelp"=>array( "descr"=>_("create help"),
                                    "url"=>"$surl&app=GENERIC&action=GENERIC_EDIT&classid=HELPPAGE&help_family=$docid",
                                   "confirm"=>"false",
                                   "control"=>"false",
                                   "tconfirm"=>"",
                                   "target"=>"edithelp",
                                   "visibility"=>POPUP_ACTIVE,
                                   "submenu"=>"",
                                   "barmenu"=>"false")

	       );

  changeFamMenuVisibility($action,$tlink,$doc);
  
  return $tlink;


         
}
/**
 * Add control view menu
 */
function changeFamMenuVisibility(&$action,&$tlink,&$doc) {
   $clf = ($doc->CanEdit() == "");

  if (getParam("FREEDOM_IDBASKET") == 0)  $tlink["tobasket"]["visibility"]=POPUP_INVISIBLE;


  if ($doc->IsControlled() && ($doc->profid > 0) && ($doc->Control("viewacl") == "")) {
    $tlink["access"]["visibility"]=POPUP_ACTIVE;
  } else {
    $tlink["access"]["visibility"]=POPUP_INVISIBLE;
  }

  if ($doc->Control("modifyacl") == "") {
    $tlink["editprof"]["visibility"]=POPUP_ACTIVE;
  } else {
    $tlink["editprof"]["visibility"]=POPUP_INACTIVE;
  }

  $param=$doc->getParamAttributes();
  if (count($param)==0) $tlink["param"]["visibility"]=POPUP_INVISIBLE;


  if ($doc->locked == -1) { // fixed document
    if ($doc->doctype != 'Z') $tlink["latest"]["visibility"]=POPUP_ACTIVE; 
    $tlink["editdoc"]["visibility"]=POPUP_INVISIBLE;
    $tlink["delete"]["visibility"]=POPUP_INVISIBLE;
    $tlink["editprof"]["visibility"]=POPUP_INVISIBLE;
    $tlink["revise"]["visibility"]=POPUP_INVISIBLE;
    $tlink["lockdoc"]["visibility"]=POPUP_INVISIBLE;
  } 



  if ($doc->dfldid != "") {
    $tlink["editcfld"]["visibility"]=POPUP_ACTIVE;
  }



  if ($doc->postitid > 0) $tlink["addpostit"]["visibility"]=POPUP_INVISIBLE;
  else $tlink["addpostit"]["visibility"]=POPUP_CTRLACTIVE;

  if (! $clf) {

   
    // actions not available
    $tlink["defval"]["visibility"]=POPUP_INVISIBLE;
    $tlink["editattr"]["visibility"]=POPUP_INVISIBLE;
    $tlink["editenum"]["visibility"]=POPUP_INVISIBLE;
    $tlink["chicon"]["visibility"]=POPUP_INVISIBLE;
    $tlink["param"]["visibility"]=POPUP_INVISIBLE;
    $tlink["chgtitle"]["visibility"]=POPUP_INVISIBLE;
    $tlink["editprof"]["visibility"]=POPUP_INVISIBLE;
    $tlink["editcprof"]["visibility"]=POPUP_INVISIBLE;
    $tlink["editdfld"]["visibility"]=POPUP_INVISIBLE;
    $tlink["editcfld"]["visibility"]=POPUP_INVISIBLE;
    $tlink["editwdoc"]["visibility"]=POPUP_INVISIBLE;
  }
  if (! $action->parent->Haspermission("FREEDOM_READ","FREEDOM")) {
    $tlink["histo"]["visibility"]=POPUP_INVISIBLE;
  }

  if ($doc->forumid=="") {
    $tlink["forumdisabled"]["visibility"]=POPUP_INVISIBLE;
    $tlink["forumenabled"]["visibility"]=POPUP_CTRLACTIVE;
  } else {
    $tlink["forumdisabled"]["visibility"]=POPUP_CTRLACTIVE;
    $tlink["forumenabled"]["visibility"]=POPUP_INVISIBLE;
  }
  include_once("FDL/Class.SearchDoc.php");
  $s=new SearchDoc($doc->dbaccess,"HELPPAGE");
  $s->addFilter("help_family='%d'",$doc->id);
  $help=$s->search();
  if ($s->count() > 0) {
      $tlink["edithelp"]["descr"]=_("modify family help");
      $helpid=$help[0]["id"];
      $tlink["edithelp"]["url"]="?app=GENERIC&action=GENERIC_EDIT&id=$helpid";
               
            }
  
  
}

?>