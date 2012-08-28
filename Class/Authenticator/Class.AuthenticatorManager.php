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

abstract class AuthenticatorManager
{
    
    public static $session = null;
    const NeedAsk = 6;
    const AccessOk = 0;
    const AccessBug = - 1;
    /**
     * @var Authenticator
     */
    public static $auth = null;
    public static $provider_errno = 0;
    
    public static function checkAccess($authtype = null, $noask = false)
    {
        $error = self::AccessOk;
        self::$provider_errno = 0;
        if ($authtype == null) $authtype = getAuthType();
        if ($authtype == 'apache') {
            // Apache has already handled the authentication
            return self::AccessOk;
        } else {
            if (!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $authtype)) {
                print sprintf("Invalid authtype '%s'", $authtype);
                exit;
            }
            $authClass = strtolower($authtype) . "Authenticator";
            if (!@include_once ('WHAT/Class.' . $authClass . '.php')) {
                print "Unknown authtype " . $_GET['authtype'];
                exit;
            }
            $auth = new $authClass($authtype, "__for_logout__");
        }
        
        $authProviderList = getAuthProviderList();
        $status = false;
        foreach ($authProviderList as $authProvider) {
            self::$auth = new $authClass($authtype, $authProvider);
            $status = self::$auth->checkAuthentication();
            if ($status === Authenticator::AUTH_ASK) {
                if ($noask) {
                    return self::NeedAsk;
                } else {
                    self::$auth->askAuthentication();
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
            self::secureLog("failure", "invalid credential", self::$auth->provider->parms['type'] . "/" . self::$auth->provider->parms['provider'], $_SERVER["REMOTE_ADDR"], $_REQUEST["auth_user"], $_SERVER["HTTP_USER_AGENT"]);
            // count login failure
            if (getParam("AUTHENT_FAILURECOUNT") > 0) {
                $wu = new Account();
                if ($wu->SetLoginName(self::$auth->getAuthUser())) {
                    if ($wu->id != 1) {
                        include_once ("FDL/freedom_util.php");
                        $du = new_Doc(getParam("FREEDOM_DB") , $wu->fid);
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
        $login = self::$auth->getAuthUser();
        $wu = new Account();
        $existu = false;
        if ($wu->SetLoginName($login)) {
            $existu = true;
        }
        
        if (!$existu) {
            self::secureLog("failure", "login have no Dynacase account", self::$auth->provider->parms['type'] . "/" . self::$auth->provider->parms['provider'], $_SERVER["REMOTE_ADDR"], $login, $_SERVER["HTTP_USER_AGENT"]);
            return 1;
        }
        
        if ($wu->id != 1) {
            
            include_once ("FDL/freedom_util.php");
            $du = new_Doc(getParam("FREEDOM_DB") , $wu->fid);
            // First check if account is active
            if ($du->isAccountInactive()) {
                self::secureLog("failure", "inactive account", self::$auth->provider->parms['type'] . "/" . self::$auth->provider->parms['provider'], $_SERVER["REMOTE_ADDR"], $login, $_SERVER["HTTP_USER_AGENT"]);
                self::clearGDocs();
                return 3;
            }
            // check if the account expiration date is elapsed
            if ($du->accountHasExpired()) {
                self::secureLog("failure", "account has expired", self::$auth->provider->parms['type'] . "/" . self::$auth->provider->parms['provider'], $_SERVER["REMOTE_ADDR"], $login, $_SERVER["HTTP_USER_AGENT"]);
                self::clearGDocs();
                return 4;
            }
            // check count of login failure
            $maxfail = getParam("AUTHENT_FAILURECOUNT");
            if ($maxfail > 0 && $du->getValue("us_loginfailure", 0) >= $maxfail) {
                self::secureLog("failure", "max connection (" . $maxfail . ") attempts exceeded", self::$auth->provider->parms['type'] . "/" . self::$auth->provider->parms['provider'], $_SERVER["REMOTE_ADDR"], $login, $_SERVER["HTTP_USER_AGENT"]);
                self::clearGDocs();
                return 2;
            }
            // authen OK, max login failure OK => reset count of login failure
            $du->disableEditControl();
            $du->resetLoginFailure();
            $du->enableEditControl();
        }
        /*
         * All authenticators are not necessarily based on sessions (i.e. 'basic')
        */
        if (method_exists(self::$auth, 'getAuthSession')) {
            self::$session = self::$auth->getAuthSession();
            if (self::$session->read('username') == "") {
                self::secureLog("failure", "username should exists in session", $authprovider, $_SERVER["REMOTE_ADDR"], $login, $_SERVER["HTTP_USER_AGENT"]);
                exit(0);
            }
        }
        
        self::clearGDocs();
        return self::AccessOk;
    }
    
    public function closeAccess()
    {
        $authtype = getAuthType();
        if ($authtype == 'apache') {
            AuthenticatorManager::secureLog("close", "see you tomorrow", "apache/apache", $_SERVER["REMOTE_ADDR"], $_SERVER["PHP_AUTH_USER"], $_SERVER["HTTP_USER_AGENT"]);
            global $action;
            if ($action) {
                $rapp = GetHttpVars("rapp");
                $raction = GetHttpVars("raction");
                $rurl = GetHttpVars("rurl", $action->GetParam("CORE_ROOTURL"));
                
                if (!isset($_SERVER['PHP_AUTH_USER']) || ($_POST["SeenBefore"] == 1 && !strcmp($_POST["OldAuth"], $_SERVER['PHP_AUTH_USER']))) {
                    self::authenticate($action);
                } else {
                    redirect($action, $rapp, $raction, $rurl);
                }
            }
            exit(0);
        } else {
            $authClass = strtolower($authtype) . "Authenticator";
            if (!@include_once ('WHAT/Class.' . $authClass . '.php')) {
                print "Unknown authtype " . $_GET['authtype'];
                exit;
            }
            $auth = new $authClass($authtype, "__for_logout__");
            
            if (method_exists(AuthenticatorManager::$auth, 'logout')) {
                AuthenticatorManager::secureLog("close", "see you tomorrow", AuthenticatorManager::$auth->provider->parms['type'] . "/" . AuthenticatorManager::$auth->provider->parms['provider'], $_SERVER["REMOTE_ADDR"], AuthenticatorManager::$auth->getAuthUser() , $_SERVER["HTTP_USER_AGENT"]);
                AuthenticatorManager::$auth->logout();
                exit(0);
            }
            
            header('HTTP/1.0 500 Internal Error');
            print sprintf("logout method not supported by authtype '%s'", $authtype);
            exit(0);
        }
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
        // Header("Location:guest.php");
        echo _("Vous devez entrer un nom d'utilisateur valide et un mot de passe correct pour acceder a cette ressource");
        exit;
    }
    
    public function secureLog($status = "", $additionalMessage = "", $provider = "", $clientIp = "", $account = "", $userAgent = "")
    {
        global $_GET;
        $log = new Log("", "Session", "Authentication");
        $facility = constant(getParam("AUTHENT_LOGFACILITY", "LOG_AUTH"));
        $log->wlog("S", sprintf("[%s] [%s] [%s] [%s] [%s] [%s]", $status, $additionalMessage, $provider, $clientIp, $account, $userAgent) , NULL, $facility);
        return 0;
    }
    
    private static function clearGDocs()
    {
        global $gdocs;
        $gdocs = array();
    }
}
?>