<?php

/**
 * basicAuthenticator class
 *
 * This class provides methods for HTTP Basic authentication
 *
 * @author Anakeen 2009
 * @version $Id: Class.basicAuthenticator.php,v 1.3 2009/01/16 13:33:00 jerome Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage
 */
 /**
 */

Class openAuthenticator {
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
  

  /**
   * no need to ask authentication
   */
  public function checkAuthentication() {    
    return true;
  }

  public function checkAuthorization($opt) {
    if( is_callable(array($this->provider, 'checkAuthorization')) ) {
      return $this->provider->checkAuthorization($opt);
    }

    return false;
  }
  
  /**
   * no ask
   */
  public function askAuthentication() {    
    return TRUE;
  }
  
  public function getAuthUser() {
    if( is_callable(array($this->provider, 'getAuthUser')) ) {
      return $this->provider->getAuthUser();
    }

    return "";
  }
  
  /**
   * no password needed
   */
  public function getAuthPw() {
    return false;
  }
  
  /**
   * no logout
   */
  public function logout($redir_uri) {           
    header("HTTP/1.0 401 Authorization Required ");
    print _("private key is not valid");
    return true;  
  }

}

?>
