<?php
/**
 * Main program to activate action in WHAT software in guest mode
 *
 * @author Anakeen 2000 
 * @version $Id: guest.php,v 1.22 2008/06/12 08:17:31 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage 
 */
 /**
 */



#
# This is the main body of App manager
# It is used to launch application and 
# function giving them all necessary environment
# element
#
#

include_once('Class.Action.php');
include_once('Class.Application.php');
include_once('Class.Session.php');
include_once('Lib.Http.php');
include_once('Class.Log.php');
include_once('Class.Domain.php');
include_once('Class.DbObj.php');


$authtype = getAuthType();
if( $authtype == 'basic' || $authtype == 'html' ) {
  ini_set("session.use_cookies","1");
 }


$log=new Log("","guest.php");

$CoreNull = "";
global $CORE_LOGLEVEL;

global $_GET;
$standalone = GetHttpVars("sole");
if (!isset($_GET["app"])) {
  $_GET["app"]="CORE";
  switch($_SERVER["FREEDOM_ACCESS"]) {
  case "WEBDESK":
    $_GET["app"] = "WEBDESK";
    $_GET["action"] = "";
    $standalone = "Y";
    break;
  }
 }

if (!isset($_GET["action"])) $_GET["action"]="";

if (isset($_COOKIE['session'])) $sess_num= $_COOKIE['session'];
else $sess_num=GetHttpVars("session");//$_GET["session"];
$session=new Session();
$session->Set($sess_num);
if ($session->userid != ANONYMOUS_ID) { 
  // reopen a new anonymous session
  setcookie ("session",$session->id,0,"/");
  unset($_SERVER['PHP_AUTH_USER']); // cause IE send systematicaly AUTH_USER & AUTH_PASSWD
  $session->Set("");
  //setcookie ("session",$session->id,0,"/");
}
if ($session->userid != ANONYMOUS_ID) { 
  // reverify
  print "<B>:~((</B>";
  exit;
}
$core = new Application();
$core->Set("CORE",$CoreNull,$session);

$CORE_LOGLEVEL=$core->GetParam("CORE_LOGLEVEL", "IWEF");
ini_set("memory_limit",$core->GetParam("MEMORY_LIMIT","32")."M");

// ----------------------------------------
// Init PUBLISH URL from script name
if (ereg("(.*)/guest\.php", $_SERVER['SCRIPT_NAME'], $reg)) {

  // determine publish url (detect ssl require)
 
  if ($_SERVER['HTTPS'] != 'on')   $puburl = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT'].$reg[1];
  else $puburl = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT'].$reg[1];
} else {
  // it is not allowed
  print "<B>:~(</B>";
  exit;
}


$urlindex=$core->getParam("CORE_URLINDEX");
if ($urlindex) $core->SetVolatileParam("CORE_EXTERNURL",$urlindex);
else $core->SetVolatileParam("CORE_EXTERNURL",$puburl."/");


$core->SetVolatileParam("CORE_PUBURL", "."); // relative links
$core->SetVolatileParam("CORE_ABSURL", $puburl."/"); // absolute links
$core->SetVolatileParam("CORE_JSURL", "WHAT/Layout");





$core->SetVolatileParam("CORE_ROOTURL", "guest.php?sole=R&");
$core->SetVolatileParam("CORE_BASEURL", "guest.php?sole=A&");
$core->SetVolatileParam("CORE_STANDURL","guest.php?sole=Y&");
$core->SetVolatileParam("CORE_SBASEURL","guest.php?sole=A&session={$session->id}&");
$core->SetVolatileParam("CORE_SSTANDURL","guest.php?sole=Y&session={$session->id}&");


// ----------------------------------------
// Init Application & Actions Objects
if (($standalone == "") || ($standalone == "N")) {
  $action = new Action();
  $action->Set("MAIN",$core,$session);
} else {
  $appl = new Application();
  $appl->Set($_GET["app"],$core);

   if (($appl->machine != "") && ($_SERVER['SERVER_NAME'] != $appl->machine)) { // special machine to redirect    
      if (substr($_SERVER['REQUEST_URI'],0,6) == "http:/") {
         $aquest=parse_url($_SERVER['REQUEST_URI']);
         $aquest['host']=$appl->machine;
         $puburl=glue_url($aquest);
      } else {
         $puburl = "http://".$appl->machine.$_SERVER['REQUEST_URI'];
      }

      Header("Location: $puburl");
      exit;
  }

  // ----------------------------------------
    // test SSL mode needed or not
    // redirect if needed
  if ($appl->ssl == "Y") {
    if ($_SERVER['HTTPS'] != 'on') {

      // redirect to go to ssl http
      $sslurl = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT'].$_SERVER['REQUEST_URI'];
      Header("Location: $sslurl");
      exit;
    }     
    
    $core->SetVolatileParam("CORE_BGCOLOR", $core->GetParam("CORE_SSLBGCOLOR"));
  } 

  
  // -----------------------------------------------
    // now we are in correct protocol (http or https)

  $action = new Action();
  $action->Set($_GET["action"],$appl,$session);

}

$nav=$_SERVER['HTTP_USER_AGENT'];
$pos=strpos($nav,"MSIE");
if ($action->Read("navigator","") == "") {
  if ( $pos>0) {
    $action->Register("navigator","EXPLORER");
    $core->SetVolatileParam("ISIE", true);
    if (ereg("MSIE ([0-9.]+).*",$nav,$reg)) {
      $action->Register("navversion",$reg[1]);      
    }
  } else {
    $action->Register("navigator","NETSCAPE");
    if (ereg("([a-zA-Z]+)/([0-9.]+).*",$nav,$reg)) {
      $action->Register("navversion",$reg[2]);      
    }
  }
}
$core->SetVolatileParam("ISIE",($action->read("navigator")=="EXPLORER"));
// init for gettext
setlocale(LC_MESSAGES,$action->Getparam("CORE_LANG"));  
setlocale(LC_MONETARY, $action->Getparam("CORE_LANG"));
setlocale(LC_TIME, $action->Getparam("CORE_LANG"));
//print $action->Getparam("CORE_LANG");
putenv ("LANG=".$action->Getparam("CORE_LANG")); // needed for old Linux kernel < 2.4
bindtextdomain ("what", "$pubdir/locale");
bind_textdomain_codeset("what", 'ISO-8859-15');
textdomain ("what");

  
  $action->log->debug("gettext init for ".$action->parent->name.$action->Getparam("CORE_LANG"));

if (($standalone == "Y") || ($standalone == "N") || ($standalone == ""))
{
  echo ($action->execute ());
} 
else 
  if ($standalone == "R")
    {      
      $app = GetHttpVars("app","CORE");
      $act = GetHttpVars("action","");

      // compute others argument to propagate to redirect url
      global $_GET;
      $getargs="";
      while (list($k, $v) =each($_GET)) {
	if ( ($k != "session") &&
	     ($k != "app") &&
	     ($k != "sole") &&
	     ($k != "action") )
	$getargs .= "&".$k."=".$v;
      }
      redirect($action, "CORE", "MAIN&appd=${app}&actd=${act}".urlencode($getargs),$action->GetParam("CORE_STANDURL"));
    }
  else
    if ($standalone == "A")
      {
	
	if ((isset ($appl)) && ( $appl->with_frame != "Y" ))
	  {  
	    // This document is not completed : does not contain header and footer

	    // HTML body result
	    // achieve action
	    $body = ($action->execute ());
	    // write HTML header
	    $head = new Layout($action->GetLayoutFile("htmltablehead.xml"),$action);
	    // copy JS ref & code from action to header
	    $head->jsref = $action->parent->GetJsRef();
	    $head->jscode = $action->parent->GetJsCode();
	    $head->set("TITLE", _($action->parent->short_name));	    
	    echo($head->gen());
	    // write HTML body
	    echo ($body);
	    // write HTML footer
	    $foot = new Layout($action->GetLayoutFile("htmltablefoot.xml"),$action);
	    echo($foot->gen());
	  }
	else
	  {
	    // This document is completed 
	    echo ($action->execute ());
	  }
	  
      }


?>
