<?php
/**
 * Main program to activate action in WHAT software
 *
 * All HTTP requests call index.php to execute action within application
 *
 * @author Anakeen 2000 
 * @version $Id: index.php,v 1.46 2008/06/10 14:59:58 jerome Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage 
 */
 /**
 */

$authtype = 'html';
 
if( $authtype == 'basic' ) {
  include_once('Authenticator/authenticator.php');
  $auth = new Authenticator(
			    array(
				  'type' => 'basic',
				  'realm' => 'Freedom',
				  'provider' => 'freedom',
				  'connection' => 'host=localhost dbname=anakeen user=anakeen',
				  )
			    );
} else if( $authtype == 'html' ) {
  include_once('Authenticator/authenticator.php');
  $auth = new Authenticator(
			    array(
				  'type' => 'html',
				  'provider' => 'freedom',
				  'username' => 'username',
				  'password' => 'password',
				  'cookie' => 'session',
				  'connection' => 'host=localhost dbname=anakeen user=anakeen',
				  'authurl' => 'auth.php',
				  )
			    );
} else if( $authtype == 'apache' ) {
  // Apache has already handled the authentication
} else {
  print "Unknown authtype ".$_GET['authtype'];
  exit;
}

if( $authtype != 'apache' ) {
  $status = $auth->checkAuthentication();
  if( $status == FALSE ) {
    $auth->askAuthentication();
    exit(0);
  }
  $_SERVER['PHP_AUTH_USER'] = $auth->getAuthUser();
  $_SERVER['PHP_AUTH_PW'] = "Unknown";
}

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
if (!  $session->Set($sess_num))  {
    print "<B>:~((</B>";
    exit;
 };


$core = new Application();
$core->Set("CORE",$CoreNull,$session);

if( $authtype == 'apache' ) {
  if ($core->user->login != $_SERVER['PHP_AUTH_USER']) {
    // reopen a new session
    $session->Set("");
    $core->SetSession($session);
  }
}
//$core->SetSession($session);

$CORE_LOGLEVEL=$core->GetParam("CORE_LOGLEVEL", "IWEF");
ini_set("memory_limit",$core->GetParam("MEMORY_LIMIT","32")."M");
// ----------------------------------------
// Init PUBLISH URL from script name

if (ereg("(.*)/$indexphp", $_SERVER['SCRIPT_NAME'], $reg)) {

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
$core->SetVolatileParam("CORE_ROOTURL", "?sole=R&");
$core->SetVolatileParam("CORE_BASEURL", "?sole=A&");
$core->SetVolatileParam("CORE_SBASEURL","?sole=A&session={$session->id}&");
$core->SetVolatileParam("CORE_STANDURL","?sole=Y&");
$core->SetVolatileParam("CORE_SSTANDURL","?sole=Y&session={$session->id}&");$core->SetVolatileParam("CORE_ASTANDURL","$puburl/$indexphp?sole=Y&"); // absolute links

// ----------------------------------------
// Init Application & Actions Objects
if (($standalone == "") || ($standalone == "N")) {  
  $action = new Action();
  $action->Set("MAIN",$core,$session);
} else {
  $appl = new Application();
  $appl->Set($_GET["app"],$core,$session);

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
