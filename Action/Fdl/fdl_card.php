<?php
/**
 * View Document
 *
 * @author Anakeen 2000 
 * @version $Id: fdl_card.php,v 1.42 2008/12/02 15:20:52 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



include_once("FDL/Class.Dir.php");


/**
 * View a document
 * @param Action &$action current action
 * @global id Http var : document identificator to see
 * @global latest Http var : (Y|N|L|P) if Y force view latest revision, L : latest fixed revision, P : previous revision
 * @global state Http var : to view document in latest fixed state (only if revision > 0) 
 * @global abstract Http var : (Y|N) if Y view only abstract attribute
 * @global props Http var : (Y|N) if Y view properties also
 * @global zonebodycard Http var : if set, view other specific representation
 * @global vid Http var : if set, view represention describe in view control (can be use only if doc has controlled view)
 * @global ulink Http var : (Y|N)if N hyperlink are disabled
 * @global target Http var : is set target of hyperlink can change (default _self)
 * @global inline Http var : (Y|N) set to Y for binary template. View in navigator
 * @global reload Http var : (Y|N) if Y update freedom folders in client navigator
 * @global dochead Http var :  (Y|N) if N don't see head of document (not title and icon)
 */
function fdl_card(&$action) {
  // -----------------------------------
  
  $docid = GetHttpVars("id");
  $latest = GetHttpVars("latest");
  $zone = GetHttpVars("zone");
  $ulink = (GetHttpVars("ulink",'2')); // add url link
  $target = GetHttpVars("target"); // may be mail
  $vid = GetHttpVars("vid"); // special controlled view
  $state = GetHttpVars("state"); // search doc in this state
  $inline=getHttpVars("inline"=="Y"); // view file inline
  $dbaccess = $action->GetParam("FREEDOM_DB");

  if ($docid=="") $action->exitError(_("no document reference"));
  if (! is_numeric($docid)) $docid=getIdFromName($dbaccess,$docid);
  if (intval($docid) == 0) $action->exitError(sprintf(_("unknow logical reference '%s'"),GetHttpVars("id")));
  $doc = new_Doc($dbaccess, $docid);
  if (! $doc->isAffected()) {
      $err=simpleQuery($dbaccess,sprintf("select id from dochisto where id=%d limit 1",$docid),$hashisto,true,true);
      if ($hashisto)  $action->exitError(sprintf(_("Document %s has been destroyed."),$docid).sprintf(" <a href='?app=FDL&action=VIEWDESTROYDOC&id=%s>%s</a>",$docid,_("See latest information about it.")));
      else $action->exitError(sprintf(_("cannot see unknow reference %s"),$docid));
  }


  if ($state != "") {
    $docid=$doc->getRevisionState($state,true);
    if ($docid==0) {
      $action->exitError(sprintf(_("Document %s in %s state not found"),
				 $doc->title,_($state)));
    }
    SetHttpVar("id",$docid);
  } else {
    if (($latest == "Y") && ($doc->locked == -1)) {
      // get latest revision
      $docid=$doc->latestId();
      if ($docid=="") $action->exitError(_("no alive document reference"));
      SetHttpVar("id",$docid);
    } else if (($latest == "L") && ($doc->lmodify != 'L')) {
      // get latest fixed revision
      $docid=$doc->latestId(true);
      SetHttpVar("id",$docid);
    } else if (($latest == "P") && ($doc->revision > 0)) {
      // get previous fixed revision
      $pdoc = getRevTDoc($dbaccess, $doc->initid,$doc->revision-1);
      $docid=$pdoc["id"];
      SetHttpVar("id",$docid);
    }
  }
  
  if ($docid != $doc->id) $doc=new_doc($dbaccess,$docid);

  SetHttpVar("viewbarmenu",1);    

  $action->lay->set("RSS", ($doc->getValue("gui_isrss")));
  $action->lay->set("rsslink", $doc->getRssLink());
  $action->lay->Set("TITLE",$doc->getHtmlTitle());
  $action->lay->Set("id",$docid);
  if ($action->read("navigator")=="EXPLORER")  $action->lay->Set("shorticon",getParam("DYNACASE_FAVICO"));
  else $action->lay->Set("shorticon",$doc->getIcon());
  $action->lay->Set("pds",$doc->urlWhatEncodeSpec(""));
  
  $action->lay->Set("forum", false);
  if (($doc->doctype=='F' || $doc->doctype=='D' ) &&  abs(intval($doc->forumid))>0){
    $action->lay->Set("forumid",abs($doc->forumid));
    $action->lay->Set("forum",($doc->forumid!="" ? true : false ));
  }    
  
  if (($zone=="") && ($vid != "")) {
      $cvdoc= new_Doc($dbaccess, $doc->cvid);
      if ($cvdoc->fromid==28) {
          $cvdoc->set($doc);

          $err = $cvdoc->control(trim($vid)); // control special view
          if ($err != "") $action->exitError("CV:".$cvdoc->title."\n".$err);
          $tview = $cvdoc->getView($vid);
          $zone=$tview["CV_ZVIEW"];
      }
  }
  if ($zone == "") $zone=$doc->defaultview;
  $zo=$doc->getZoneOption($zone);
  if ($zo=="Sxxxxxxxxx") { // waiting for special zone contradiction
    $action->lay = new Layout(getLayoutFile("FDL","viewscard.xml"),$action);
    $action->lay->set("ZONESCARD",$doc->viewdoc($zone,$target,$ulink));
  } else {
    $engine=$doc->getZoneTransform($zone);
      if ($engine) {     
	$sgets=fdl_getGetVars();
	redirect($action,"FDL",
		 "GETFILETRANSFORMATION&idv=$vid&zone=$zone$sgets&id=".$doc->id,
		 $action->GetParam("CORE_STANDURL"));
	exit;
      } 
      if ($zo=="B") {
	// binary layout file
	
	if ($tview["CV_MSKID"]) $doc->setMask($tview["CV_MSKID"]);
	$file=$doc->viewdoc($zone,$target,$ulink);
	if ( (! file_exists($file))) { // error in  file generation
	  $action->lay->template=$file;
	  return;
	}

	$ext=getFileExtension($file);
	if ($ext=='')  $ext="html";
	$mime=getSysMimeFile($file,basename($file));

	//	print "$file,".$doc->title.".$ext $mime"; exit;
	Http_DownloadFile($file,$doc->title.".$ext",$mime,$inline,false);
	@unlink($file);
	exit;
      } else {
      $action->lay->set("nocss",($zo=="U"));
      $taction=array();
      if ($doc->doctype!='C') {
	$listattr = $doc->GetActionAttributes();
	$mwidth=$action->getParam("FDL_HD2SIZE",300);
	$mheight=$action->getParam("FDL_VD2SIZE",400);
	foreach ($listattr as $k => $v) {
	  if (($v->mvisibility != "H")&&($v->mvisibility != "O")) {
	    if ($v->getOption("onlymenu")!="yes") {
	      $mvis=MENU_ACTIVE;
	      if ($v->precond != "") $mvis=$doc->ApplyMethod($v->precond,MENU_ACTIVE);
	      if ($mvis == MENU_ACTIVE) {
		$taction[$k]=array("wadesc"=>$v->getOption("llabel"),
				   "walabel"=>ucfirst($v->getLabel()),
				   "wwidth"=>$v->getOption("mwidth",$mwidth),
				   "wheight"=>$v->getOption("mheight",$mheight),
				   "wtarget"=>($v->getOption("ltarget")=="")?$v->id."_".$doc->id:$v->getOption("ltarget"),
				   "wlink"=>$doc->urlWhatEncode($v->getLink($doc->latestId())));
	      }
	    }
	
	  }
	}
      }
      $action->lay->setBlockData("WACTION",$taction);
      $action->lay->set("VALTERN",($action->GetParam("FDL_VIEWALTERN","yes")=="yes"));
    }
  }
}

function fdl_getGetVars() {
  global $_GET;

  $exclude=array("app","action","sole","id","zone");
  $s='';
  foreach ($_GET as $k=>$v) {
    if (! in_array($k,$exclude)) $s.="&$k=$v";    
  }

  return $s;
  
}
?>
