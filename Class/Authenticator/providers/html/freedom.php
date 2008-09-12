<?php

Class htmlFreedomProvider {
  private $parms = array();
  
  public function __construct($parms) {
    $this->parms = $parms;
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

  public function checkAuthentication() {
    include_once('WHAT/Class.Session.php');

    $session = new Session($this->parms{'cookie'});
    if( array_key_exists($this->parms{'cookie'}, $_COOKIE) ) {
      $session->Set($_COOKIE[$this->parms{'cookie'}]);
    } else {
      $session->Set();
    }
    
    if( $session->read('username') != "" ) {
      return TRUE;
    }
    
    if( ! array_key_exists($this->parms{'username'}, $_POST) ) {
      return FALSE;
    }
    if( ! array_key_exists($this->parms{'password'}, $_POST) ) {
      return FALSE;
    }
    
    if( $this->validateCredential($_POST[$this->parms{'username'}], $_POST[$this->parms{'password'}]) ) {
      $session->register('username', $_POST[$this->parms{'username'}]);
      $session->register('password', $_POST[$this->parms{'password'}]);
      return TRUE;
    }
    return FALSE;
  }

  public function checkAuthorization($opt) {
    if( ! array_key_exists('username', $opt) ) {
      error_log(__CLASS__."::".__FUNCTION__." "."Missing username key in opt array");
      return FALSE;
    }
    $dbh = pg_connect($this->parms{'connection'});
    if( $dbh == FALSE ) {
      error_log(__CLASS__."::".__FUNCTION__." "."Error connecting to database");
      return FALSE;
    }
    $stmt = pg_prepare($dbh, "get_status", 'SELECT status FROM users WHERE login = $1');
    if( $stmt == FALSE ) {
      error_log(__CLASS__."::".__FUNCTION__." "."Error preparing select statement");
      return FALSE;
    }
    $res = pg_execute($dbh, "get_status", array($opt['username']));
    if( $res == FALSE ) {
      error_log(__CLASS__."::".__FUNCTION__." "."Error in result of get_status");
      return FALSE;
    }
    $status = pg_fetch_result($res, 0);
    if( $status == 'D' ) {
      error_log(__CLASS__."::".__FUNCTION__." "."Account ".$opt['username']." has been suspended");
      return FALSE;
    }
    return TRUE;
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
    
    error_log("referer_uri = ".$referer_uri." / REQUEST_URI = ".$_SERVER['REQUEST_URI']);
    if( $referer_uri == "" ) {
      error_log("Setting fromuri = ".$_SERVER['REQUEST_URI']);
      $session->register('fromuri', $_SERVER['REQUEST_URI']);
    } else if( $session->read('fromuri') == "" && $referer_uri != $_SERVER['REQUEST_URI'] ) {
      error_log("Setting fromuri = ".$_SERVER['REQUEST_URI']);
      $session->register('fromuri', $_SERVER['REQUEST_URI']);
    }

    if( array_key_exists('authurl', $this->parms )) {
      header('Location: '.$this->parms{'authurl'});
      return TRUE;
    }
    
    error_log(__CLASS__."::".__FUNCTION__." "."Error: no authurl of askAuthentication() method defined for ".$this->parms{'type'}.$this->parms{'provider'}."Provider");
    return FALSE;
  }

  public function logout($redir_uri) {
    $session_auth = new Session($this->parms{'cookie'});
    if( array_key_exists($this->parms{'cookie'}, $_COOKIE) ) {
      $session_auth->Set($_COOKIE[$this->parms{'cookie'}]);
      error_log("Closing auth session for cookie : ".$this->parms{'cookie'});
      $session_auth->close();
    }
    if( $redir_uri == "" && array_key_exists('indexurl', $this->parms) ) {
      header('Location: '.$this->parms{'indexurl'});
    } else {
      header('Location: '.$redir_uri);
    }
    return TRUE;
  }

  public function setSessionVar($name, $value) {
    $session_auth = new Session($this->parms{'cookie'});
    if( array_key_exists($this->parms{'cookie'}, $_COOKIE) ) {
      $session_auth->Set($_COOKIE[$this->parms{'cookie'}]);
    } else {
      $session_auth->Set();
    }
    
    $session_auth->register($name, $value);
    
    return $session_auth->read($name);
  }
  
  public function getSessionVar($name) {
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
