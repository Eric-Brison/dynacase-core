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
    $creatuserlist=explode(",", strtolower(trim(getenv("allowAutoFreedomUserCreation"))));
    if (in_array(strtolower($this->pname),$creatuserlist) && is_callable(array($this, 'initializeUser'))) return TRUE;
    return FALSE;
  }
}

?>