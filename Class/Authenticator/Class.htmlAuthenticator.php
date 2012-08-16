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
            if (!$this->provider->validateCredential(getHttpVars($this->parms{'username'}), getHttpVars($this->parms{'password'}))) {
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
     * retrieve authentification session
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
    public function askAuthentication($args = array())
    {
        
        $parsed_referer = parse_url($_SERVER['HTTP_REFERER']);
        
        $referer_uri = "";
        if ($parsed_referer['path'] != "") {
            $referer_uri.= $parsed_referer['path'];
        }
        if ($parsed_referer['query'] != "") {
            $referer_uri.= "?" . $parsed_referer['query'];
        }
        if ($parsed_referer['fragment'] != "") {
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
        
        if (array_key_exists('authurl', $this->parms)) {
            $sargs = '';
            foreach ($args as $k => $v) $sargs.= sprintf("&%s=%s", $k, urlencode($v));
            
            $location = '';
            if (substr($this->parms{'authurl'}, 0, 9) == "guest.php") {
                $dirname = dirname($_SERVER["SCRIPT_NAME"]);
                $location = str_replace('//', '/', $dirname . '/' . $this->parms{'authurl'});
                if (strpos($location, '?') === false && $sargs != '') {
                    $sargs = sprintf('?%s', $sargs);
                }
            } else {
                $location = $this->parms{'authurl'};
                if (strpos($location, '?') === false && $sargs != '') {
                    $sargs = sprintf('?%s', $sargs);
                }
            }
            
            header(sprintf('Location: %s%s', $location, $sargs));
            return TRUE;
        }
        
        error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "Error: no authurl of askAuthentication() method defined for " . $this->parms{'type'} . $this->parms{'provider'} . "Provider");
        return FALSE;
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
            if (array_key_exists('authurl', $this->parms)) {
                header('Location: ' . $this->parms['authurl']);
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
}
?>
