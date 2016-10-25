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
    
    const openAuthorizationScheme = "DcpOpen";
    const openGetId = "dcpopen-authorization";
    private $privatelogin = false;
    public $token;
    public $auth_session = null;
    /**
     * no need to ask authentication
     */
    public function checkAuthentication()
    {
        include_once ('WHAT/Lib.Http.php');
        
        $privatekey = static::getTokenId();
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
    
    public static function getTokenId()
    {
        $tokenId = getHttpVars(self::openGetId, getHttpVars("privateid"));
        if (!$tokenId) {
            $headers = apache_request_headers();
            if (!empty($headers["Authorization"])) {
                
                if (preg_match(sprintf("/%s\\s+(.*)$/", self::openAuthorizationScheme) , $headers["Authorization"], $reg)) {
                    $tokenId = trim($reg[1]);
                }
            }
        }
        return $tokenId;
    }
    
    public static function getLoginFromPrivateKey($privatekey)
    {
        include_once ('WHAT/Class.UserToken.php');
        include_once ('WHAT/Class.User.php');
        
        $token = static::getUserToken($privatekey);
        if ($token === false) {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " " . sprintf("Token '%s' not found.", $privatekey));
            return false;
        }
        
        $uid = $token->userid;
        $user = new Account('', $uid);
        if (!is_object($user) || !$user->isAffected()) {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " " . sprintf("Could not get user with uid '%s' for token '%s'.", $uid, $privatekey));
            return false;
        }
        
        if (!static::verifyOpenAccess($token)) {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " " . sprintf("Access deny for user '%s' with token '%s' : context not match.", $user->login, $privatekey));
            
            return false;
        }
        
        if (!static::verifyOpenExpire($token)) {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " " . sprintf("Access deny for user '%s' with token '%s' : token has expired.", $user->login, $privatekey));
            
            return false;
        }
        
        return $user->login;
    }
    public static function getUserToken($tokenId)
    {
        
        $token = new UserToken('', $tokenId);
        if (!is_object($token) || !$token->isAffected()) {
            
            return false;
        }
        
        return $token;
    }
    
    public static function verifyOpenExpire(\UserToken $token)
    {
        $expiredate = $token->expire;
        if ($expiredate === "infinity") {
            return true;
        }
        $date = new \DateTime($expiredate);
        $now = new \DateTime();
        
        return $now <= $date;
    }
    public static function verifyOpenAccess(\UserToken $token)
    {
        $rawContext = $token->context;
        
        $allow = false;
        
        if ($token->type && $token->type !== "CORE") {
            return false;
        }
        
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
            $this->auth_session = new Session(Session::PARAMNAME, false);
            
            $this->auth_session->Set();
        }
        return $this->auth_session;
    }
}
