<?php

Class basicFreedomProvider {
  private $parms = array();
  
  public function __construct($parms) {
    $this->parms = $parms;
    if( ! array_key_exists('realm', $parms) ) {
      throw new Exception(__CLASS__."::".__FUNCTION__." "."Error: realm parm is not defined at __construct");
    }
    if( ! array_key_exists('connection', $parms) ) {
      throw new Exception(__CLASS__."::".__FUNCTION__." "."connection parm is not defined at __construct");
    }
  }
  
  public function checkAuthentication() {
    if( ! isset($_SERVER['PHP_AUTH_USER']) ) {
      error_log(__CLASS__."::".__FUNCTION__." "."Error: undefined _SERVER[PHP_AUTH_USER]");
      return FALSE;
    }
    if( ! isset($_SERVER['PHP_AUTH_PW']) ) {
      error_log(__CLASS__."::".__FUNCTION__." "."Error: undefined _SERVER[PHP_AUTH_PW] for user ".$_SERVER['PHP_AUTH_USER']);
      return FALSE;
    }
    return $this->validateCredential($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
  }

  public function validateCredential($username, $password) {
    $dbh = pg_connect($this->parms{'connection'});
    if( $dbh == FALSE ) {
      error_log(__CLASS__."::".__FUNCTION__." "."Error: failed connection to database");
      return FALSE;
    }
    $stmt = pg_prepare($dbh, "get_password", 'SELECT password FROM users WHERE login = $1');
    if( $stmt == FALSE ) {
      error_log(__CLASS__."::".__FUNCTION__." "."Error: pg_prepare(get_password) returned false");
      return FALSE;
    }
    $res = pg_execute($dbh, "get_password", array($username));
    if( $res == FALSE ) {
      error_log(__CLASS__."::".__FUNCTION__." "."Error: pg_execute(get_password) returned false. User $username not found ?");
      return FALSE;
    }
    $encrypted_password = pg_fetch_result($res, 0);
    $ret = preg_match("/^(..)/", $encrypted_password, $salt);
    if( $ret == 0 ) {
      error_log(__CLASS__."::".__FUNCTION__." "."Error: could not get salt from encrypted password for user $username");
      return FALSE;
    }
    if( $encrypted_password == crypt($password, $salt[0]) ) {
      return TRUE;
    }
    return FALSE;
  }  
}

?>
