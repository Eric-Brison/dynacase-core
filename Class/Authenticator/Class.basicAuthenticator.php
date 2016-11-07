<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * basicAuthenticator class
 *
 * This class provides methods for HTTP Basic authentication
 *
 * @author Anakeen
 * @version $Id: Class.basicAuthenticator.php,v 1.3 2009/01/16 13:33:00 jerome Exp $
 * @package FDL
 * @subpackage
 */
/**
 */
include_once ('WHAT/Class.Authenticator.php');

class basicAuthenticator extends Authenticator
{
    
    const basicAuthorizationScheme = "Basic";
    protected $auth_session = null;
    public function checkAuthentication()
    {
        if (array_key_exists('logout', $_COOKIE) && $_COOKIE['logout'] == "true") {
            setcookie('logout', '', time() - 3600, null, null, null, true);
            return Authenticator::AUTH_ASK;
        }
        
        if (!array_key_exists('PHP_AUTH_USER', $_SERVER)) {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "Error: undefined _SERVER[PHP_AUTH_USER]");
            return Authenticator::AUTH_ASK;
        }
        
        if (!array_key_exists('PHP_AUTH_PW', $_SERVER)) {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "Error: undefined _SERVER[PHP_AUTH_PW] for user " . $_SERVER['PHP_AUTH_USER']);
            return Authenticator::AUTH_ASK;
        }
        
        if (!is_callable(array(
            $this->provider,
            'validateCredential'
        ))) {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "Error: " . $this->parms{'type'} . $this->parms{'provider'} . "Provider must implement validateCredential()");
            return Authenticator::AUTH_NOK;
        }
        
        if (!$this->provider->validateCredential($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])) {
            return Authenticator::AUTH_NOK;
        }
        
        if (!$this->freedomUserExists($_SERVER['PHP_AUTH_USER'])) {
            if (!$this->tryInitializeUser($_SERVER['PHP_AUTH_USER'])) {
                return Authenticator::AUTH_NOK;
            }
        }
        
        $session = $this->getAuthSession();
        $session->register('username', $this->getAuthUser());
        $session->setuid($this->getAuthUser());
        return Authenticator::AUTH_OK;
    }
    
    public function checkAuthorization($opt)
    {
        return TRUE;
    }
    
    public function askAuthentication($args)
    {
        header('HTTP/1.1 401 Authentication Required');
        header('WWW-Authenticate: Basic realm="' . $this->parms{'realm'} . '"');
        header('Connection: close');
        return TRUE;
    }
    
    public function getAuthUser()
    {
        return isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : null;
    }
    
    public function getAuthPw()
    {
        return isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : null;
    }
    
    public function logout($redir_uri = '')
    {
        setcookie('logout', 'true', 0, null, null, null, true);
        
        if ($redir_uri == '') {
            $pUri = parse_url($_SERVER['REQUEST_URI']);
            if (preg_match(':(?P<path>.*/)[^/]*$:', $pUri['path'], $m)) {
                $redir_uri = $m['path'];
            }
        }
        header('Location: ' . $redir_uri);
        return TRUE;
    }
    
    public function setSessionVar($name, $value)
    {
        $session = $this->getAuthSession();
        $session->register($name, $value);
        return $session->read($name);
    }
    public function getSessionVar($name)
    {
        $session = $this->getAuthSession();
        return $session->read($name);
    }
    /**
     *
     */
    public function getAuthSession()
    {
        if (!$this->auth_session) {
            $this->auth_session = new Session(Session::PARAMNAME, false);
            
            $this->auth_session->Set();
        }
        return $this->auth_session;
    }
}
