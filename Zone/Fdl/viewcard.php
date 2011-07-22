<?php
/**
 * View document zone
 *
 * @author Anakeen 2000 
 * @version $Id: viewcard.php,v 1.93 2009/01/04 18:36:37 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage 
 */
 /**
 */



include_once("FDL/Class.Doc.php");
include_once("FDL/Class.DocAttr.php");

include_once("Class.TableLayout.php");
include_once("Class.QueryDb.php");
include_once("Class.QueryGen.php");
include_once("FDL/freedom_util.php");
include_once("FDL/family_help.php");
include_once("VAULT/Class.VaultFile.php");

// -----------------------------------
// -----------------------------------
function viewcard(&$action) {
  // -----------------------------------


  // GetAllParameters
  $docid = GetHttpVars("id");
  $abstract = (GetHttpVars("abstract",'N') == "Y");// view doc abstract attributes
  $props = (GetHttpVars("props",'N') == "Y"); // view doc properties
  $zonebodycard = GetHttpVars("zone"); // define view action
  $vid = GetHttpVars("vid"); // special controlled view
  
  $ulink = (GetHttpVars("ulink",'2')); // add url link
  $target = GetHttpVars("target"); // may be mail
  $reload = ($action->read("reload$docid","N")=="Y"); // need reload

  if (($target != "mail")&&($target != "te")) $action->lay->set("MAILVIEW",false);
  else $action->lay->set("MAILVIEW",true);
  $action->lay->set("fhelp",($action->Read("navigator","")=="EXPLORER")?"_blank":"fhidden");
  $action->lay->set('verifyfiles',false);
  $action->lay->set('POSTIT',(GetHttpVars("postit",'Y') == "Y"));

  if ($ulink == "N") $ulink = false;
  else  if ($ulink == "Y") $ulink = 1;
  $action->lay->set("ulink",$ulink);

  // Set the globals elements

  $action->parent->AddJsRef(sprintf("%sapp=FDL&action=ALLVIEWJS&wv=%s", $action->getParam("CORE_SSTANDURL"), $action->getParam('WVERSION')));

  /*
 $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/WHAT/Layout/AnchorPosition.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/resizeimg.js");
 // $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FDL/Layout/idoc.js");
 $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FDL/Layout/common.js");
 $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FDC/Layout/setparamu.js");
 $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FDC/Layout/inserthtml.js");
 */

 //pour les idocs
 $jsfile=$action->GetLayoutFile("viewicard.js");
 $jslay = new Layout($jsfile,$action);
 $action->parent->AddJsCode($jslay->gen());

  $baseurl=$action->GetParam("CORE_BASEURL");
  $standurl=$action->GetParam("CORE_STANDURL");
  $dbaccess = $action->GetParam("FREEDOM_DB");

  /*
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/DHTMLapi.js");
  $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FDL/Layout/iframe.js");
  */

   if ($reload) {
     $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FDL/Layout/reload.js");
     $action->unregister("reload$docid");
   } else {
     $action->lay->set("refreshfld",  GetHttpVars("refreshfld"));
   }

  $doc = new_Doc($dbaccess, $docid);
  if (! $doc->isAffected()) $action->exitError(sprintf(_("cannot see unknow reference %s"),$docid));

  $err = $doc->control("view");
  if ($err != "") $action->exitError($err);
  
  if ($doc->isConfidential()) {      
    redirect($action,"FDL",
	     "FDL_CONFIDENTIAL&id=".$doc->id);
  }
  $action->lay->set("RSS", ($doc->getValue("gui_isrss")=="yes"));
  $action->lay->set("rsslink", $doc->getRssLink());
  if ($doc->cvid > 0) {
    // special controlled view
    $cvdoc= new_Doc($dbaccess, $doc->cvid);
    $cvdoc->set($doc);
    if ($vid != "") {    
      $err = $cvdoc->control($vid); // control special view
      if ($err != "") $action->exitError($err);  
    } else  {
      // search preferred view	
      $vid=$doc->getDefaultView(false,"id");
      if ($vid)   setHttpVar("vid",$vid);
    } 
    if ($vid != "") {
      $tview = $cvdoc->getView($vid);
      $doc->setMask($tview["CV_MSKID"]);
      if ($zonebodycard == "") $zonebodycard=$tview["CV_ZVIEW"];
    }
  }
  // set emblem
  $action->lay->set("emblem",$doc->getEmblem());
  $domains=$doc->getDomainIds();
  if (empty($domains)) {
      $action->lay->set("inDomain",false);
  } else {
      $action->lay->set("inDomain",true);
  }
  
  if ($doc->doctype == 'Z') {
    $err =_("This document has been deleted");
  } else {    
    // disabled control just to refresh
    $doc->disableEditControl();
    $err=$doc->refresh();
    $err.=$doc->preConsultation();
    $doc->enableEditControl();
    if ($doc->hasWaitingFiles()) {
    	/*
      $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FDL/Layout/verifycomputedfiles.js");
      */
      $action->lay->set('verifyfiles',true);
    }
  }
  // set view zone
  if ($zonebodycard == "") {
  	$zonebodycard = $doc->defaultview;
  }
  if ($zonebodycard == "") {
  	$zonebodycard ="FDL:VIEWBODYCARD";
  }

  // with doc head ?
  $zo=$doc->getZoneOption($zonebodycard);
  if (GetHttpVars("dochead")=="")   $dochead=  (! preg_match("/[T|U|V]/", $zo, $reg));
  else $dochead = (GetHttpVars("dochead",'Y') == "Y");
  $action->lay->set("viewbarmenu",($zo=="V"));

  $action->lay->set("LGTEXTERROR", strlen($err));
  $action->lay->set("TEXTERROR", nl2br($err));
  $action->lay->Set("ZONEBODYCARD", $doc->viewDoc($zonebodycard,$target,$ulink,$abstract));
  
 
  /*
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/geometry.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
  $action->parent->AddJsRef($action->GetParam("CORE_STANDURL")."app=FDL&action=VIEWDOCJS&sesscache=".$action->session->id);
  */





  //------------------------------
  // display document attributes
  $action->lay->Set("reference", $doc->initid.(( $doc->name=="")?"":" ({$doc->name})"));

  $action->lay->Set("revision", $doc->revision);
  
  $action->lay->Set("lockedid",0);
  $action->lay->Set("comment", $doc->comment);

  if ($doc->confidential >0) $action->lay->Set("locked", _("confidential"));
  else if ($doc->locked == -1) $action->lay->Set("locked", _("fixed"));
  else if ($doc->archiveid) $action->lay->Set("locked", _("archived"));
  else if ($doc->control("edit") != "") $action->lay->Set("locked", _("read only"));
  else if ($doc->locked == 0) {
    $action->lay->Set("locked", _("not locked"));
  } else {
    $user = new User("", abs($doc->locked));
    $action->lay->Set("locked", $user->firstname." ".$user->lastname);
    $action->lay->Set("lockedid", $user->fid);
  }

  $action->lay->Set("dhelp", "none");
  if ($doc->fromid > 0) {
    $cdoc = $doc->getFamDoc();
    $action->lay->Set("classtitle", $cdoc->getTitle());
    if (getFamilyHelpFile($action,$doc->fromid) ) {      
      $action->lay->Set("dhelp", "");
      $action->lay->Set("helpid", $doc->fromid);
    }
  } else {
    $action->lay->Set("classtitle", _("no family"));
  }
  $action->lay->Set("postitid", ($doc->postitid>0)?$doc->postitid:false);
  $action->lay->Set("waskid",0);
  $action->lay->Set("latestwaskid",0);

  if ($doc->locked != -1) {
    $latestidwask=$doc->getLatestIdWithAsk();
    if ($latestidwask) {
      $rdoc=new_doc($doc->dbaccess,$latestidwask);
     
      if (!$rdoc->askIsCompleted())   $action->lay->Set("latestwaskid",$latestidwask);
    }
  } else {
    if (! $doc->askIsCompleted()) {   	
      $action->lay->Set("waskid","1");
    }
  }
  

  
  if ($doc->doctype=='F' || $doc->doctype=='D') {
	$action->lay->Set("forum",(abs(intval($doc->forumid))>0 ? true : false ));
  } else {
	$action->lay->Set("forum",false);
  }
 
  if (($target=="mail") && ($doc->icon != "")) $action->lay->Set("iconsrc", "cid:icon");
  else $action->lay->Set("iconsrc", $doc->geticon());

  if ($doc->fromid > 0)    $action->lay->Set("cid", $doc->fromid);
  else   $action->lay->Set("cid", $doc->id);
  
  $action->lay->Set("viewstate", "none");
  $action->lay->Set("state", "");

  $state=$doc->getState();
  $action->lay->Set("statecolor",$doc->getStateColor("transparent"));
  if ($state) { // see only if it is a transitionnal doc
    if ($doc->locked == -1) $action->lay->Set("state", $action->text($state));
    else {
      if ($doc->lmodify == 'Y') $stateaction=$doc->getStateActivity(_("current_state"));
      else $stateaction=$doc->getStateActivity();
      $action->lay->Set("state", sprintf("%s (<i>%s</i>)",$stateaction,$action->text($state)));
    }
    $action->lay->Set("viewstate", "inherit");
    $action->lay->Set("wid", ($doc->wid>0)?$doc->wid:$doc->state);
  } 
  $action->lay->Set("version", $doc->version);

  $action->lay->Set("title", $doc->getHTMLTitle());
  $action->lay->Set("id", $docid);

  

  if ($abstract){
    // only 3 properties for abstract mode
    $listattr = $doc->GetAbstractAttributes();
  } else {
    $listattr = $doc->GetNormalAttributes();    
  }
 

    
  // see or don't see head
  $action->lay->Set("HEAD",$dochead);
  $action->lay->Set("ACTIONS",(getHttpVars("viewbarmenu")==1));

  $action->lay->Set("amail",(($doc->usefor != "P")&&
			     ($doc->control('send')==""))?"inline":"none");

  

  // update access date
  $doc->adate=$doc->getTimeDate(0,true);
  $doc->modify(true,array("adate"),true);
  if ($doc->delUTag($action->user->id,"TOVIEW")=="") {
    $err=$doc->addUTag($action->user->id,"VIEWED");
  }

}

?>
