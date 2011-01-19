<?php
/**
 * Retrieve a file converted from source
 *
 * @author Anakeen 2008
 * @version $Id: getfiletransformation.php,v 1.5 2008/10/09 16:12:21 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



include_once("FDL/Class.Dir.php");


/**
 * Retrieve file converted
 * @param Action &$action current action
 * @global id Http var : document identificator to see
 * @global tid Http var : transformation is done : tid is set to give result
 * @global zone Http var : specific representation where engine is set
 * @global vid Http var : vault id file to convert if zone is not set
 * @global idv Http var : view identificator to apply mask
 */
function getfiletransformation(&$action) {  
  $docid = GetHttpVars("id");
  $tid = GetHttpVars("tid");
  $zone = GetHttpVars("zone");
  $vid = GetHttpVars("vid"); 
  $idv = GetHttpVars("idv"); 
  $dbaccess = $action->GetParam("FREEDOM_DB");

  if ($docid=="") $action->exitError(_("no document reference"));
  if (! is_numeric($docid)) $docid=getIdFromName($dbaccess,$docid);
  if (intval($docid) == 0) $action->exitError(sprintf(_("unknow logical reference '%s'"),GetHttpVars("id")));
  $doc = new_Doc($dbaccess, $docid);
  if (! $doc->isAffected()) $action->exitError(sprintf(_("cannot see unknow reference %s"),$docid));

  if ($tid > 0) {
    $err=downloadTid($tid,$doc->title);
    if ($err=="") exit;
    else $action->exitError($err);
  } else {
    $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FDL/Layout/getfiletransformation.js");
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/resizeimg.js");


    $err = $doc->control("view");
    if ($err != "") $action->exitError($err);
  
    $action->lay->Set("TITLE",$doc->title);
    $action->lay->Set("id",$docid);
    $action->lay->Set("icon",$doc->getIcon());

    if ($zone == "") $zone=$doc->defaultview;
    $zo=$doc->getZoneOption($zone);
   
    $engine=$doc->getZoneTransform($zone);
    if ($engine) { 
      $tplfile=$doc->getZoneFile($zone);
      if (($idv != "") && ($doc->cvid)) {
	$cvdoc= new_Doc($dbaccess, $doc->cvid);
	$err = $cvdoc->control(trim($idv)); // control special view
	if ($err != "") $action->exitError("CV:".$cvdoc->title."\n".$err);
	$tview = $cvdoc->getView($idv);	
	if ($tview["CV_MSKID"]) $doc->setMask($tview["CV_MSKID"]);
      }
      if (preg_match('/\.odt/',$tplfile)) {
	  $target="ooo";
	  $file=$doc->viewdoc($zone,$target,$ulink);	
      } else {	  
	  $file= uniqid(getTmpDir()."/doc")."-".$doc->id.".html";
	  if ($zo=="S") $view=$doc->viewdoc($zone,"te");
	  else $view=completeHTMLDoc($doc,$zone);
	  file_put_contents($file,preg_replace("/<script([^>]*)>.*?<\/script>/is","",$view));
      }

	$ulink=false;
	$err=sendRequestForFileTransformation($file,$engine,$info);
	//@unlink($file);
	$action->lay->set("error",($err!=""));
	if ($err=="") {
	  $action->lay->set("tid",$info["tid"]);
	  $action->lay->set("status",$info["status"]);
	  $action->lay->set("message",$info["comment"]);
	  $action->lay->set("processtext",sprintf(_("processing <b>%s</b> transformation"),$engine));
	} else {

	  $action->lay->set("tid","");
	  $action->lay->set("status","K");
	  $action->lay->set("message",$err);
	  $action->lay->set("processtext",sprintf(_("cannot lauch <b>%s</b> transformation"),$engine));
	}
      }   
    
  }
}


function completeHTMLDoc(&$doc,$zone) {
  global $action;
  $layout="singledoc.xml"; // the default
  $docmail = new Layout(getLayoutFile("FDL",$layout),$action);

  $docmail->Set("TITLE", $doc->title);
  $docmail->Set("iconsrc", $doc->getIcon());
  $docmail->Set("ID", $doc->id);
  $docmail->Set("zone", $zone);
  $docmail->Set("baseurl",dirname($action->getParam("TE_URLINDEX"))."/") ;	
 
  
  return $docmail->gen();
}

function downloadTid($tid,$title) {  
  $tea=getParam("TE_ACTIVATE");
  if ($tea!="yes") return;
  if (@include_once("WHAT/Class.TEClient.php")) {
    global $action;
    include_once("FDL/insertfile.php");
     
    $filename= uniqid(getTmpDir()."/tid-".$tid);
    $err=getTEFile($tid,$filename,$info);
    $mime=getSysMimeFile($filename, basename($filename));
    $ext=getExtension($mime);
    if ($ext=="") $ext=$infoout->teng_lname; 
    if ($err=="") {
      Http_DownloadFile($filename,$title.".$ext",$mime,false,false);
      @unlink($filename);
    }
  } else {
    AddWarningMsg(_("TE engine activate but TE-CLIENT not found"));
  }
  
  return $err;
}

function sendRequestForFileTransformation($filename,$engine,&$info) { 
  if (file_exists($filename)  && ($engine!="")) {

    $tea=getParam("TE_ACTIVATE");
    if ($tea!="yes") return _("TE engine is not activate");
    if (include_once("WHAT/Class.TEClient.php")) {
      global $action;
      include_once("FDL/Class.TaskRequest.php");
     
      $callback="";
      $ot=new TransformationEngine(getParam("TE_HOST"),getParam("TE_PORT"));
      $err=$ot->sendTransformation($engine,$vid,$filename,$callback,$info);
      if ($err=="") {
	$dbaccess = GetParam("FREEDOM_DB");
	$tr=new TaskRequest($dbaccess);
	$tr->tid=$info["tid"];
	$tr->fkey=$vid;
	$tr->status=$info["status"];
	$tr->comment=$info["comment"];
	$tr->uid=$action->user->id;
	$tr->uname=$action->user->firstname." ".$action->user->lastname;
	$err=$tr->Add();
      }
    } else {
      AddWarningMsg(_("TE engine activate but TE-CLIENT not found"));
    }
  } else {
    $err="no file filename ($filename) or engine ($engine)";
  }
  return $err;
}
?>
