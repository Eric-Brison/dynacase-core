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
    
    const nullProvider = "__for_logout__";
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
        $ta = self::getAuthTypeParams();
        if ($authprovider != self::nullProvider) {
            $tp = self::getAuthParam($authprovider);
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
            //     error_log("Using authentication provider [".$providerClass."]");
            $this->provider = new $providerClass($authprovider, $this->parms);
            if (!is_a($this->provider, 'Provider')) {
                throw new Dcp\Exception(__METHOD__ . " " . sprintf("Error: provider with class '%s' does not inherits from class 'Provider'.", $providerClass));
            }
        } else {
            $this->parms = array_merge($tx, $ta);
        }
    }
    public static function getAuthParam($provider = "")
    {
        if ($provider == "") return array();
        $freedom_providers = getDbAccessValue('freedom_providers');
        if (!is_array($freedom_providers)) {
            return array();
        }
        
        if (!array_key_exists($provider, $freedom_providers)) {
            error_log(__FUNCTION__ . ":" . __LINE__ . "provider " . $provider . " does not exists in freedom_providers");
            return array();
        }
        
        return $freedom_providers[$provider];
    }
    
    public static function getAuthTypeParams()
    {
        $freedom_authtypeparams = getDbAccessValue('freedom_authtypeparams');
        if (!is_array($freedom_authtypeparams)) {
            throw new Dcp\Exception('FILE0006');
        }
        
        if (!array_key_exists(AuthenticatorManager::getAuthType() , $freedom_authtypeparams)) {
            return array();
        }
        
        return $freedom_authtypeparams[AuthenticatorManager::getAuthType() ];
    }
    
    public static function freedomUserExists($username)
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
