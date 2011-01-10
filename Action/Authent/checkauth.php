<?php
/**
 * PHP Authentification control
 *
 * @author Anakeen 1999
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package WHAT
 * @subpackage CORE
 * @deprecated since HTTP Authentification
 */

function checkauth(&$action) {
  include_once('WHAT/Lib.Common.php');
  include_once('WHAT/Class.htmlAuthenticator.php');
  include_once('WHAT/Class.Session.php');
  include_once('WHAT/Class.User.php');

  $authtype = getAuthType();
  $authProviderList = getAuthProviderList();
  foreach ($authProviderList as $ka=>$authprovider) {
   $auth = new htmlAuthenticator( 'html', $authprovider );
    $status = $auth->checkAuthentication();
//     error_log(__FILE__.":".__LINE__." provider = ".$authprovider." status = ".($status?"OK":"NOK"));
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
    sleep(1); // for robots
//     error_log(__CLASS__."::".__FUNCTION__." ".'Location : '.$_SERVER['SCRIPT_NAME'].'?sole=A&app=AUTHENT&action=LOGINFORM&error=1');
    // Redirect to authentication
    global $_POST;
    Redirect($action, 'AUTHENT', 'LOGINFORM&error=1&auth_user='.urlencode($_POST['auth_user']));
    exit(0);
  }


  $login = $auth->getAuthUser();
  $pass = $auth->getAuthPw();
  $wu = new User();   
  $existu = false;
  if( $wu->SetLoginName($login) ) {
    $existu = true;
  }
   
  if (!$existu) {
    error_log(basename(__FILE__).":".__LINE__." User $login has no account");
    global $_POST;
    Redirect($action, 'AUTHENT', 'LOGINFORM&error=1&auth_user='.urlencode($_POST['auth_user']));
    exit(0);
  }
  
  $session_auth = $auth->getAuthSession();
  
  
  if( $session_auth->read('username') == "" ) {
    error_log(__CLASS__."::".__FUNCTION__." "."Error: 'username' should exists in session ".$auth->parms{'cookie'});
    exit(0);
  }

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
