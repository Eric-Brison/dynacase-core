<?php
/**
 * PHP Authentification control
 *
 * @author Anakeen 1999
 * @license http://www.gnu.org/licenses/lgpl-3.0.html GNU Lesser General Public License
 * @package WHAT
 * @subpackage CORE
 * @deprecated since HTTP Authentification
 */

function checkauth(&$action) {
  include_once('WHAT/Lib.Common.php');
  include_once('WHAT/Class.htmlAuthenticator.php');
  include_once('WHAT/Class.Session.php');
  include_once('WHAT/Class.User.php');
  include_once('WHAT/Class.Log.php');

  $log = new Log("", "Dynacase", "Session");

  $authtype = getAuthType();
  $authProviderList = getAuthProviderList();
  foreach ($authProviderList as $ka=>$authprovider) {
   $auth = new htmlAuthenticator( 'html', $authprovider );
    $status = $auth->checkAuthentication();
     //error_log(__FILE__.":".__LINE__." provider = ".$authprovider." status = ".($status?"OK":"NOK"));
    if ($status) {
      break;
    }
  }

  if( $status == FALSE ) {
    $providerErrno = $auth->getProviderErrno();
    if( $providerErrno != 0 ) {
      switch( $providerErrno ) {
      case Provider::ERRNO_BUG_639:
       // User must change his password
       $action->session->close();
       global $_POST;
       Redirect($action, 'AUTHENT', 'ERRNO_BUG_639');
       exit(0);
      }
    }
    $action->session->close();
    $log->wlog("W","[authentication failure] [invalid credentials] provider=[".$authprovider."] ip=[".$_SERVER["REMOTE_ADDR"]."] user=[".$_REQUEST["auth_user"]."] user-agent=[".$_SERVER["HTTP_USER_AGENT"]."]", NULL, LOG_AUTH);

    // count login failure
    if ($action->getParam("AUTHENT_FAILURECOUNT") > 0) {
      $wu = new User();
      if ($wu->SetLoginName($_REQUEST["auth_user"])) {
	if ($wu->id!=1) {
	  include_once("FDL/freedom_util.php");
	  $du = new_Doc(getParam("FREEDOM_DB"), $wu->fid);
	  if ($du->isAlive()) {  $du->increaseLoginFailure(); }
	}
      } 
    }
      
    sleep(1); // for robots
    // Redirect to authentication
    global $_POST;
    Redirect($action, 'AUTHENT', 'LOGINFORM&error=1&auth_user='.urlencode($_POST['auth_user']));
    exit(0);
  }


  $login = $auth->getAuthUser();
  $wu = new User();   
  $existu = false;
  if ($wu->SetLoginName($login) ) {
    $existu = true;
  }
   
  if (!$existu) {
    $log->wlog("W","[authentication failure] [user has no account] provider=[".$authprovider."] ip=[".$_SERVER["REMOTE_ADDR"]."] user=[".$_REQUEST["auth_user"]."] user-agent=[".$_SERVER["HTTP_USER_AGENT"]."]", NULL, LOG_AUTH);
    global $_POST;
    Redirect($action, 'AUTHENT', 'LOGINFORM&error=1&auth_user='.urlencode($_POST['auth_user']));
    exit(0);
  }
  
  if ($wu->id!=1) {
    include_once("FDL/freedom_util.php");
    $du = new_Doc(getParam("FREEDOM_DB"), $wu->fid);

    // check count of login failure
    $maxfail = $action->getParam("AUTHENT_FAILURECOUNT");
    if ( $maxfail > 0 && $du->getValue("us_loginfailure",0) >= $maxfail ) {
      $log->wlog("W","[session refused] [max connection (".$maxfail.") attempts exceeded] provider=[".$authprovider."] ip=[".$_SERVER["REMOTE_ADDR"]."] user=[".$_REQUEST["auth_user"]."] user-agent=[".$_SERVER["HTTP_USER_AGENT"]."]", NULL, LOG_AUTH); 
    global $_POST;
    Redirect($action, 'AUTHENT', 'LOGINFORM&error=2&auth_user='.urlencode($_POST['auth_user']));
    exit(0);
    }

    // authen OK, max login failure OK => reset count of login failure
    $du->resetLoginFailure();
  }

  $session_auth = $auth->getAuthSession();
  
  if( $session_auth->read('username') == "" ) {
    $log->wlog("W","[authentication failure] [username should exists in session] provider=[".$authprovider."] ip=[".$_SERVER["REMOTE_ADDR"]."] user=[".$_REQUEST["auth_user"]."] user-agent=[".$_SERVER["HTTP_USER_AGENT"]."]", NULL, LOG_AUTH);
    exit(0);
  }

    $log->wlog("W","[authentication success] provider=[".$authprovider."] ip=[".$_SERVER["REMOTE_ADDR"]."] user=[".$_REQUEST["auth_user"]."] user-agent=[".$_SERVER["HTTP_USER_AGENT"]."]", NULL, LOG_AUTH);

  $fromuri = $session_auth->read('fromuri');
  if (($fromuri == "" )|| (preg_match('/app=AUTHENT/',$fromuri))) {
    $fromuri = ".";
  }

  include_once('CORE/lang.php');
  $core_lang = getHttpVars('CORE_LANG');
  if( $core_lang != "" && array_key_exists($core_lang, $lang) ) {
//     error_log(__CLASS__."::".__FUNCTION__." "."Registering vaviable CORE_LANG = '".$core_lang."' in session_auth");
    $session_auth->register('CORE_LANG', $core_lang);
  }

//   error_log(__CLASS__."::".__FUNCTION__." ".'Redirect Location: '.$fromuri);

  // clean $fromuri
  $fromuri = preg_replace('!//+!', '/', $fromuri);
  $fromuri = preg_replace('!&&+!', '&', $fromuri);

  // Redirect to initial page
  header('Location: '.$fromuri);
  exit(0);
}

?>
