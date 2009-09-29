<?php

/**
 * htmlAuthenticator class
 *
 * This class provides methods for HTML form based authentication
 *
 * @author Anakeen 2009
 * @version $Id: Class.htmlAuthenticator.php,v 1.8 2009/01/16 13:33:00 jerome Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage
 */
 /**
 */
include_once('WHAT/Class.Authenticator.php');

Class htmlAuthenticator extends Authenticator {

  /**
   **
   **
   **/
  public function checkAuthentication() {
    include_once('WHAT/Class.Session.php');

    $session = new Session($this->parms{'cookie'});
    if( array_key_exists($this->parms{'cookie'}, $_COOKIE) ) {
      $session->Set($_COOKIE[$this->parms{'cookie'}]);
    } else {
      $session->Set();
    }
    
    if( $session->read('username') != "" ) return TRUE;
        
    if( ! array_key_exists($this->parms{'username'}, $_POST) ) return FALSE;
    if( ! array_key_exists($this->parms{'password'}, $_POST) ) return FALSE;

    if( is_callable(array($this->provider, 'validateCredential')) ) {
      if( ! $this->provider->validateCredential($_POST[$this->parms{'username'}], $_POST[$this->parms{'password'}]) ) {
	return FALSE;
      }

      if( ! $this->freedomUserExists($_POST[$this->parms{'username'}]) ) {
	if( ! $this->tryInitializeUser($_POST[$this->parms{'username'}]) ) {
	  return FALSE;
	}
      }

      $session->register('username', $_POST[$this->parms{'username'}]);
      $session->register('password', $_POST[$this->parms{'password'}]);
      $session->setuid($_POST[$this->parms{'username'}]);
      return TRUE;
    }

    error_log(__CLASS__."::".__FUNCTION__." "."Error: ".get_class($this->provider)." must implement function validateCredential()");
    return FALSE;
  }

  /**
   **
   **
   **/
  function checkAuthorization($opt) {
    if( is_callable(array($this->provider, 'validateAuthorization')) ) {
      return $this->provider->validateAuthorization($opt);
    }
    return TRUE;
  }
  
  /**
   **
   **
   **/
  public function askAuthentication() {
    include_once('WHAT/Class.Session.php');

    $parsed_referer = parse_url($_SERVER['HTTP_REFERER']);
    
    $referer_uri = "";
    if( $parsed_referer['path'] != "" ) {
      $referer_uri .= $parsed_referer['path'];
    }
    if( $parsed_referer['query'] != "" ) {
      $referer_uri .= "?".$parsed_referer['query'];
    }
    if( $parsed_referer['fragment'] != "" ) {
      $referer_uri .= "#".$parsed_referer['fragment'];
    }
    $session = new Session($this->parms{'cookie'});
    if( array_key_exists($this->parms{'cookie'}, $_COOKIE) ) {
      $session->Set($_COOKIE[$this->parms{'cookie'}]);
    } else {
      $session->Set();
    }
    
//     error_log("referer_uri = ".$referer_uri." / REQUEST_URI = ".$_SERVER['REQUEST_URI']);
    if( $referer_uri == "" ) {
//       error_log("Setting fromuri = ".$_SERVER['REQUEST_URI']);
      $session->register('fromuri', $_SERVER['REQUEST_URI']);
    } else if( $session->read('fromuri') == "" && $referer_uri != $_SERVER['REQUEST_URI'] ) {
//       error_log("Setting fromuri = ".$_SERVER['REQUEST_URI']);
      $session->register('fromuri', $_SERVER['REQUEST_URI']);
    }
    
    if( array_key_exists('authurl', $this->parms )) {
      header('Location: '.$this->parms{'authurl'});
      return TRUE;
    }
    
    error_log(__CLASS__."::".__FUNCTION__." "."Error: no authurl of askAuthentication() method defined for ".$this->parms{'type'}.$this->parms{'provider'}."Provider");
    return FALSE;
  }

  /**
   **
   **
   **/
  public function getAuthUser() {
    include_once('WHAT/Class.Session.php');
    $session_auth = new Session($this->parms{'cookie'});
    if( array_key_exists($this->parms{'cookie'}, $_COOKIE) ) {
      $session_auth->Set($_COOKIE[$this->parms{'cookie'}]);
    } else {
      $session_auth->Set();
    }
    return $session_auth->read('username');
  }
  
  /**
   **
   **
   **/
  public function getAuthPw() {
    include_once('WHAT/Class.Session.php');
    $session_auth = new Session($this->parms{'cookie'});
    if( array_key_exists($this->parms{'cookie'}, $_COOKIE) ) {
      $session_auth->Set($_COOKIE[$this->parms{'cookie'}]);
    } else {
      $session_auth->Set();
    }
    return $session_auth->read('password');
  }
  
  /**
   **
   **
   **/
  public function logout($redir_uri) {
    include_once('WHAT/Class.Session.php');
    $session_auth = new Session($this->parms{'cookie'});
    if( array_key_exists($this->parms{'cookie'}, $_COOKIE) ) {
      $session_auth->Set($_COOKIE[$this->parms{'cookie'}]);
//       error_log("Closing auth session for cookie : ".$this->parms{'cookie'});
      $session_auth->close();
    }
    if( $redir_uri == "" && array_key_exists('indexurl', $this->parms) ) {
      header('Location: '.$this->parms{'indexurl'});
    } else {
      header('Location: '.$redir_uri);
    }
    return TRUE;
  }


 
  /**
   **
   **
   **/
  public function setSessionVar($name, $value) {
    include_once('WHAT/Class.Session.php');
    $session_auth = new Session($this->parms{'cookie'});
    if( array_key_exists($this->parms{'cookie'}, $_COOKIE) ) {
      $session_auth->Set($_COOKIE[$this->parms{'cookie'}]);
    } else {
      $session_auth->Set();
    }
    
    $session_auth->register($name, $value);
    
    return $session_auth->read($name);
  }
  
  /**
   **
   **
   **/
  public function getSessionVar($name) {
    include_once('WHAT/Class.Session.php');
    $session_auth = new Session($this->parms{'cookie'});
    if( array_key_exists($this->parms{'cookie'}, $_COOKIE) ) {
      $session_auth->Set($_COOKIE[$this->parms{'cookie'}]);
    } else {
      $session_auth->Set();
    }
    
    return $session_auth->read($name);
  }


}

?>
