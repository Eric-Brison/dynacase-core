<?php

Class htmlFreedomProvider {
  private $parms = array();
  private $secret = "052c899bf1e3ade928ef23cffc5529b6";
  
  public function __construct($parms) {
    $this->parms = $parms;
  }

  public function validateCredential($username, $password) {  
    $dbh = pg_connect($this->parms{'connection'});
    if( $dbh == FALSE ) {
      error_log("Error connecting to database");
      return FALSE;
    }
    $stmt = pg_prepare($dbh, "get_password", 'SELECT password FROM users WHERE login = $1');
    if( $stmt == FALSE ) {
      error_log("Error preparing select statement");
      return FALSE;
    }
    $res = pg_execute($dbh, "get_password", array($username));
    if( $res == FALSE ) {
      error_log("Error in result of get_password");
      return FALSE;
    }
    $encrypted_password = pg_fetch_result($res, 0);
    $ret = preg_match("/^(..)/", $encrypted_password, $salt);
    if( $ret == 0 ) {
      return FALSE;
    }
    if( $encrypted_password == crypt($password, $salt[0]) ) {
      return TRUE;
    }
    return FALSE;
  }

  /* 
  public function askAuthentication() {
    header('Content-Type: text/html');
    echo '<html><head><title>Authentication</title></head>';
    if( array_key_exists('indexurl', $this->parms) ) {
      $indexurl = $this->parms{'indexurl'};
    } else {
      $indexurl = $_SERVER['REQUEST_URI'];
    }
    echo '<body><form action="'.$indexurl.'" method="post" encoding="x-www-form-urlencoded">';
    echo '<input type="test" name="'.$this->parms{'username'}.'" />';
    echo '<input type="password" name="'.$this->parms{'password'}.'" />';
    echo '<input type="submit" name="submit" />';
    echo '</form></body></html>';
    return TRUE;
  }
  */

}

?>
