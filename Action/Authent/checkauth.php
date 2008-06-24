<?php
/**
 * PHP Authentification control
 *
 * @author Anakeen 1999
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 * @deprecated since HTTP Authentification
 */

function checkauth(&$action) {
  include_once('WHAT/Lib.Common.php');
  include_once('WHAT/Class.Authenticator.php');
  include_once('WHAT/Class.Session.php');

  $auth = new Authenticator(
                            array_merge(
                                        array(
                                              'type' => 'html',
                                              'provider' => getAuthProvider(),
                                              ),
                                        getauthParam()
                                        )
                            );

  $status = $auth->checkAuthentication();
  if( $status == FALSE ) {
    $action->session->close();
    sleep(1); // for robots
    error_log(__CLASS__."::".__FUNCTION__." ".'Location : '.$_SERVER['SCRIPT_NAME'].'?sole=A&app=AUTHENT&action=LOGINFORM&error=1');
    // Redirect to authentication
    redirect($action, 'AUTHENT', 'LOGINFORM&error=1');
    exit(0);
  }

  $session_auth = new Session($auth->parms{'cookie'});
  if( array_key_exists($auth->parms{'cookie'}, $_COOKIE) ) {
    $session_auth->Set($_COOKIE[$auth->parms{'cookie'}]);
  } else {
    $session_auth->Set();
  }
  
  if( $session_auth->read('username') == "" ) {
    error_log(__CLASS__."::".__FUNCTION__." "."Error: 'username' should exists in session ".$auth->parms{'cookie'});
    exit(0);
  }

  $fromuri = $session_auth->read('fromuri');
  if( $fromuri == "" ) {
    $fromuri = "index.php";
  }

  error_log(__CLASS__."::".__FUNCTION__." ".'Redirect Location: '.$fromuri);

  // Redirect to initial page
  header('Location: '.$fromuri);
  exit(0);
}

?>