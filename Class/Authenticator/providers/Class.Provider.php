<?php

/**
 * provider abstract class
 *
 */
 /**
 */

abstract class Provider {
 
  public function __construct($authprovider, $parms) {
    $this->parms = $parms;
    $this->pname = strtolower($authprovider);
  }
  
  abstract function validateCredential($username, $password);
  abstract function validateAuthorization($opt);


  public function canICreateUser() {
    if( array_key_exists('allowAutoFreedomUserCreation', $this->parms)
	&& strtolower($this->parms{'allowAutoFreedomUserCreation'}) == 'yes'
	&& is_callable(array($this, 'initializeUser')) ) {
      return TRUE;
    }
    return FALSE;
  }

}

?>