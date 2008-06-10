<?php

Class htmlFooProvider {
  private $parms = array();
  
  public function __construct($parms) {
    $this->parms = $parms;
  }

  public function validateCredential($username, $password) {
    if( $username == "foo" && $password == "bar" ) {
      return TRUE;
    }
    return FALSE;
  }
  
  public function askAuthentication() {
    header("HTTP/1.1 200 OK");
    header("Content-Type: text/html");
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

}

?>
