<?php

/**
 * Authenticator manager class
 *
 * Manage authentification method (classes)
 *
 * @author Anakeen 2009
 * @version $Id: Class.Authenticator.php,v 1.6 2009/01/16 13:33:00 jerome Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package WHAT
 * @subpackage
 */
 /**
 */

include_once('WHAT/Lib.Common.php');
include_once('WHAT/Class.htmlAuthenticator.php');
include_once('WHAT/Class.Session.php');
include_once('WHAT/Class.User.php');
include_once('WHAT/Class.Log.php');

abstract class AuthenticatorManager {

  public static $session = null;
  public static $auth = null;

  public static function checkAccess($authtype=null) {

    $error = 0;
    if ($authtype==null) $authtype = getAuthType();
    $authProviderList = getAuthProviderList();
    foreach ($authProviderList as $ka=>$authprovider) {
      self::$auth = new htmlAuthenticator( 'html', $authprovider );
      $status = self::$auth->checkAuthentication();
      if ($status) { break; }
    }
    
    if ( $status == false) {
      $error = 1;
      $providerErrno = self::$auth->getProviderErrno();
      if( $providerErrno != 0 ) {
	switch( $providerErrno ) {
	  
	case Provider::ERRNO_BUG_639:
	  // User must change his password
	  $error = -1;
	  break;
	  
	}
      }
      self::secureLog("failure", "invalid credential", $authprovider, $_SERVER["REMOTE_ADDR"], $_REQUEST["auth_user"], $_SERVER["HTTP_USER_AGENT"]);
      
      // count login failure
      if (getParam("AUTHENT_FAILURECOUNT") > 0) {
	$wu = new User();
	if ($wu->SetLoginName($_REQUEST["auth_user"])) {
	  if ($wu->id!=1) {
	    include_once("FDL/freedom_util.php");
	    $du = new_Doc(getParam("FREEDOM_DB"), $wu->fid);
	    if ($du->isAlive()) {  $du->increaseLoginFailure(); }
	  }
	} 
      }
      return $error;
    }

    // Authentication success
    $login = self::$auth->getAuthUser();
    $wu = new User();   
    $existu = false;
    if ($wu->SetLoginName($login) ) {
      $existu = true;
    }
   
    if (!$existu) {
      self::secureLog("failure", "login have no Dynacase account", $authprovider, $_SERVER["REMOTE_ADDR"], $login, $_SERVER["HTTP_USER_AGENT"]);
      return 1;
    }

    if ($wu->id!=1) {
      
      include_once("FDL/freedom_util.php");
      $du = new_Doc(getParam("FREEDOM_DB"), $wu->fid);
      
      // First check if account is active
      if ( $du->isAccountInactive() ) {
	self::secureLog("failure", "inactive account", $authprovider, $_SERVER["REMOTE_ADDR"], $login, $_SERVER["HTTP_USER_AGENT"]);
	return 3;
      }
      
      // check if the account expiration date is elapsed
      if ( $du->accountHasExpired() ) {
	self::secureLog("failure", "account has expired", $authprovider, $_SERVER["REMOTE_ADDR"], $login, $_SERVER["HTTP_USER_AGENT"]);
	return 4;
      }

      // check count of login failure
      $maxfail = getParam("AUTHENT_FAILURECOUNT");
      if ( $maxfail > 0 && $du->getValue("us_loginfailure",0) >= $maxfail ) {
	self::secureLog("failure", "max connection (".$maxfail.") attempts exceeded", $authprovider, $_SERVER["REMOTE_ADDR"], $login, $_SERVER["HTTP_USER_AGENT"]);
	return 2;
      }

      // authen OK, max login failure OK => reset count of login failure
      $du->resetLoginFailure();
    }
  
    self::$session = self::$auth->getAuthSession();
    if( self::$session->read('username') == "" ) {
      self::secureLog("failure", "username should exists in session", $authprovider, $_SERVER["REMOTE_ADDR"], $login, $_SERVER["HTTP_USER_AGENT"]);
      exit(0);
    }
    
    self::secureLog("success", "welcome", $authprovider, $_SERVER["REMOTE_ADDR"], $login, $_SERVER["HTTP_USER_AGENT"]);
    return 0;
    
  }

  public function secureLog($status="", $additionalMessage="", $provider="", $clientIp="", $account="", $userAgent="") {

    $log = new Log("", "Session", "Authentication");
    $facility = constant(getParam("AUTH_LOGFACILITY", "LOG_AUTH"));
    $log->wlog( "S", 
		sprintf("[%s] [%s] [%s] [%s] [%s] [%s]", 
			$status, 
			$additionalMessage, 
			$provider,
			$clientIp,
			$account,
			$userAgent),
		NULL, 
		$facility);
    return 0;
    
  }
    


}

?>