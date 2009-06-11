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

include_once('WHAT/Lib.Main.php');

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




getmainAction($auth,$action);
executeAction($action);



?>