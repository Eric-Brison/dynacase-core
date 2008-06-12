<?php

Class basicAuthenticator {
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

    error_log(__CLASS__."::".__FUNCTION__." "."Error: ".$this->parms{'type'}.$this->parms{'provider'}."Provider must implement checkAuthentication()");
    return FALSE;
  }

  public function checkAuthorization($opt) {
    if( is_callable(array($this->provider, 'checkAuthorization')) ) {
      return $this->provider->checkAuthorization($opt);
    }

    return TRUE;
  }
  
  public function askAuthentication() {
    if( is_callable(array($this->provider, 'askAuthentication')) ) {
      return $this->provider->askAuthentication();
    }

    header('HTTP/1.1 401 Authentication Required');
    header('WWW-Authenticate: Basic realm="'.$this->parms{'realm'}.'"');
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
  
  public function logout($redir_uri) {
    if( is_callable(array($this->provider, 'logout')) ) {
      return $this->provider->logout($redir_uri);
    }

    return $this->askAuthentication();
  }

}

?>
