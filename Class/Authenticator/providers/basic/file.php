<?php

/**
 * basicFileProvider class
 *
 * This class provides methods for HTTP Basic authentication against a
 * Apache htpasswd text file
 *
 * @author Anakeen 2009
 * @version $Id: file.php,v 1.2 2009/01/16 13:33:01 jerome Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage
 */
 /**
 */

Class basicFileProvider {
  private $parms = array();
  private $passwd = array();
  
  public function __construct($parms) {
    $this->parms = $parms;
    if( ! array_key_exists('realm', $parms) ) {
      throw new Exception(__CLASS__."::".__FUNCTION__." "."Error: realm parm is not defined at __construct");
    }
    if( ! array_key_exists('authfile', $parms) ) {
      throw new Exception(__CLASS__."::".__FUNCTION__." "."Error: authfile parm is not defined at __construct");
    }
    $ret = $this->rereadPasswdFile();
    if( $ret == FALSE ) {
      throw new Exception(__CLASS__."::".__FUNCTION__." "."Error: reading authfile ".$this->parms{'authfile'});
    }
  }
  
  public function rereadPasswdFile() {
    $fh = fopen($this->parms{'authfile'}, 'r');
    if( $fh == FALSE ) {
      error_log(__CLASS__."::".__FUNCTION__." "."Error: opening file ".$this->parms{'authfile'});
      return FALSE;
    }
    $this->passwd = array();
    while($line = fgets($fh)) {
      $el = split(':', $line);
      if( count($el) != 2 ) {
	continue;
      }
      $this->passwd{$el[0]} = trim($el[1]);
    }
    fclose($fh);
    return TRUE;
  }
  
  public function checkAuthentication() {
    if( array_key_exists('logout', $_COOKIE) && $_COOKIE['logout'] == "true" )  {
      setcookie('logout', '', time() - 3600);
      return FALSE;
    }
    if( ! isset($_SERVER['PHP_AUTH_USER']) ) {
      return FALSE;
    }
    if( $this->validateCredential($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) ) {
      return TRUE;
    }
    return FALSE;

  }

  public function validateCredential($username, $password) {
    if( ! array_key_exists($username, $this->passwd) ) {
      return FALSE;
    }
    $ret = preg_match("/^(..)/", $this->passwd[$username], $salt);
    if( $ret == 0 ) {
      return FALSE;
    }
    if( $this->passwd[$username] == crypt($password, $salt[0]) ) {
      return TRUE;
    }
    return FALSE;
  }

  public function logout($redir_uri) {
    setcookie('logout', 'true', 0);
    header('Location: '.$redir_uri);
    return TRUE;
  }
  
}

?>
