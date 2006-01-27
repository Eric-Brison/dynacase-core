<?php
/**
 * Main program to activate action in WHAT software
 *
 * All HTTP requests call index.php to execute action within application
 *
 * @author Anakeen 2000 
 * @version $Id: index.php,v 1.37 2006/01/27 16:36:53 eric Exp $
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
// First control
if(!isset($_SERVER['PHP_AUTH_USER'])  ) {
  Header("Location:guest.php");
  exit;
}

include_once('Class.Action.php');
include_once('Class.Application.php');
include_once('Class.Session.php');
include_once('Lib.Http.php');
include_once('Class.Log.php');
include_once('Class.Domain.php');
include_once('Class.DbObj.php');

// ----------------------------------------

$indexphp=basename($_SERVER["SCRIPT_NAME"]);
 
$log=new Log("",$indexphp);

$CoreNull = "";
global $CORE_LOGLEVEL;

global $_GET;
if (!isset($_GET["app"])) $_GET["app"]="CORE";
if (!isset($_GET["action"])) $_GET["action"]="";


$standalone = GetHttpVars("sole");

if (isset($_COOKIE['session'])) $sess_num= $_COOKIE['session'];
else $sess_num=GetHttpVars("session");//$_GET["session"];

$session=new Session();
if (!  $session->Set($sess_num))  {
    print "<B>:~((</B>";
    exit;
  };



$core = new Application();
$core->Set("CORE",$CoreNull,$session);

if ($core->user->login != $_SERVER['PHP_AUTH_USER']) {
  // reopen a new session
  $session->Set("");
  $core->SetSession($session);
}
//$core->SetSession($session);

$CORE_LOGLEVEL=$core->GetParam("CORE_LOGLEVEL", "IWEF");
ini_set("memory_limit",$core->GetParam("MEMORY_LIMIT","32")."M");
// ----------------------------------------
// Init PUBLISH URL from script name



// determine publish url (detect ssl require)
 
if ($_SERVER['HTTPS'] != 'on')   $puburl = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/$indexphp";
 else $puburl = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/$indexphp";


$core->SetVolatileParam("CORE_PUBURL", "."); // relative links
$core->SetVolatileParam("CORE_ABSURL", $puburl."/"); // absolute links
$core->SetVolatileParam("CORE_JSURL", "WHAT/Layout");





$core->SetVolatileParam("CORE_ROOTURL", "$indexphp?sole=R&");
$core->SetVolatileParam("CORE_BASEURL", "$indexphp?sole=A&");
$core->SetVolatileParam("CORE_SBASEURL","$indexphp?sole=A&session={$session->id}&");
$core->SetVolatileParam("CORE_STANDURL","$indexphp?sole=Y&");
$core->SetVolatileParam("CORE_SSTANDURL","$indexphp?sole=Y&session={$session->id}&");

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
