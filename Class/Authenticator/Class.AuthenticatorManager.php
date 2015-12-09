<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Authenticator manager class
 *
 * Manage authentification method (classes)
 *
 * @author Anakeen
 * @version $Id: Class.Authenticator.php,v 1.6 2009/01/16 13:33:00 jerome Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ('WHAT/Lib.Common.php');
include_once ('WHAT/Class.Authenticator.php');
include_once ('WHAT/Class.Session.php');
include_once ('WHAT/Class.User.php');
include_once ('WHAT/Class.Log.php');

class AuthenticatorManager
{
    /**
     * @var \Session
     */
    public static $session = null;
    const AccessBug = - 1;
    const AccessOk = 0;
    const AccessHasNoLocalAccount = 1;
    const AccessMaxLoginFailure = 2;
    const AccessAccountIsNotActive = 3;
    const AccessAccountHasExpired = 4;
    const AccessNotAuthorized = 5;
    const NeedAsk = 6;
    /**
     * @var Authenticator|htmlAuthenticator
     */
    public static $auth = null;
    public static $provider_errno = 0;
    
    public static function checkAccess($authtype = null, $noask = false)
    {
        /*
         * Part 1: check authentication
        */
        $error = self::AccessOk;
        self::$provider_errno = 0;
        if ($authtype == null) $authtype = getAuthType();
        if (!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $authtype)) {
            header('HTTP/1.0 500 Invalid authtype');
            print sprintf("Invalid authtype '%s'", $authtype);
            exit;
        }
        $authClass = strtolower($authtype) . "Authenticator";
        $authFile = 'WHAT/Class.' . $authClass . '.php';
        if (!file_exists($authFile)) {
            header('HTTP/1.0 500 Unknown authtype');
            print "Unknown authtype " . $authtype;
            exit;
        }
        include_once ($authFile);
        self::$auth = new $authClass($authtype, "__for_logout__");
        
        $authProviderList = getAuthProviderList();
        $status = false;
        foreach ($authProviderList as $authProvider) {
            self::$auth = new $authClass($authtype, $authProvider);
            $status = self::$auth->checkAuthentication();
            if ($status === Authenticator::AUTH_ASK) {
                if ($noask) {
                    return self::NeedAsk;
                } else {
                    self::$auth->askAuthentication(array());
                    exit(0);
                }
            }
            if ($status === Authenticator::AUTH_OK) {
                break;
            }
        }
        
        if ($status === Authenticator::AUTH_NOK) {
            $error = 1;
            $providerErrno = self::$auth->getProviderErrno();
            if ($providerErrno != 0) {
                self::$provider_errno = $providerErrno;
                switch ($providerErrno) {
                    case Provider::ERRNO_BUG_639:
                        // User must change his password
                        $error = self::AccessBug;
                        break;
                }
            }
            $remote_addr = isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : "";
            $auth_user = isset($_REQUEST["auth_user"]) ? $_REQUEST["auth_user"] : "";
            $http_user_agent = isset($_SERVER["HTTP_USER_AGENT"]) ? $_SERVER["HTTP_USER_AGENT"] : "";
            self::secureLog("failure", "invalid credential", self::$auth->provider->parms['type'] . "/" . self::$auth->provider->parms['provider'], $remote_addr, $auth_user, $http_user_agent);
            // count login failure
            if (getParam("AUTHENT_FAILURECOUNT") > 0) {
                $wu = new Account();
                if ($wu->SetLoginName(self::$auth->getAuthUser())) {
                    if ($wu->id != 1) {
                        include_once ("FDL/freedom_util.php");
                        /**
                         * @var \Dcp\Family\IUSER $du
                         */
                        $du = new_Doc(getDbAccess() , $wu->fid);
                        if ($du->isAlive()) {
                            $du->disableEditControl();
                            $du->increaseLoginFailure();
                            $du->enableEditControl();
                        }
                    }
                }
            }
            self::clearGDocs();
            return $error;
        }
        // Authentication success
        /*
         * Part 2: check authorization
        */
        $ret = self::checkAuthorization();
        if ($ret !== self::AccessOk) {
            return $ret;
        }
        
        $login = AuthenticatorManager::$auth->getAuthUser();
        /*
         * All authenticators are not necessarily based on sessions (i.e. 'basic')
        */
        if (method_exists(self::$auth, 'getAuthSession')) {
            self::$session = self::$auth->getAuthSession();
            /**
             * @var self::$session Session
             */
            if (self::$session->read('username') == "") {
                self::secureLog("failure", "username should exists in session", $authprovider = "", $_SERVER["REMOTE_ADDR"], $login, $_SERVER["HTTP_USER_AGENT"]);
                exit(0);
            }
        }
        
        self::clearGDocs();
        return self::AccessOk;
    }
    
    public static function closeAccess()
    {
        $authtype = getAuthType();
        $authClass = strtolower($authtype) . "Authenticator";
        $authFile = 'WHAT/Class.' . $authClass . '.php';
        if (!file_exists($authFile)) {
            header('HTTP/1.0 500 Unknown authtype');
            print "Unknown authtype " . $_GET['authtype'];
            exit;
        }
        include_once ($authFile);
        self::$auth = new $authClass($authtype, "__for_logout__");
        
        if (method_exists(self::$auth, 'logout')) {
            if (is_object(self::$auth->provider)) {
                self::secureLog("close", "see you tomorrow", self::$auth->provider->parms['type'] . "/" . self::$auth->provider->parms['provider'], $_SERVER["REMOTE_ADDR"], self::$auth->getAuthUser() , $_SERVER["HTTP_USER_AGENT"]);
            } else {
                self::secureLog("close", "see you tomorrow");
            }
            self::$auth->logout(null);
            exit(0);
        }
        
        header('HTTP/1.0 500 Internal Error');
        print sprintf("logout method not supported by authtype '%s'", $authtype);
        exit(0);
    }
    /**
     * Send a 401 Unauthorized HTTP header
     */
    public function authenticate(&$action)
    {
        //   Header( "WWW-Authenticate: Basic realm=\"WHAT Connection\", stale=FALSE");
        //Header( "WWW-Authenticate: Basic realm=\"WHAT Connection\", stale=true");
        //Header( "HTTP/1.0 401 Unauthorized");
        header('WWW-Authenticate: Basic realm="' . getParam("CORE_REALM", "Dynacase Platform connection") . '"');
        header('HTTP/1.0 401 Unauthorized');
        echo _("Vous devez entrer un nom d'utilisateur valide et un mot de passe correct pour acceder a cette ressource");
        exit;
    }
    
    public static function secureLog($status = "", $additionalMessage = "", $provider = "", $clientIp = "", $account = "", $userAgent = "")
    {
        global $_GET;
        $log = new Log("", "Session", "Authentication");
        $facility = constant(getParam("AUTHENT_LOGFACILITY", "LOG_AUTH"));
        $log->wlog("S", sprintf("[%s] [%s] [%s] [%s] [%s] [%s]", $status, $additionalMessage, $provider, $clientIp, $account, $userAgent) , NULL, $facility);
        return 0;
    }
    
    public static function clearGDocs()
    {
        \Dcp\Core\SharedDocuments::clear();
    }
    
    public static function getAccount()
    {
        $login = self::$auth->getAuthUser();
        $account = new Account();
        if ($account->setLoginName($login)) {
            return $account;
        }
        return false;
    }
    /**
     * Get Provider's protocol version.
     *
     * @param Provider $provider
     * @return int version (0, 1, etc.)
     */
    public static function _getProviderProtocolVersion(Provider $provider)
    {
        if (!isset($provider->PROTOCOL_VERSION)) {
            return 0;
        }
        return $provider->PROTOCOL_VERSION;
    }
    /**
     * Main authorization check entry point
     *
     * @return int
     * @throws \Dcp\Exception
     */
    private static function checkAuthorization()
    {
        $login = AuthenticatorManager::$auth->getAuthUser();
        $wu = new Account();
        $existu = false;
        if ($wu->SetLoginName($login)) {
            $existu = true;
        }
        
        if (!$existu) {
            AuthenticatorManager::secureLog("failure", "login have no Dynacase account", AuthenticatorManager::$auth->provider->parms['type'] . "/" . AuthenticatorManager::$auth->provider->parms['provider'], $_SERVER["REMOTE_ADDR"], $login, $_SERVER["HTTP_USER_AGENT"]);
            return AuthenticatorManager::AccessHasNoLocalAccount;
        }
        
        $protoVersion = self::_getProviderProtocolVersion(self::$auth->provider);
        if (!is_integer($protoVersion)) {
            throw new \Dcp\Exception(sprintf("Invalid provider protocol version '%s' for provider '%s'.", $protoVersion, get_class(self::$auth->provider)));
        }
        
        switch ($protoVersion) {
            case 0:
                return self::protocol_0_authorization(array(
                    'username' => $login,
                    'dcp_account' => $wu
                ));
                break;
        }
        throw new \Dcp\Exception(sprintf("Unsupported provider protocol version '%s' for provider '%s'.", $protoVersion, get_class(self::$auth->provider)));
    }
    /**
     * Protocol 0: check only Dynacase's authorization.
     *
     * @param array $opt
     * @return int
     */
    private static function protocol_0_authorization($opt)
    {
        $authz = self::checkProviderAuthorization($opt);
        if ($authz !== self::AccessOk) {
            return $authz;
        }
        return self::checkDynacaseAuthorization($opt);
    }
    /**
     * Check Provider's authorization.
     *
     * @param array $opt
     * @return int
     */
    private static function checkProviderAuthorization($opt)
    {
        $authz = self::$auth->checkAuthorization($opt);
        if ($authz === true) {
            return self::AccessOk;
        }
        return self::AccessNotAuthorized;
    }
    /**
     * Check Dynacase's authorization.
     *
     * @param array $opt
     * @throws \Dcp\Exception
     * @return int
     */
    private static function checkDynacaseAuthorization($opt)
    {
        $login = $opt['username'];
        $wu = $opt['dcp_account'];
        if ($wu->id != 1) {
            
            include_once ("FDL/freedom_util.php");
            /**
             * @var \Dcp\Family\IUSER $du
             */
            $du = new_Doc(getDbAccess() , $wu->fid);
            // First check if account is active
            if (!$du->isAccountActive()) {
                AuthenticatorManager::secureLog("failure", "inactive account", AuthenticatorManager::$auth->provider->parms['type'] . "/" . AuthenticatorManager::$auth->provider->parms['provider'], $_SERVER["REMOTE_ADDR"], $login, $_SERVER["HTTP_USER_AGENT"]);
                AuthenticatorManager::clearGDocs();
                return AuthenticatorManager::AccessAccountIsNotActive;
            }
            // check if the account expiration date is elapsed
            if ($du->accountHasExpired()) {
                AuthenticatorManager::secureLog("failure", "account has expired", AuthenticatorManager::$auth->provider->parms['type'] . "/" . AuthenticatorManager::$auth->provider->parms['provider'], $_SERVER["REMOTE_ADDR"], $login, $_SERVER["HTTP_USER_AGENT"]);
                AuthenticatorManager::clearGDocs();
                return AuthenticatorManager::AccessAccountHasExpired;
            }
            // check count of login failure
            $maxfail = getParam("AUTHENT_FAILURECOUNT");
            if ($maxfail > 0 && $du->getRawValue("us_loginfailure", 0) >= $maxfail) {
                AuthenticatorManager::secureLog("failure", "max connection (" . $maxfail . ") attempts exceeded", AuthenticatorManager::$auth->provider->parms['type'] . "/" . AuthenticatorManager::$auth->provider->parms['provider'], $_SERVER["REMOTE_ADDR"], $login, $_SERVER["HTTP_USER_AGENT"]);
                AuthenticatorManager::clearGDocs();
                return AuthenticatorManager::AccessMaxLoginFailure;
            }
            // authen OK, max login failure OK => reset count of login failure
            $du->disableEditControl();
            $du->resetLoginFailure();
            $du->enableEditControl();
        }
        
        return AuthenticatorManager::AccessOk;
    }
}
