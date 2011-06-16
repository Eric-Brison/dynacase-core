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
  include_once('WHAT/Class.AuthenticatorManager.php');
  include_once('WHAT/Class.htmlAuthenticator.php');
  include_once('WHAT/Class.User.php');
  include_once('WHAT/Class.Log.php');

  $status = AuthenticatorManager::checkAccess();
  //error_log("checkauth: AuthenticatorManager::checkAccess() = {$status}");

  switch ($status) {
 
  case 0: // it'good, user is authentified, just log the connexion
    AuthenticatorManager::secureLog("success", "welcome", AuthenticatorManager::$auth->provider->parms['type']."/".AuthenticatorManager::$auth->provider->parms['provider'], $_SERVER["REMOTE_ADDR"], AuthenticatorManager::$auth->getAuthUser(), $_SERVER["HTTP_USER_AGENT"]);
    break;

  case -1:
    // User must change his password
    $action->session->close();
    global $_POST;
    Redirect($action, 'AUTHENT', 'ERRNO_BUG_639');
    exit(0);
    break;
    
  default:
    $action->session->close();
    sleep(1); // for robots
    // Redirect to authentication
    global $_POST;
    Redirect($action, 'AUTHENT', 'LOGINFORM&error='.$status.'&auth_user='.urlencode($_POST['auth_user']));
    exit(0);

  }

  $fromuri = AuthenticatorManager::$session->read('fromuri');
  if (($fromuri == "" )|| (preg_match('/app=AUTHENT/',$fromuri))) {
    $fromuri = ".";
  }
  
  include_once('CORE/lang.php');
  $core_lang = getHttpVars('CORE_LANG');
  if( $core_lang != "" && array_key_exists($core_lang, $lang) ) {
    //     error_log(__CLASS__."::".__FUNCTION__." "."Registering vaviable CORE_LANG = '".$core_lang."' in session_auth");
    AuthenticatorManager::$session->register('CORE_LANG', $core_lang);
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
