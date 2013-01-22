<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * htmlAuthenticator class
 *
 * This class provides methods for HTML form based authentication
 *
 * @author Anakeen
 * @version $Id: Class.htmlAuthenticator.php,v 1.8 2009/01/16 13:33:00 jerome Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */
include_once ('WHAT/Class.Authenticator.php');

class htmlAuthenticator extends Authenticator
{
    
    public $auth_session = null;
    /*
     * Store the current authenticating user
    */
    private $username = '';
    /**
     **
     **
     *
     */
    public function checkAuthentication()
    {
        $session = $this->getAuthSession();
        
        $this->username = $session->read('username');
        if ($this->username != "") return Authenticator::AUTH_OK;
        
        if (!array_key_exists($this->parms{'username'}, $_POST)) return Authenticator::AUTH_ASK;
        if (!array_key_exists($this->parms{'password'}, $_POST)) return Authenticator::AUTH_ASK;
        
        $this->username = getHttpVars($this->parms{'username'});
        if (is_callable(array(
            $this->provider,
            'validateCredential'
        ))) {
            if (!$this->provider->validateCredential(getHttpVars($this->parms{'username'}) , getHttpVars($this->parms{'password'}))) {
                return Authenticator::AUTH_NOK;
            }
            
            if (!$this->freedomUserExists(getHttpVars($this->parms{'username'}))) {
                if (!$this->tryInitializeUser(getHttpVars($this->parms{'username'}))) {
                    return Authenticator::AUTH_NOK;
                }
            }
            $session->register('username', getHttpVars($this->parms{'username'}));
            $session->setuid(getHttpVars($this->parms{'username'}));
            return Authenticator::AUTH_OK;
        }
        
        error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "Error: " . get_class($this->provider) . " must implement function validateCredential()");
        return Authenticator::AUTH_NOK;
    }
    /**
     * retrieve authentication session
     * @return Session the session object
     */
    public function getAuthSession()
    {
        if (!$this->auth_session) {
            include_once ('WHAT/Class.Session.php');
            $this->auth_session = new Session($this->parms{'cookie'});
            if (array_key_exists($this->parms{'cookie'}, $_COOKIE)) {
                $this->auth_session->Set($_COOKIE[$this->parms{'cookie'}]);
            } else {
                $this->auth_session->Set();
            }
        }
        return $this->auth_session;
    }
    /**
     **
     **
     *
     */
    function checkAuthorization($opt)
    {
        if (is_callable(array(
            $this->provider,
            'validateAuthorization'
        ))) {
            return $this->provider->validateAuthorization($opt);
        }
        return TRUE;
    }
    /**
     **
     **
     *
     */
    public function askAuthentication($args)
    {
        if (empty($args)) $args = array();
        $parsed_referer = parse_url(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "");
        
        $referer_uri = "";
        if ($parsed_referer['path'] != "") {
            $referer_uri.= $parsed_referer['path'];
        }
        if (!empty($parsed_referer['query'])) {
            $referer_uri.= "?" . $parsed_referer['query'];
        }
        if (!empty($parsed_referer['fragment'])) {
            $referer_uri.= "#" . $parsed_referer['fragment'];
        }
        $session = $this->getAuthSession();
        /* Force removal of username if it already exists on the session */
        $session->register('username', '');
        $session->setuid(ANONYMOUS_ID);
        //     error_log("referer_uri = ".$referer_uri." / REQUEST_URI = ".$_SERVER['REQUEST_URI']);
        if ($referer_uri == "") {
            //       error_log("Setting fromuri = ".$_SERVER['REQUEST_URI']);
            $session->register('fromuri', $_SERVER['REQUEST_URI']);
        } else if ($session->read('fromuri') == "" && $referer_uri != $_SERVER['REQUEST_URI']) {
            //       error_log("Setting fromuri = ".$_SERVER['REQUEST_URI']);
            $session->register('fromuri', $_SERVER['REQUEST_URI']);
        }
        
        $location = $this->getAuthUrl($args);
        header(sprintf('Location: %s', $location));
        return TRUE;
    }

    /**
     * return url used to connect user
     * @param array $extendedArg
     * @throws Dcp\Exception
     * @return string
     */
    public function getAuthUrl(array $extendedArg = array())
    {
        if (!isset($this->parms['auth']['app'])) {
            throw new \Dcp\Exception("Missing html/auth/app config.");
        }
        $location = 'authent.php?app=' . $this->parms['auth']['app'];
        if (isset($this->parms['auth']['action'])) {
            $location.= '&action=' . $this->parms['auth']['action'];
        }
        if (isset($this->parms['auth']['args'])) {
            $location.= '&' . $this->parms['auth']['args'];
        }
        $sargs = '';
        foreach ($extendedArg as $k => $v) {
            $sargs.= sprintf("&%s=%s", $k, urlencode($v));
        }
        return $location . $sargs;
    }
    /**
     * ask authentication and redirect
     * @param string $uri uri to redirect after connection
     */
    public function connectTo($uri)
    {
        $this->getAuthSession()->register('fromuri', $uri);
        header(sprintf('Location: %s', $this->getAuthUrl()));
        exit(0);
    }
    /**
     **
     **
     *
     */
    public function getAuthUser()
    {
        $session_auth = $this->getAuthSession();
        $username = $session_auth->read('username');
        if ($username != '') {
            return $username;
        }
        return $this->username;
    }
    /**
     **
     **
     *
     */
    public function getAuthPw()
    {
        return null;
    }
    /**
     **
     **
     *
     */
    public function logout($redir_uri = '')
    {
        $session_auth = $this->getAuthSession();
        if (array_key_exists($this->parms{'cookie'}, $_COOKIE)) {
            //       error_log("Closing auth session for cookie : ".$this->parms{'cookie'});
            $session_auth->close();
        }
        if ($redir_uri == "") {
            if (isset($this->parms['auth']['app'])) {
                header('Location: ' . $this->getAuthUrl());
                return TRUE;
            }
            $redir_uri = GetParam("CORE_BASEURL");
        }
        header('Location: ' . $redir_uri);
        return TRUE;
    }
    /**
     **
     **
     *
     */
    public function setSessionVar($name, $value)
    {
        $session_auth = $this->getAuthSession();
        $session_auth->register($name, $value);
        
        return $session_auth->read($name);
    }
    /**
     **
     **
     *
     */
    public function getSessionVar($name)
    {
        $session_auth = $this->getAuthSession();
        return $session_auth->read($name);
    }
    
    public function logon()
    {
        include_once ('WHAT/Class.ActionRouter.php');
        include_once ('WHAT/Class.Account.php');
        
        $app = $this->getAuthApp();
        if ($app === false || $app == '') {
            throw new \Dcp\Exception("Missing or empty auth app definition.");
        }
        
        $account = new Account();
        if ($account->setLoginName("anonymous") === false) {
            throw new \Dcp\Exception(sprintf("anonymous account not found."));
        }
        $actionRouter = new ActionRouter($account);
        
        $allowList = array(
            array(
                'app' => 'AUTHENT'
            ) ,
            array(
                'app' => 'CORE',
                'action' => 'CORE_CSS'
            )
        );
        $action = $actionRouter->getAction();
        $app = $action->parent;
        $allowed = false;
        foreach ($allowList as $allow) {
            if (isset($allow['app']) && $allow['app'] == $app->name) {
                if (!isset($allow['action']) || $allow['action'] == $action->name) {
                    $allowed = true;
                    break;
                }
            }
        }
        if (!$allowed) {
            throw new \Dcp\Exception(sprintf("Unauthorized app '%s' with action '%s' for authentication with '%s'.", $action->parent->name, $action->name, get_class($this)));
        }
        
        $actionRouter->executeAction();
    }

}
