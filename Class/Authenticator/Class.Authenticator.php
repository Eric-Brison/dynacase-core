<?php

/**
 * Authenticator class
 *
 * Top-level class to authenticate and authorize users
 *
 * @author Anakeen 2009
 * @version $Id: Class.Authenticator.php,v 1.6 2009/01/16 13:33:00 jerome Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package WHAT
 * @subpackage
 */
 /**
 */

abstract class Authenticator {
  
  public function __construct($authtype, $authprovider) {
    
    include_once('WHAT/Lib.Common.php');

    if ($authtype=="")       
      throw new Exception(__CLASS__."::".__FUNCTION__." "."Error: authentication mode not set");
    if ($authprovider=="")       
      throw new Exception(__CLASS__."::".__FUNCTION__." "."Error: authentication provider not set");

    
    $tx = array( 'type' => $authtype, 'provider' => $authprovider );
    $ta = getAuthTypeParams();
    if ($authprovider!="__for_logout__") {
      $tp = getAuthParam("", $authprovider);
      $this->parms = array_merge($tx, $ta, $tp);
      
      if( ! array_key_exists('provider', $this->parms) ) {
	throw new Exception(__CLASS__."::".__FUNCTION__." "."Error: provider parm not specified at __construct");
      }
      $providerClass = $this->parms{'provider'}.'Provider';
      $ret = @include_once('WHAT/Class.'.$providerClass.'.php');
      if( $ret === FALSE ) {
	throw new Exception(__CLASS__."::".__FUNCTION__." "."Error: WHAT/Class.".$providerClass.".php not found");
      }
      if( ! class_exists($providerClass) ) {
	throw new Exception(__CLASS__."::".__FUNCTION__." "."Error: ".$providerClass." class not found");
      }
      global $action;
      //     error_log("Using authentication provider [".$providerClass."]");
      $this->provider = new $providerClass($authprovider, $this->parms);
    } else {
      $this->parms = array_merge($tx, $ta);
    }
  }

  public function freedomUserExists($username) {
    @include_once('FDL/Class.Doc.php');
    @include_once('WHAT/Class.User.php');
    
    $u = new User();
    if( $u->SetLoginName($username) ) {
      $dbaccess=GetParam("FREEDOM_DB");    	 
      $du = new_Doc($dbaccess, $u->fid);
      if( $du->isAlive() ) {
	return TRUE;
      }
    }
    return FALSE;
  }

  public function tryInitializeUser($username) {
    if( ! $this->provider->canICreateUser() ) {
      error_log(__CLASS__."::".__FUNCTION__." ".sprintf("Authentication failed for user '%s' because auto-creation is disabled for provider '%s'!", $username, $this->provider->pname));
      return FALSE;
    }
    $err = $this->provider->initializeUser($username);
    if( $err != "" ) {
      error_log(__CLASS__."::".__FUNCTION__." ".sprintf("Error creating user '%s' err=[%s]", $username, $err));
      return FALSE;
    }
    error_log(__CLASS__."::".__FUNCTION__." ".sprintf("Initialized user '%s'!", $username));
    return TRUE;
  }

  public function getProviderErrno() {
    if( $this->provider ) {
      return $this->provider->errno;
    }
    return 0;
  }

  abstract function checkAuthentication();
  abstract function checkAuthorization($opt);
  abstract function askAuthentication();
  abstract function getAuthUser();
  abstract function getAuthPw();
  abstract function logout($redir_uri);
  abstract function setSessionVar($name, $value);
  abstract function getSessionVar($name);

}

?>
