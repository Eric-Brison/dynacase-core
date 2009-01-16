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

Class htmlAuthenticator {
  private $parms = array();
  private $provider = null;
  
  public function __construct($parms) {
    $this->parms = $parms;
    
    if( ! array_key_exists('provider', $this->parms) ) {
      throw new Exception(__CLASS__."::".__FUNCTION__." "."Error: provider parm not specified at __construct");
    }
    $ret = @include_once('WHAT/providers/'.$this->parms{'type'}.'/'.$this->parms{'provider'}.'.php');
    if( $ret === FALSE ) {
      throw new Exception(__CLASS__."::".__FUNCTION__." "."Error: WHAT/providers/".$this->parms{'type'}."/".$this->parms{'provider'}.".php not found");
    }
    if( ! class_exists($this->parms{'type'}.$this->parms{'provider'}.'Provider') ) {
      throw new Exception(__CLASS__."::".__FUNCTION__." "."Error: ".$this->parms{'type'}.$this->parms{'provider'}."Provider class not found");
    }
    $providerclass = $this->parms{'type'}.$this->parms{'provider'}.'Provider';
    $this->provider = new $providerclass($this->parms);
  }
  
  public function checkAuthentication() {
    if( is_callable(array($this->provider, 'checkAuthentication')) ) {
      return $this->provider->checkAuthentication();
    }

    session_name($this->parms{'cookie'});
    session_id($_COOKIE[$this->parms{'cookie'}]);
    session_start();

    if( array_key_exists('username', $_SESSION) ) {
      session_commit();
      return TRUE;
    }

    if( ! array_key_exists($this->parms{'username'}, $_POST) ) {
      session_commit();
      return FALSE;
    }
    if( ! array_key_exists($this->parms{'password'}, $_POST) ) {
      session_commit();
      return FALSE;
    }

    if( $this->validateCredential($_POST[$this->parms{'username'}], $_POST[$this->parms{'password'}]) ) {
      $_SESSION['username'] = $_POST[$this->parms{'username'}];
      session_commit();
      return TRUE;
    }
    session_commit();
    return FALSE;
  }

  function checkAuthorization($opt) {
    if( is_callable(array($this->provider, 'checkAuthorization')) ) {
      return $this->provider->checkAuthorization($opt);
    }

    return TRUE;
  }
  
  function validateCredential($username, $password) {
    if( is_callable(array($this->provider, 'validateCredential')) ) {
      return $this->provider->validateCredential($username, $password);
    }

    error_log(__CLASS__."::".__FUNCTION__." "."Error: ".$this->parms{'type'}.$this->parms{'provider'}."Provider must implement function validateCredential()");
    return FALSE;
  }

  public function askAuthentication() {
    if( is_callable(array($this->provider, 'askAuthentication')) ) {
      return $this->provider->askAuthentication();
    }

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

    session_name($this->parms{'cookie'});
    session_id($_COOKIE[$this->parms{'cookie'}]);
    session_start();
    if( ! array_key_exists('fromuri', $_SESSION) && $referer_uri != $_SERVER['REQUEST_URI'] ) {
      $_SESSION['fromuri'] = $_SERVER['REQUEST_URI'];
    }
    session_commit();

    if( array_key_exists('authurl', $this->parms )) {
      header('Location: '.$this->parms{'authurl'});
      return TRUE;
    }

    error_log(__CLASS__."::".__FUNCTION__." "."Error: no authurl of askAuthentication() method defined for ".$this->parms{'type'}.$this->parms{'provider'}."Provider");
    return FALSE;
  }
  
  public function getAuthUser() {
    if( is_callable(array($this->provider, 'getAuthUser')) ) {
      return $this->provider->getAuthUser();
    }
    
    session_name($this->parms{'cookie'});
    session_id($_COOKIE[$this->parms{'cookie'}]);
    session_start();
    if( array_key_exists('username', $_SESSION) ) {
      $username = $_SESSION['username'];
      session_commit();
      return $username;
    }
    session_commit();
    return null;
  }
  
  public function getAuthPw() {
    if( is_callable(array($this->provider, 'getAuthPw')) ) {
      return $this->provider->getAuthPw();
    }
    
    session_name($this->parms{'cookie'});
    session_id($_COOKIE[$this->parms{'cookie'}]);
    session_start();
    if( array_key_exists('password', $_SESSION) ) {
      $password = $_SESSION['password'];
      session_commit();
      return $password;
    }
    session_commit();
    return null;
  }
  
  public function logout($redir_uri="") {
    if( is_callable(array($this->provider, 'logout')) ) {
      return $this->provider->logout($redir_uri);
    }

    session_name($this->parms{'cookie'});
    session_id($_COOKIE[$this->parms{'cookie'}]);
    session_start();
    session_unset();
    session_destroy();
    $_SESSION = array();
    session_commit();

    if( $redir_uri == "" && array_key_exists('indexurl', $this->parms) ) {
      header('Location: '.$this->parms{'indexurl'});
    } else {
      header('Location: '.$redir_uri);
    }
    return TRUE;
  }

  public function setSessionVar($name, $value) {
    if( is_callable(array($this->provider, 'setSessionVar')) ) {
      return $this->provider->setSessionVar($name, $value);
    }
    
    session_name($this->parms{'cookie'});
    session_id($_COOKIE[$this->parms{'cookie'}]);
    session_start();
    $_SESSION[$name] = $value;
    $value = $_SESSION[$name];
    session_commit();
    
    return $value;
  }
  
  public function getSessionVar($name) {
    if( is_callable(array($this->provider, 'getSessionVar')) ) {
      return $this->provider->getSessionVar($name);
    }
    
    session_name($this->parms{'cookie'});
    session_id($_COOKIE[$this->parms{'cookie'}]);
    session_start();
    $value = $_SESSION[$name];
    session_commit();
    
    return $value;
  }
  
}

?>
