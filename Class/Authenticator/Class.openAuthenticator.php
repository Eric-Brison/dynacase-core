<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * openAuthenticator class
 *
 * This class provides methods for private key based authentification
 *
 * @author Anakeen 2009
 * @version $Id:  $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
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
        
        return Authenticator::AUTH_OK;
    }
    
    public function getLoginFromPrivateKey($privatekey)
    {
        include_once ('WHAT/Class.UserToken.php');
        include_once ('WHAT/Class.User.php');
        
        $token = new UserToken('', $privatekey);
        if (!is_object($token) || !$token->isAffected()) {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " " . sprintf("Token '%s' not found.", $privatekey));
            return false;
        }
        
        $uid = $token->userid;
        $user = new User('', $uid);
        if (!is_object($user) || !$user->isAffected()) {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " " . sprintf("Could not get user with uid '%s' for token '%s'.", $uid, $privatekey));
            return false;
        }
        
        return $user->login;
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
        return TRUE;
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
    public function logout($redir_uri)
    {
        header("HTTP/1.0 401 Authorization Required ");
        print _("private key is not valid");
        return true;
    }
    /**
     **
     **
     *
     */
    public function setSessionVar($name, $value)
    {
        include_once ('WHAT/Class.Session.php');
        $session_auth = new Session($this->parms{'cookie'});
        if (array_key_exists($this->parms{'cookie'}, $_COOKIE)) {
            $session_auth->Set($_COOKIE[$this->parms{'cookie'}]);
        } else {
            $session_auth->Set();
        }
        
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
        include_once ('WHAT/Class.Session.php');
        $session_auth = new Session($this->parms{'cookie'});
        if (array_key_exists($this->parms{'cookie'}, $_COOKIE)) {
            $session_auth->Set($_COOKIE[$this->parms{'cookie'}]);
        } else {
            $session_auth->Set();
        }
        
        return $session_auth->read($name);
    }
}
?>
