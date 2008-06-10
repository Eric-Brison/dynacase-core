<?php
/**
 * Close session
 *
 * @author Anakeen 1999
 * @version $Id: logout.php,v 1.9 2008/06/10 15:01:51 jerome Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 * @deprecated since HTTP Authentification
 */
/**
 */
include_once('Class.Session.php');
include_once('Class.User.php');
include_once('Lib.Http.php');
/**
 * Close session
 *
 */
function logout(&$action) {
  global $_SERVER;
  global $_POST;

  $authtype = 'html';
  
  if( $authtype == 'basic' ) {
    include_once('WHAT/Lib.Common.php');
    include_once('WHAT/Class.Authenticator.php');
    $auth = new Authenticator(
			      array(
				    'type' => 'basic',
				    'realm' => 'Freedom',
				    'provider' => 'freedom',
				    'connection' => 'service='.getServiceCore(),
				    )
    );
  } else if( $authtype == 'html' ) {
    include_once('WHAT/Class.Authenticator.php');
    $auth = new Authenticator(
			      array(
				    'type' => 'html',
				    'provider' => 'freedom',
				    'username' => 'username',
				    'password' => 'password',
				    'cookie' => 'session',
				    'connection' => 'service='.getServiceCore(),
				    )
			      );
  } else if( $authtype == 'apache' ) {
    // Apache has already handled the authentication
  } else {
    print "Unknown authtype ".$_GET['authtype'];
    exit;
  }

  if( $authtype == 'html' ) {
    $action->session->DeleteSession(session_id());
    $redir_uri = preg_replace('/^([^?]+).*/', '$1', $_SERVER['REQUEST_URI']);
    $auth->logout($redir_uri);
    exit(0);
  }
  
  $action->session->Close();
  
  $rapp = GetHttpVars("rapp");
  $raction = GetHttpVars("raction");
  $rurl = GetHttpVars("rurl", $action->GetParam("CORE_ROOTURL"));
  
  if( $authtype == 'basic' ) {
    $action->session->DeleteSession(session_id());
    $redir_uri = preg_replace('/^([^?]+).*/', '$1', $_SERVER['REQUEST_URI']);
    $auth->logout($redir_uri);
    exit(0);
  }

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
  
  header('WWW-Authenticate: Basic realm="'.$action->getParam("CORE_REALM","FREEDOM Connection").'"');
  header('HTTP/1.0 401 Unauthorized');
  // Header("Location:guest.php");
  echo _("Vous devez entrer un nom d'utilisateur valide et un mot de passe correct pour acceder a cette ressource");
  exit;
}     
?>
