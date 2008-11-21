<?php
/**
 * Main program to activate action in WHAT software
 *
 * All HTTP requests call index.php to execute action within application
 *
 * @author Anakeen 2000 
 * @version $Id: indexq.php,v 1.1 2008/11/21 15:02:30 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage 
 */
 /**
 */
global $tic1;
function dtic($text="") {
  static $ptic=0;
  static $ptic0=0;

  $deb=gettimeofday();
  $tuc1= $deb["sec"]+$deb["usec"]/1000000;
  if ($ptic==0) {
    $ptic=$tuc1;
    $ptic0=$tuc1;
  }

  $msg= sprintf("%s : %.03f -  %.03f ",$text,($tuc1-$ptic), ($tuc1-$ptic0));
  $ptic=$tuc1;
  return $msg;
}
$deb=gettimeofday();
$tic1 = $deb["sec"]+$deb["usec"]/1000000;
include_once('WHAT/Lib.Common.php');

$authtype = getAuthType();
 
if( $authtype == 'basic' || $authtype == 'html' ) {
  ini_set("session.use_cookies","0");
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

global $SQLDELAY, $SQLDEBUG;
global $TSQLDELAY;	
$SQLDEBUG=true;
// ----------------------------------------

$indexphp=basename($_SERVER["SCRIPT_NAME"]);
 
$log=new Log("",$indexphp);

$CoreNull = "";
global $CORE_LOGLEVEL;


global $_GET;
$standalone = GetHttpVars("sole","Y");
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

$core->SetVolatileParam("TRACETIME", "true");
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

if ($auth) {
  $core_lang = $auth->getSessionVar('CORE_LANG');
  if( $core_lang != '' ) {
    $action->setParamU('CORE_LANG', $core_lang);
    $auth->setSessionVar('CORE_LANG', '');
  }
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

setLanguage($action->Getparam("CORE_LANG"));

$action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/tracetime.js");
$deb=gettimeofday();
$ticainit= $deb["sec"]+$deb["usec"]/1000000;
$trace=$action->read("trace");
$trace["url"]=$_SERVER["REQUEST_URI"];
$trace["init"]=sprintf("%.03fs" ,$ticainit-$tic1);
  



if (($standalone == "Y") || ($standalone == "N") || ($standalone == ""))
{
  	$out=$action->execute ();
   $deb=gettimeofday();
   $tic4= $deb["sec"]+$deb["usec"]/1000000;
   $trace["app"]=sprintf("%.03fs" ,$tic4-$ticainit);
   $trace["memory"]=sprintf("%dkb" ,round(memory_get_usage()/1024));
   $trace["queries"]=sprintf("%.03fs #%d",$SQLDELAY, count($TSQLDELAY));
   $trace["server all"]=sprintf("%.03fs" ,$tic4-$tic1);
   $trace["n"]="-------------";
   $strace='var TTRACE=new Object();'."\n";
   foreach ($trace as $k=>$v) $strace.=sprintf(" TTRACE['%s']='%s';\n",$k,$v);
   $out=str_replace("<head>","<head><script>$strace</script>",$out);
   echo ($out);
   $action->unregister("trace");   
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
	if ( ($k != "freedom_param") &&
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
	    // This document is completed 
	    $out=$action->execute();  
	    $deb=gettimeofday();
            $tic4= $deb["sec"]+$deb["usec"]/1000000;
	    $trace["app"]=sprintf("%.03fs" ,$tic4-$ticainit);
	    $trace["memory"]=sprintf("%dkb" ,round(memory_get_usage()/1024));
	    $trace["queries"]=sprintf("%.03fs #%d",$SQLDELAY, count($TSQLDELAY));
	    $trace["server all"]=sprintf("%.03fs" ,$tic4-$tic1);
	    $trace["n"]="-------------";
	    $strace='var TTRACE=new Object();'."\n";
	    foreach ($trace as $k=>$v) $strace.=sprintf(" TTRACE['%s']='%s';\n",$k,$v);
	    $out=str_replace("<head>","<head><script>$strace</script>",$out);
            echo $out;
	    $action->unregister("trace");  
	  }
	  
      }

   $deb=gettimeofday(); 
  $tic5 = $deb["sec"]+$deb["usec"]/1000000;


function sortqdelay($a,$b) {
	$xa=doubleval($a["t"]);
	$xb=doubleval($b["t"]);
	if ($xa > $xb) return -1;
	else if ($xa < $xb) return 1;
	return 0;
}

usort($TSQLDELAY,"sortqdelay");
if (false) {
printf("//<SUP><B>%.3fs</B><I>[OUT:%.3fs]</I> <I>[Init:%.3fs]</I> <I>[App:%.3fs]</I> <I>[S%.3fs %d]</I> <I>%dKo</I><A href=\"#\" onclick=\"document.getElementById('TSQLDELAY').style.display='';\"><I>[Q %.2fs §%d]</I></a></SUP>",
       $tic5-$tic1,
       $tic5-$tic4,
       $ticainit-$tic1,
       $tic4-$ticainit,
       $tic3-$tic2,$nbcache,			
       round(memory_get_usage()/1024),	
       $SQLDELAY,
	count($TSQLDELAY));
print("<table style=\"display:none\" id='TSQLDELAY'>");
foreach ($TSQLDELAY as $k=>$v) {
  print "<tr>";
  if (! is_array($v)) print "ERIIR $v";
  foreach ($v as $kk=>$vv) {
    print "<td>".(str_replace("\n","",$vv))."</td>";
  }	         
  print "</tr>";
}
print("</table>");
 }
?>
