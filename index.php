<?php
/**
 * Main program to activate action in WHAT software
 *
 * All HTTP requests call index.php to execute action within application
 *
 * @author Anakeen 2000 
 * @version $Id: index.php,v 1.64 2008/12/16 15:51:53 jerome Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage 
 */
 /**
 */

include_once('WHAT/Lib.Common.php');

$authtype = getAuthType();
 
if( $authtype == 'basic' || $authtype == 'html'|| $authtype == 'open' ) {
  include_once('WHAT/Class.Authenticator.php');
  $auth = new Authenticator(
			    array_merge(
					array(
					      'type' => getAuthType(),
					      'provider' => getAuthProvider(),
					      ),
					getAuthParam()
				  )
			    );

} else if( $authtype == 'apache' ) {
  // Apache has already handled the authentication
  global $_SERVER;
    if ($_SERVER['PHP_AUTH_USER']=="") {
      header('HTTP/1.0 403 Forbidden');
      echo _("User must be authenticate");
      exit;
    }
} else {
  print "Unknown authtype ".$_GET['authtype'];
  exit;
}

if( $authtype != 'apache' ) {
  $status = $auth->checkAuthentication();
  if( $status == FALSE ) {
    sleep(2); // wait for robots
    $auth->askAuthentication();
    exit(0);
  }

  $status = $auth->checkAuthorization(
                                      array(
                                            'username' => $auth->getauthUser(),
                                            )
                                      );
  if( $status == FALSE ) {
    $auth->logout("guest.php?sole=A&app=AUTHENT&action=UNAUTHORIZED");
    exit(0);
  }

  $_SERVER['PHP_AUTH_USER'] = $auth->getAuthUser();
  $_SERVER['PHP_AUTH_PW'] = $auth->getAuthPw();
}

if( file_exists('maintenance.lock') ) {
  if( $_SERVER['PHP_AUTH_USER'] != 'admin' ) {
    if( $authtype != 'apache' ) {
      $auth->logout();
    }
    include_once('WHAT/stop.php');
    exit(0);
  }
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
$standalone = GetHttpVars("sole","Y");
if (! getHttpVars("app")) {
  $_GET["app"]="CORE";
  if ($_SERVER["FREEDOM_ACCESS"]) {
    $_GET["app"] = $_SERVER["FREEDOM_ACCESS"];
    $_GET["action"] = "";    
  }
 }


if (isset($_COOKIE['freedom_param'])) $sess_num= $_COOKIE['freedom_param'];
else $sess_num=GetHttpVars('freedom_param');//$_GET["session"];

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

ini_set("memory_limit",$core->GetParam("MEMORY_LIMIT","32")."M");
//$core->SetSession($session);

$CORE_LOGLEVEL=$core->GetParam("CORE_LOGLEVEL", "IWEF");
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

$add_args = "";
if( array_key_exists('authtype', $_GET) ) {
  $add_args .= "&authtype=".$_GET['authtype'];
}

$urlindex=$core->getParam("CORE_URLINDEX");
if ($urlindex) $core->SetVolatileParam("CORE_EXTERNURL",$urlindex);
else $core->SetVolatileParam("CORE_EXTERNURL",$puburl."/");
 
$core->SetVolatileParam("CORE_PUBURL", "."); // relative links
$core->SetVolatileParam("CORE_ABSURL", $puburl."/"); // absolute links
$core->SetVolatileParam("CORE_JSURL", "WHAT/Layout");
$core->SetVolatileParam("CORE_ROOTURL", "?sole=R$add_args&");
$core->SetVolatileParam("CORE_BASEURL", "?sole=A$add_args&");
$core->SetVolatileParam("CORE_SBASEURL","?sole=A&freedom_param={$session->id}$add_args&");
$core->SetVolatileParam("CORE_STANDURL","?sole=Y$add_args&");
$core->SetVolatileParam("CORE_SSTANDURL","?sole=Y&freedom_param={$session->id}$add_args&");
$core->SetVolatileParam("CORE_ASTANDURL","$puburl/$indexphp?sole=Y$add_args&"); // absolute links


// ----------------------------------------
// Init Application & Actions Objects
if (($standalone == "") || ($standalone == "N")) {  
  $action = new Action();
  $action->Set("MAIN",$core,$session);
} else {
  $appl = new Application();
  $err=$appl->Set(getHttpVars("app"),$core,$session);
  if ($err) {
    print $err;
    exit;
  }


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
  $action->Set(getHttpVars("action"),$appl,$session);

}
if ($auth) {
  $core_lang = $auth->getSessionVar('CORE_LANG');
  if( $core_lang != '' ) {
    $action->setParamU('CORE_LANG', $core_lang);
    $auth->setSessionVar('CORE_LANG', '');
  }    
  $action->auth=&$auth;
  $core->SetVolatileParam("CORE_BASICAUTH",'&authtype=basic');
 } else  $core->SetVolatileParam("CORE_BASICAUTH",'');

$nav=$_SERVER['HTTP_USER_AGENT'];
$pos=strpos($nav,"MSIE");
if ($action->Read("navigator","") == "") {
  if ( $pos !== false ) {
    $action->Register("navigator","EXPLORER");
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

if( preg_match('/MSIE ([0-9]+).*/', $nav, $match) ) {
  switch( $match[1] ) {
  case "6":
    $ISIE6 = true;
    break;
  }
}

$core->SetVolatileParam("ISIE",($action->read("navigator")=="EXPLORER"));
$core->SetVolatileParam("ISIE6", ($ISIE6 === true));

// init for gettext
setLanguage($action->Getparam("CORE_LANG"));

  
$action->log->debug("gettext init for ".$action->parent->name.$action->Getparam("CORE_LANG"));


if (($standalone == "Y") || ($standalone == "N") || ($standalone == "")) {
  echo ($action->execute ());
} else if ($standalone == "R") {      
      $app = GetHttpVars("app","CORE");
      $act = GetHttpVars("action","");

      // compute others argument to propagate to redirect url
      global $_GET;
      $getargs="";
      while (list($k, $v) =each($_GET)) {
	if ( ($k != "freedom_param") &&
	     ($k != "app") &&
	     ($k != "sole") &&
	     ($k != "action") )
	$getargs .= "&".$k."=".$v;
      }
      redirect($action, "CORE", "MAIN&appd=${app}&actd=${act}".urlencode($getargs),$action->GetParam("CORE_STANDURL"));
    } else if ($standalone == "A") {	
	if ((isset ($appl)) && ( $appl->with_frame != "Y" ))  {  
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
	  } else {
	    // This document is completed 
	    echo ($action->execute ());
	  }	  
      }
?>