<?php

/**
 * basicAuthenticator class
 *
 * This class provides methods for HTTP Basic authentication
 *
 * @author Anakeen 2009
 * @version $Id: Class.basicAuthenticator.php,v 1.3 2009/01/16 13:33:00 jerome Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package WHAT
 * @subpackage
 */
 /**
 */
include_once('WHAT/Class.Authenticator.php');

Class basicAuthenticator extends Authenticator {

  public function checkAuthentication() {
    
    if( array_key_exists('logout', $_COOKIE) && $_COOKIE['logout'] == "true" )  {
      setcookie('logout', '', time() - 3600);
      return Authenticator::AUTH_ASK;
    }
    
    if( ! array_key_exists('PHP_AUTH_USER', $_SERVER) ) {
      error_log(__CLASS__."::".__FUNCTION__." "."Error: undefined _SERVER[PHP_AUTH_USER]");
      return Authenticator::AUTH_ASK;
    }
    
    if( ! array_key_exists('PHP_AUTH_PW', $_SERVER) ) {
      error_log(__CLASS__."::".__FUNCTION__." "."Error: undefined _SERVER[PHP_AUTH_PW] for user ".$_SERVER['PHP_AUTH_USER']);
      return Authenticator::AUTH_ASK;
    }
    
    if(! is_callable(array($this->provider, 'validateCredential')) ) {
      error_log(__CLASS__."::".__FUNCTION__." "."Error: ".$this->parms{'type'}.$this->parms{'provider'}."Provider must implement validateCredential()");
      return Authenticator::AUTH_NOK;
    }
    
    if( ! $this->provider->validateCredential($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) ) {
      return Authenticator::AUTH_NOK;
    }

    if( ! $this->freedomUserExists($_SERVER['PHP_AUTH_USER']) ) {
      if( ! $this->tryInitializeUser($_SERVER['PHP_AUTH_USER']) ) {
	return Authenticator::AUTH_NOK;
      }
    }

    return Authenticator::AUTH_OK;
  }

  public function checkAuthorization($opt) {
    if( is_callable(array($this->provider, 'checkAuthorization')) ) {
      return $this->provider->checkAuthorization($opt);
    }

    return TRUE;
  }
  
  public function askAuthentication($args=array()) {
    if( is_callable(array($this->provider, 'askAuthentication')) ) {
      return $this->provider->askAuthentication();
    }
    header('HTTP/1.1 401 Authentication Required');
    header('WWW-Authenticate: Basic realm="'.$this->parms{'realm'}.'"');
    header('Connection: close');
    return TRUE;
  }
  
  public function getAuthUser() {
    if( is_callable(array($this->provider, 'getAuthUser')) ) {
      return $this->provider->getAuthUser();
    }

    return $_SERVER['PHP_AUTH_USER'];
  }
  
  public function getAuthPw() {
    if( is_callable(array($this->provider, 'getAuthPw')) ) {
      return $this->provider->getAuthPw();
    }

    return $_SERVER['PHP_AUTH_PW'];
  }
  
  public function logout($redir_uri='') {
    setcookie('logout', 'true', 0);
    if( $redir_uri == '' ) {
      $redir_uri = getParam('CORE_BASEURL');
    }
    header('Location: '.$redir_uri);
    return TRUE;
  }

  public function setSessionVar($name, $value) {
    return TRUE;
  }

  public function getSessionVar($name) {
    return '';
  }


}

?>
