<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * openAuthenticator class
 *
 * This class provides methods for private key based authentification
 *
 * @author Anakeen
 * @version $Id:  $
 * @package FDL
 * @subpackage
 */
/**
 */
include_once ('WHAT/Class.Authenticator.php');

class openAuthenticator extends Authenticator
{
    
    private $privatelogin = false;
    public $token;
    public $auth_session = null;
    /**
     * no need to ask authentication
     */
    public function checkAuthentication()
    {
        include_once ('WHAT/Lib.Http.php');
        
        $privatekey = getHttpVars("privateid");
        if (!$privatekey) return Authenticator::AUTH_NOK;
        
        $this->privatelogin = $this->getLoginFromPrivateKey($privatekey);
        if ($this->privatelogin === false) {
            return Authenticator::AUTH_NOK;
        }
        
        $err = $this->consumeToken($privatekey);
        if ($err === false) {
            return Authenticator::AUTH_NOK;
        }
        
        $session = $this->getAuthSession();
        $session->register('username', $this->getAuthUser());
        $session->setuid($this->getAuthUser());
        return Authenticator::AUTH_OK;
    }
    
    public static function getLoginFromPrivateKey($privatekey)
    {
        include_once ('WHAT/Class.UserToken.php');
        include_once ('WHAT/Class.User.php');
        
        $token = new UserToken('', $privatekey);
        if (!is_object($token) || !$token->isAffected()) {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " " . sprintf("Token '%s' not found.", $privatekey));
            return false;
        }
        
        $uid = $token->userid;
        $user = new Account('', $uid);
        if (!is_object($user) || !$user->isAffected()) {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " " . sprintf("Could not get user with uid '%s' for token '%s'.", $uid, $privatekey));
            return false;
        }
        
        if (!self::verifyOpenAccess($token->context)) {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " " . sprintf("Access deny for user '%s' with token '%s' : context not match.", $user->login, $privatekey));
            
            return false;
        }
        
        if (!self::verifyOpenExpire($token->expire)) {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " " . sprintf("Access deny for user '%s' with token '%s' : token has expired.", $user->login, $privatekey));
            
            return false;
        }
        
        return $user->login;
    }
    
    public static function verifyOpenExpire($expiredate)
    {
        if ($expiredate === "infinity") {
            return true;
        }
        $date = new \DateTime($expiredate);
        $now = new \DateTime();
        
        return $now <= $date;
    }
    public static function verifyOpenAccess($rawContext)
    {
        $allow = false;
        if ($rawContext === null) {
            return true;
        }
        
        if ($rawContext) {
            $context = unserialize($rawContext);
            if (is_array($context)) {
                $allow = true;
                foreach ($context as $k => $v) {
                    if (getHttpVars($k) !== (string)$v) {
                        $allow = false;
                    }
                }
            }
        }
        
        return $allow;
    }
    
    public function consumeToken($privatekey)
    {
        include_once ('WHAT/Class.UserToken.php');
        
        $token = new UserToken('', $privatekey);
        if (!is_object($token) || !$token->isAffected()) {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " " . sprintf("Token '%s' not found.", $privatekey));
            return false;
        }
        
        $this->token = $token->getValues();
        if ($token->expendable === 't') {
            $token->delete();
        }
        
        return $privatekey;
    }
    
    public function checkAuthorization($opt)
    {
        return TRUE;
    }
    /**
     * no ask
     */
    public function askAuthentication($args)
    {
        header("HTTP/1.0 403 Forbidden", true);
        print ___("Private key identifier is not valid", "authentOpen");
        
        return true;
    }
    
    public function getAuthUser()
    {
        return $this->privatelogin;
    }
    /**
     * no password needed
     */
    public function getAuthPw()
    {
        return false;
    }
    /**
     * no logout
     */
    public function logout($redir_uri = '')
    {
        header("HTTP/1.0 401 Authorization Required");
        print ___("Authorization Required", "authentOpen");
        return true;
    }
    /**
     **
     **
     *
     */
    public function setSessionVar($name, $value)
    {
        $session = $this->getAuthSession();
        $session->register($name, $value);
        return $session->read($name);
    }
    /**
     **
     **
     *
     */
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
            include_once ('WHAT/Class.Session.php');
            $this->auth_session = new Session(Session::PARAMNAME, false);
            if (array_key_exists(Session::PARAMNAME, $_COOKIE)) {
                $this->auth_session->Set($_COOKIE[Session::PARAMNAME]);
            } else {
                $this->auth_session->Set();
            }
        }
        return $this->auth_session;
    }
}
