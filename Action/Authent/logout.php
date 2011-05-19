<?php
/**
 * Close session
 *
 * @author Anakeen 1999
 * @version $Id: logout.php,v 1.12 2008/06/24 16:05:51 jerome Exp $
 * @license http://www.gnu.org/licenses/lgpl-3.0.html GNU Lesser General Public License
 * @package WHAT
 * @subpackage CORE
 * @deprecated since HTTP Authentification
 */
/**
 */
include_once('Class.Session.php');
include_once('Class.User.php');
include_once('Lib.Http.php');
include_once('WHAT/Class.AuthenticatorManager.php');
/**
 * Close session
 *
 */
function logout(&$action) {
  global $_SERVER;
  global $_POST;

  include_once('WHAT/Lib.Common.php');

  $log = new Log("", "Dynacase", "Session");
  $facility = constant($action->getParam("AUTH_LOGFACILITY", "LOG_AUTH"));

  $authtype = getAuthType();
  
  if( $authtype == 'apache' ) {

    // Apache has already handled the authentication

  } else {

    $authClass = strtolower($authtype)."Authenticator";
    if (! @include_once('WHAT/Class.'.$authClass.'.php')) {
      print "Unknown authtype ".$_GET['authtype'];
      exit;
    }
    $auth = new $authClass( $authtype, "__for_logout__" );
  }
  
  if( $authtype == 'cas' || $authtype == 'html' || $authtype == 'basic') {
    $redir_uri = $action->GetParam("CORE_BASEURL");
    $action->session->close();

    AuthenticatorManager::secureLog("close", "see you tomorrow", "",  $_SERVER["REMOTE_ADDR"], $action->user->login, $_SERVER["HTTP_USER_AGENT"]);
    $auth->logout($redir_uri);
    exit(0);
  }
  
  $rapp = GetHttpVars("rapp");
  $raction = GetHttpVars("raction");
  $rurl = GetHttpVars("rurl", $action->GetParam("CORE_ROOTURL"));
  
  AuthenticatorManager::secureLog("close", "see you tomorrow", "",  $_SERVER["REMOTE_ADDR"], $action->user->login, $_SERVER["HTTP_USER_AGENT"]);
  $action->session->close();

  if(!isset($_SERVER['PHP_AUTH_USER']) || ($_POST["SeenBefore"] == 1 && !strcmp($_POST["OldAuth"],$_SERVER['PHP_AUTH_USER'] )) ) {
    authenticate($action);
  } else {
    redirect($action,$rapp,$raction,$rurl);
  }
}

/**
 * Send a 401 Unauthorized HTTP header
 */
function authenticate(&$action) {
  //   Header( "WWW-Authenticate: Basic realm=\"WHAT Connection\", stale=FALSE");
  //Header( "WWW-Authenticate: Basic realm=\"WHAT Connection\", stale=true");
  //Header( "HTTP/1.0 401 Unauthorized");
  
  header('WWW-Authenticate: Basic realm="'.$action->getParam("CORE_REALM","Dynacase Platform connection").'"');
  header('HTTP/1.0 401 Unauthorized');
  // Header("Location:guest.php");
  echo _("Vous devez entrer un nom d'utilisateur valide et un mot de passe correct pour acceder a cette ressource");
  exit;
}     
?>
