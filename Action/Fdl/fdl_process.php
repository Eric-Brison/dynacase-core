<?php
/**
 * Emailing
 *
 * @author Anakeen 2005
 * @version $Id: fdl_process.php,v 1.2 2005/06/28 08:37:46 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */




include_once("FDL/mailcard.php");
include_once("FDL/modcard.php");

/**
 * Fusion all document to be printed
 * @param Action &$action current action
 * @global docid Http var : folder id (generaly an action)
 * @global fromedit Http var : (Y|N) if Y action comes from edition else from viewing
 * @global zonebodycard Http var : definition of the zone used for print
 */
function fdl_process(&$action) {

  // GetAllParameters

  $docid = GetHttpVars("id");
  $udocid = GetHttpVars("uid");
  $fromedit = (GetHttpVars("fromedit","Y")=="Y"); // need to compose temporary doc
  $method="::fdl_pubprint(pubm_title,pubm_body)";

  $dbaccess = $action->GetParam("FREEDOM_DB");

  $doc = new_Doc($dbaccess, $docid);
  if ($udocid > 0) {
    $t[]=getTDoc($dbaccess,$udocid);
  } else {
    $t=$doc->getContent(true,array(),true);
  }
  if ($fromedit) {
    $doc = $doc->copy(true,false);
    $err=setPostVars($doc);
    $doc->modify();
  };

  
  $subject=$doc->getValue("pubm_title");
  $body=$doc->getValue("pubm_body");
  if (preg_match("/\[us_[a-z0-9_]+\]/i",$body)) {
    foreach ($t as $k=>$v) {
      $mail=getv($v,"us_mail");
      if ($mail == "") {
	$action->AddWarningMsg(_("no email address found"));
      }
      $zoneu=$zonebodycard."?uid=".$v["id"];
	
      $tlay[]=array("doc"=>$doc->viewDoc($zoneu,"",true),
		    "subject"=>$v["title"]);
	
      
    }
  } else {
    $laydoc=$doc->viewDoc($zonebodycard,"",true);
    
    foreach ($t as $k=>$v) {
      $tlay[]=array("doc"=>$laydoc,
		    "subject"=>$v["title"]);      
    }
  }
  if ($err) $action->AddWarningMsg($err);
  if (count($t)==0) $action->AddWarningMsg(_("no avalaible persons found"));


  $action->lay->setBlockData("DOCS",$tlay);
  $action->lay->set("BGIMG",$doc->getHtmlAttrValue("pubm_bgimg"));


  
}


?>
