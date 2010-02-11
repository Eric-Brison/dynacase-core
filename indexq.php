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
global $tic1;

$deb=gettimeofday();
$tic1 = $deb["sec"]+$deb["usec"]/1000000;
include_once('WHAT/Lib.Main.php');
include_once('WHAT/wdebug.php');

global $SQLDELAY, $SQLDEBUG;
global $TSQLDELAY;	

$authtype = getAuthType();
$authproviderlist = getAuthProvider();

if ($authtype == 'apache') {
  
  // Apache has already handled the authentication
  global $_SERVER;
  if ($_SERVER['PHP_AUTH_USER']=="") {
    header('HTTP/1.0 403 Forbidden');
    echo _("User must be authenticate");
    exit;
  }
  
 } else {

  $authProviderList = explode(",", $authproviderlist);
  foreach ($authProviderList as $ka=>$authprovider) {
    $authClass = strtolower($authtype)."Authenticator";
    if (! @include_once('WHAT/Class.'.$authClass.'.php')) {
      error_log(__FILE__.":".__LINE__."> Unknown authtype ".$authtype);
    } else {
  
      $auth = new $authClass( $authtype, $authprovider);
      $status = $auth->checkAuthentication();
      if ($status) {
	$statusA = $auth->checkAuthorization( array( 'username' => $auth->getauthUser() ) );
	if( $statusA == FALSE ) {
	  $auth->logout("guest.php?sole=A&app=AUTHENT&action=UNAUTHORIZED");
	  exit(0);
	}
	break;
      }
    }
  }

  if( $status == FALSE ) {
    sleep(2); // wait for robots
    $auth->askAuthentication();
    exit(0);
  }
  
  $_SERVER['PHP_AUTH_USER'] = $auth->getAuthUser();
  $_SERVER['PHP_AUTH_PW'] = $auth->getAuthPw();  
 }

if( file_exists('maintenance.lock') ) {
  if( $_SERVER['PHP_AUTH_USER'] != 'admin' ) {
    if( $authtype != 'apache' ) {
      $auth->logout("");
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


// ----------------------------------------

getmainAction($auth,$action);
$deb=gettimeofday();
$ticainit= $deb["sec"]+$deb["usec"]/1000000;
$trace=$action->read("trace");
$trace["url"]=$_SERVER["REQUEST_URI"];
$trace["init"]=sprintf("%.03fs" ,$ticainit-$tic1);
$out='';
$action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/tracetime.js");
executeAction($action,$out);
function sortqdelay($a,$b) {
        $xa=doubleval($a["t"]);
        $xb=doubleval($b["t"]);
        if ($xa > $xb) return -1;
        else if ($xa < $xb) return 1;
        return 0;
}
usort($TSQLDELAY,"sortqdelay");
addLogMsg($TSQLDELAY);
$deb=gettimeofday();
   $tic4= $deb["sec"]+$deb["usec"]/1000000;
   $trace["app"]=sprintf("%.03fs" ,$tic4-$ticainit);
   $trace["memory"]=sprintf("%dkb" ,round(memory_get_usage()/1024));
   $trace["queries"]=sprintf("%.03fs #%d",$SQLDELAY, count($TSQLDELAY));
   $trace["server all"]=sprintf("%.03fs" ,$tic4-$tic1);
   $trace["n"]="-------------";
   $strace='var TTRACE=new Object();'."\n";
   
   
   foreach ($trace as $k=>$v) $strace.=sprintf(" TTRACE['%s']='%s';\n",$k,str_replace("'"," ",$v));
   
   $logcode=$action->lay->GenJsCode(true,true);
   $out=str_replace("<head>","<head><script>;$strace</script>",$out);
   $out=str_replace("</head>","<script>$logcode</script></head>",$out);
echo ($out);
   $action->unregister("trace");
?>