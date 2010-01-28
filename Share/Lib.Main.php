<?php
/**
 * Main first level function 
 *
 * @author Anakeen 2002
 * @version $Id: Lib.Common.php,v 1.50 2008/09/11 14:50:04 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
/**
 */
include_once("Lib.Common.php");



function getMainAction($auth,&$action) {
  include_once('Class.Action.php');
  include_once('Class.Application.php');
  include_once('Class.Session.php');
  include_once('Lib.Http.php');
  include_once('Class.Log.php');
  include_once('Class.Domain.php');
  include_once('Class.DbObj.php');
  $indexphp=basename($_SERVER["SCRIPT_NAME"]);
 
  $log=new Log("",$indexphp);

  $CoreNull = "";
  global $CORE_LOGLEVEL;


  global $_GET;
  $standalone = GetHttpVars("sole","Y");
  $defaultapp=false;
  if (! getHttpVars("app")) {
      $defaultapp=true;
    $_GET["app"]="CORE";
    if ($_SERVER["FREEDOM_ACCESS"]) {
      $_GET["app"] = $_SERVER["FREEDOM_ACCESS"];
      $_GET["action"] = "";    
    } else {
        $_GET["action"] = "INVALID"; 
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
  if ($defaultapp && $core->GetParam("CORE_START_APP")) {
    $_GET["app"] =$core->GetParam("CORE_START_APP");
  }
  ini_set("memory_limit",$core->GetParam("MEMORY_LIMIT","32")."M");
  //$core->SetSession($session);

  $CORE_LOGLEVEL=$core->GetParam("CORE_LOGLEVEL", "IWEF");
  // ----------------------------------------
  // Init PUBLISH URL from script name

  $pattern = preg_quote($indexphp);
  if (preg_match("|(.*)/$pattern|", $_SERVER['SCRIPT_NAME'], $reg)) {

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
	if (preg_match("/MSIE ([0-9.]+).*/",$nav,$reg)) {
	  $action->Register("navversion",$reg[1]);
	}
      } else {
	$action->Register("navigator","NETSCAPE");
	if (preg_match("|([a-zA-Z]+)/([0-9.]+).*|",$nav,$reg)) {
	  $action->Register("navversion",$reg[2]);      
	}
      }
    }

    $ISIE6 = false;
    $ISAPPLEWEBKIT = false;
    $ISSAFARI = false;
    $ISCHROME = false;
    if( preg_match('/MSIE ([0-9]+).*/', $nav, $match) ) {
      switch( $match[1] ) {
      case "6":
	$ISIE6 = true;
	break;
      }
    } elseif( preg_match('|\bAppleWebKit/(.*?)\b|', $nav, $match) ) {
      $ISAPPLEWEBKIT = true;
      if( preg_match('|\bSafari/(.*?)\b|', $nav, $match) ) {
	$ISSAFARI = true;
	if( preg_match('|\bChrome/(.*?)\b|', $nav, $match) ) {
	  $ISCHROME = true;
	}
      }
    }

    $core->SetVolatileParam("ISIE",($action->read("navigator")=="EXPLORER"));
    $core->SetVolatileParam("ISIE6", ($ISIE6 === true));
    $core->SetVolatileParam("ISAPPLEWEBKIT", ($ISAPPLEWEBKIT === true));
    $core->SetVolatileParam("ISSAFARI", ($ISSAFARI === true));
    $core->SetVolatileParam("ISCHROME", ($ISCHROME === true));
    // init for gettext
    setLanguage($action->Getparam("CORE_LANG"));

  
    $action->log->debug("gettext init for ".$action->parent->name.$action->Getparam("CORE_LANG"));


  }
}
/**
 * execute action 
 * app and action http param
 */
function executeAction(&$action,&$out=null) {

  $standalone = GetHttpVars("sole","Y");
  if (($standalone == "Y") || ($standalone == "N") || ($standalone == "")) {
    if ($out !== null) $out=$action->execute ();
    else echo ($action->execute ());

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
    if ((isset ($action->parent)) && ( $action->parent->with_frame != "Y" ))  {  
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
      if ($out !== null) {
	$out=$head->gen();
	$out.=$body;
	$foot = new Layout($action->GetLayoutFile("htmltablefoot.xml"),$action);
	$out.=$foot->gen();
      } else {
	echo($head->gen());
	// write HTML body
	echo ($body);
	// write HTML footer
	$foot = new Layout($action->GetLayoutFile("htmltablefoot.xml"),$action);
	echo($foot->gen());
      }
    } else {
      // This document is completed
      if ($out !== null) $out=$action->execute ();
      else echo ($action->execute ());
    }	  
  }
  
}

?>
