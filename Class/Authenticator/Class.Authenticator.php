<?php

Class Authenticator {
  public $parms = array();
  private $authenticator = null;
  
  public function __construct($parms) {
    $this->parms = $parms;
    if( ! array_key_exists('type', $this->parms) ) {
      throw new Exception(__CLASS__."::".__FUNCTION__." "."Error: undefined type in constructor");
    }
    $ret = @include_once('WHAT/Class.'.$this->parms{'type'}."Authenticator.php");
    if( $ret === FALSE ) {
      throw new Exception(__CLASS__."::".__FUNCTION__." "."Error: WHAT/Class.".$this->parms{'type'}."Authenticator.php not found");
    }
    if( ! class_exists($this->parms{'type'}."Authenticator") ) {
      throw new Exception(__CLASS__."::".__FUNCTION__." "."Error: ".$this->parms{'type'}."Authenticator class not found");
    }
    $authclass = $this->parms{'type'}."Authenticator";
    $this->authenticator = new $authclass($this->parms);
  }
  
  public function checkAuthentication() {
    return $this->authenticator->checkAuthentication();
  }

  public function checkAuthorization($opt=array()) {
    return $this->authenticator->checkAuthorization($opt);
  }
  
  public function validateCredential($username, $password) {
    return $this->authenticator->validateCredential($username, $password);
  }
  
  public function askAuthentication() {
    return $this->authenticator->askAuthentication();
  }
  
  public function getAuthUser() {
    return $this->authenticator->getAuthUser();
  }
  
  public function logout($redir_uri="") {
    return $this->authenticator->logout($redir_uri);
  }

}

?>
