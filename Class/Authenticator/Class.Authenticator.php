<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Authenticator class
 *
 * Top-level class to authenticate and authorize users
 *
 * @author Anakeen
 * @version $Id: Class.Authenticator.php,v 1.6 2009/01/16 13:33:00 jerome Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

abstract class Authenticator
{
    /* Authentication success */
    const AUTH_OK = 0;
    /* Authentication failed */
    const AUTH_NOK = 1;
    /* Authentication status cannot be determined, and credentials should be asked */
    const AUTH_ASK = 2;
    /**
     * @var Provider
     */
    public $provider = null;
    
    public function __construct($authtype, $authprovider)
    {
        
        include_once ('WHAT/Lib.Common.php');
        
        if ($authtype == "") throw new Dcp\Exception(__METHOD__ . " " . "Error: authentication mode not set");
        if ($authprovider == "") throw new Dcp\Exception(__METHOD__ . " " . "Error: authentication provider not set");
        
        $tx = array(
            'type' => $authtype,
            'provider' => $authprovider
        );
        $ta = getAuthTypeParams();
        if ($authprovider != "__for_logout__") {
            $tp = getAuthParam("", $authprovider);
            $this->parms = array_merge($tx, $ta, $tp);
            
            if (!array_key_exists('provider', $this->parms)) {
                throw new Dcp\Exception(__METHOD__ . " " . "Error: provider parm not specified at __construct");
            }
            $providerClass = $this->parms{'provider'} . 'Provider';
            
            $classFile = 'WHAT/Class.' . $providerClass . '.php';
            $ret = file_exists($classFile);
            if ($ret === FALSE) {
                throw new Dcp\Exception(__METHOD__ . " " . "Error: ." . $classFile . " not found");
            }
            include_once ($classFile);
            if (!class_exists($providerClass)) {
                throw new Dcp\Exception(__METHOD__ . " " . "Error: " . $providerClass . " class not found");
            }
            global $action;
            //     error_log("Using authentication provider [".$providerClass."]");
            $this->provider = new $providerClass($authprovider, $this->parms);
            if (!is_a($this->provider, 'Provider')) {
                throw new Dcp\Exception(__METHOD__ . " " . sprintf("Error: provider with class '%s' does not inherits from class 'Provider'.", $providerClass));
            }
        } else {
            $this->parms = array_merge($tx, $ta);
        }
    }
    
    public function freedomUserExists($username)
    {
        include_once ('FDL/Class.Doc.php');
        include_once ('WHAT/Class.User.php');
        
        $u = new Account();
        if ($u->SetLoginName($username)) {
            $dbaccess = getDbAccess();
            $du = new_Doc($dbaccess, $u->fid);
            if ($du->isAlive()) {
                return TRUE;
            }
        }
        return FALSE;
    }
    
    public function tryInitializeUser($username)
    {
        if (!$this->provider->canICreateUser()) {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " " . sprintf("Authentication failed for user '%s' because auto-creation is disabled for provider '%s'!", $username, $this->provider->pname));
            return FALSE;
        }
        $err = $this->provider->initializeUser($username);
        if ($err != "") {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " " . sprintf("Error creating user '%s' err=[%s]", $username, $err));
            return FALSE;
        }
        error_log(__CLASS__ . "::" . __FUNCTION__ . " " . sprintf("Initialized user '%s'!", $username));
        return TRUE;
    }
    
    public function getProviderErrno()
    {
        if ($this->provider) {
            return $this->provider->errno;
        }
        return 0;
    }
    
    public function getAuthApp()
    {
        if (isset($this->parms['auth']['app'])) {
            return $this->parms['auth']['app'];
        }
        return false;
    }
    
    abstract function checkAuthentication();
    abstract function checkAuthorization($opt);
    abstract function askAuthentication($args);
    abstract function getAuthUser();
    abstract function getAuthPw();
    abstract function logout($redir_uri = '');
    abstract function setSessionVar($name, $value);
    abstract function getSessionVar($name);
}
