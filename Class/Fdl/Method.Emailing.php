<?php
/**
 * Methods for emailing family
 *
 * @author Anakeen 2005
 * @version $Id: Method.Emailing.php,v 1.14 2008/08/14 09:59:14 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */
var $defaultedit= "FDL:FDL_PUBEDIT";
var $defaultmview= "FDL:FDL_PUBMAIL:T";
function fdl_pubsendmail($target="_self",$ulink=true,$abstract=false) {
  $this->viewdefaultcard($target,$ulink,$abstract);
  $this->lay->set("V_PUBM_BODY", str_replace("&#x5B;","[",$this->lay->get("V_PUBM_BODY")));

  $uid=getHttpVars("uid");
  if ($uid) {
    $udoc=new_Doc($this->dbaccess,$uid);
    if ($udoc->isAlive()) {
      $listattr = $udoc->GetNormalAttributes();
      $atarget=""; // must not be mail the same bacuse it is not the doc itself
      foreach($listattr as $k=>$v) {
	$value=$udoc->getValue($v->id);
	
	if ($value || ($v->type=="image")) $this->lay->Set(strtoupper($v->id),$udoc->GetHtmlValue($v,$value,$atarget,$ulink));
	else $this->lay->Set(strtoupper($v->id),false);
      }  
    }
  }
}
function fdl_pubprintone($target="_self",$ulink=true,$abstract=false) {
  return $this->fdl_pubsendmail($target,$ulink,$abstract); 
}
function fdl_pubedit() {
  $this->editattr();
  $famid=$this->getValue("PUBM_IDFAM","USER");
  $udoc=createDoc($this->dbaccess,$famid,false);
  $listattr = $udoc->GetNormalAttributes();  
  foreach($listattr as $k=>$v) {
    $tatt[$k]=array("aid"=>"[".strtoupper($k)."]",
		    "alabel"=>str_replace("'","\\'",$v->getLabel()));
    
  }
  $listattr = $udoc->GetFileAttributes();  
  foreach($listattr as $k=>$v) {
    if ($v->type=="image") {
      $tatt[$k]=array("aid"=>"<img src=\"[".strtoupper($k)."]\">",
		      "alabel"=>str_replace("'","\\'",$v->getLabel()));
    } else {
      $tatt[$k]=array("aid"=>"<a href=\"[".strtoupper($k)."]\">".$v->getLabel()."</a>",
		      "alabel"=>str_replace("'","\\'",$v->getLabel()));
      
    }
    
  }
  $this->lay->set("famattr",sprintf(_("%s attribute"),$this->getValue("pubm_fam","personne")));
  $this->lay->setBlockData("ATTR",$tatt);
  

}
/**
 * Fusion all document to be printed
 * @param Action &$action current action
 * @global uid Http var : user document id (if not all use rpresent in folder)
 */
function fdl_pubprint($target="_self",$ulink=true,$abstract=false) {
  global $action;
  // GetAllParameters

  $udocid = GetHttpVars("uid");
  $subject=$this->getValue("pubm_title");
  $body=$this->getValue("pubm_body");
  $zonebodycard = "FDL:FDL_PUBPRINTONE:S"; // define view zone

  
  if ($udocid > 0) {
    $t[]=getTDoc($this->dbaccess,$udocid);
  } else {
    $t=$this->getContent();
  }
  
  if (preg_match("/\[[a-z]+_[a-z0-9_]+\]/i",str_replace("&#x5B;","[",$body))) {
    foreach ($t as $k=>$v) {
      $zoneu=$zonebodycard."?uid=".$v["id"];
      $tlay[]=array("doc"=>$this->viewDoc($zoneu,"",true),
		    "subject"=>$v["title"]);	      
    }
  } else {
    $laydoc=$this->viewDoc($zonebodycard,"",true);
    
    foreach ($t as $k=>$v) {
      $tlay[]=array("doc"=>$laydoc,
		    "subject"=>$v["title"]);      
    }
  }
  if ($err) $action->AddWarningMsg($err);
  if (count($t)==0) $action->AddWarningMsg(_("no available persons found"));

  $this->lay->setBlockData("DOCS",$tlay);
  $this->lay->set("BGIMG",$this->getHtmlAttrValue("pubm_bgimg"));
}

/**
 * Fusion all document to be displayed
 * idem as fdl_pubprint but without new page
 * @param Action &$action current action
 * @global uid Http var : user document id (if not all use rpresent in folder)
 */
function fdl_pubdisplay($target="_self",$ulink=true,$abstract=false) {
  return $this->fdl_pubprint($target,$ulink,$abstract);
}
function fdl_pubmail($target="_self",$ulink=true,$abstract=false) {
  include_once("FDL/mailcard.php");
  global $action;
  $subject=$this->getValue("pubm_title");
  $body=$this->getValue("pubm_body");

  $t=$this->getContent();
  $mailattr=strtolower($this->getValue("PÜBM_MAILATT","us_mail"));


  $tout=array();
  $zonebodycard="FDL:FDL_PUBSENDMAIL:S";
  if (preg_match("/\[[a-z]+_[a-z0-9_]+\]/i",$body)) {
    foreach ($t as $k=>$v) {
      $mail=getv($v,$mailattr);
      if ($mail != "") {
	$zoneu=$zonebodycard."?uid=".$v["id"];
	$to=$mail;	
	$cc="";
	$err=sendCard($action,
		      $this->id,
		      $to,$cc,$subject,
		      $zoneu);
	$tout[]=array("name"=>$v["title"],
		      "mailto"=>$to,
		      "color"=>($err)?"#ea4c4c":"#7df89d",
		      "status"=>($err)?$err:"OK");
      }
    }
  } else {
    $tmail=array();
    foreach ($t as $k=>$v) {
      $mail=getv($v,$mailattr);
      if ($mail != "") $tmail[]=$mail;
    }
    $to="";
    $bcc=implode(",",$tmail);
    $cc="";
    $err=sendCard($action,
		  $this->id,
		  $to,$cc,$subject,
		  $zonebodycard,false,"","",$bcc);
    $tout[]=array("name"=>"-",
		  "mailto"=>$bcc,
		  "color"=>($err)?"#ea4c4c":"#7df89d",
		  "status"=>($err)?$err:"OK");
  }
  if ($err) $action->AddWarningMsg($err);
  $this->lay->setBlockData("MAILS",$tout);
  $this->viewattr($target,$ulink,$abstract);
}
/**
 * Preview of each document to be printed
 *
 */
function fdl_pubpreview($target="_self",$ulink=true,$abstract=false) {

  $this->lay->set("dirid",$this->id);
  
}
/**
 * Preview of each document to be printed
 */
function fdl_pubnavpreview($target="_self",$ulink=true,$abstract=false) {

  $t=$this->getContent();
    
  foreach ($t as $k=>$v) {
    $tlay[]=array("udocid"=>$v["id"],
		  "utitle"=>$v["title"]);      
  }
  
  if ($err) $action->AddWarningMsg($err);

  $this->lay->setBlockData("DOCS",$tlay);
  $this->lay->set("dirid",$this->id);
    
}


?>