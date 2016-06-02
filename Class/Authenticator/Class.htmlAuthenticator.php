<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * htmlAuthenticator class
 *
 * This class provides methods for HTML form based authentication
 *
 * @author Anakeen
 * @version $Id: Class.htmlAuthenticator.php,v 1.8 2009/01/16 13:33:00 jerome Exp $
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
        
        if (!array_key_exists($this->parms['username'], $_POST)) return Authenticator::AUTH_ASK;
        if (!array_key_exists($this->parms['password'], $_POST)) return Authenticator::AUTH_ASK;
        
        $this->username = getHttpVars($this->parms['username']);
        if (is_callable(array(
            $this->provider,
            'validateCredential'
        ))) {
            if (!$this->provider->validateCredential(getHttpVars($this->parms['username']) , getHttpVars($this->parms{'password'}))) {
                return Authenticator::AUTH_NOK;
            }
            
            if (!$this->freedomUserExists(getHttpVars($this->parms['username']))) {
                if (!$this->tryInitializeUser(getHttpVars($this->parms['username']))) {
                    return Authenticator::AUTH_NOK;
                }
            }
            $session->register('username', getHttpVars($this->parms['username']));
            $session->setuid(getHttpVars($this->parms['username']));
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
            $this->auth_session = new Session(Session::PARAMNAME);
            if (array_key_exists(Session::PARAMNAME, $_COOKIE)) {
                $this->auth_session->Set($_COOKIE[Session::PARAMNAME]);
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
        $session = $this->getAuthSession();
        /* Force removal of username if it already exists on the session */
        $session->register('username', '');
        $session->setuid(Account::ANONYMOUS_ID);
        if (!isset($args['redirect_uri'])) {
            $args['redirect_uri'] = $_SERVER['REQUEST_URI'];
        }
        
        header(sprintf('Location: %s', $this->getAuthUrl($args)));
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
        if (empty($this->parms['auth']['app'])) {
            throw new \Dcp\Exception("Missing html/auth/app config.");
        }
        $location = Session::getWebRootPath();
        $location.= 'authent.php?app=' . $this->parms['auth']['app'];
        if (!empty($this->parms['auth']['action'])) {
            $location.= '&action=' . $this->parms['auth']['action'];
        }
        if (!empty($this->parms['auth']['args'])) {
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
        $location = sprintf('%s&redirect_uri=%s', $this->getAuthUrl() , urlencode($uri));
        header(sprintf('Location: %s', $location));
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
        include_once ('WHAT/Class.Session.php');
        $session_auth = $this->getAuthSession();
        if (array_key_exists(Session::PARAMNAME, $_COOKIE)) {
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
