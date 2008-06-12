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

  error_log(__FUNCTION__);

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
    // Delete the session in database
    $action->session->DeleteSession();
    // ... and egenerate a new session ID
    session_name('session');
    session_start();
    session_regenerate_id();
    session_commit();
    sleep(1); // for robots
    error_log(__CLASS__."::".__FUNCTION__." ".'Location : '.$_SERVER['SCRIPT_NAME'].'?sole=A&app=AUTHENT&action=LOGINFORM&error=1');
    // Redirect to authentication
    header('Location: '.$_SERVER['SCRIPT_NAME'].'?sole=A&app=AUTHENT&action=LOGINFORM&error=1');
    exit(0);
  }
  // Delete the session in database
  $action->session->DeleteSession();
  // ... and delete all the _SESSION vars except the 'username' var
  session_name('session');
  session_start();
  if( ! array_key_exists('username', $_SESSION) ) {
    error_log(__CLASS__."::".__FUNCTION__." "."Error: _SESSION['username'] should exists");
    exit(0);
  }

  $username = $_SESSION['username'];
  if( array_key_exists('fromuri', $_SESSION) ) {
    $fromuri = $_SESSION['fromuri'];
  } else {
    $fromuri = "index.php";
  }

  session_unset();
  $_SESSION['username'] = $username;
  session_commit();

  error_log(__CLASS__."::".__FUNCTION__." ".'Redirect Location: '.$fromuri);
  // Redirect to initial page
  header('Location: '.$fromuri);
  exit(0);
}

?>