<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 *  * Syntaxe :
 *  ---------
 *     $session = new Session();
*/

$CLASS_SESSION_PHP = '$Id: Class.Session.php,v 1.38 2009/01/12 15:15:31 jerome Exp $';
include_once ('Class.QueryDb.php');
include_once ('Class.DbObj.php');
include_once ('Class.Log.php');
include_once ('Class.User.php');
include_once ("Class.SessionCache.php");

class Session extends DbObj
{
    const SESSION_CT_CLOSE = 2;
    const SESSION_CT_ARGS = 3;
    const SESSION_MIN_BYTE_LENGTH = 16; /* 16 bytes = 128 bits */
    var $fields = array(
        "id",
        "userid",
        "name",
        "last_seen"
    );
    
    var $id_fields = array(
        "id"
    );
    public $id;
    public $userid;
    public $name;
    public $last_seen;
    public $status;
    private $sendCookie = true;
    var $dbtable = "sessions";
    
    var $sqlcreate = "create table sessions ( id text,
                        userid   int,
                        name text not null,
                        last_seen timestamp not null DEFAULT now() );
                  create unique index sessions_idx on sessions(id);
                  create index sessions_idx_userid on sessions(userid);";
    
    var $sessiondb;
    
    const PARAMNAME = 'dcpsession';
    var $session_name = self::PARAMNAME;
    function __construct($session_name = self::PARAMNAME, $sendCookie = true)
    {
        if (!empty($_SERVER['HTTP_HOST'])) {
            include_once ("config/sessionHandler.php");
        }
        parent::__construct();
        if ($session_name != '') $this->session_name = $session_name;
        $this->last_seen = strftime('%Y-%m-%d %H:%M:%S', time());
        $this->sendCookie = ($sendCookie === true);
    }
    
    function set($id = "")
    {
        global $_SERVER;
        
        if (!$this->sessionDirExistsAndIsWritable()) {
            return false;
        }
        
        $this->gcSessions();
        
        $query = new QueryDb($this->dbaccess, "Session");
        $query->addQuery("id = '" . pg_escape_string($id) . "'");
        $list = $query->Query(0, 0, "TABLE");
        $createNewSession = true;
        if ($query->nb != 0) {
            $this->Affect($list[0]);
            if (!$this->hasExpired()) {
                $createNewSession = false;
                $this->touch();
                session_name($this->session_name);
                session_id($id);
                @session_start();
                @session_write_close(); // avoid block
                
            }
        }
        
        if ($createNewSession) {
            $u = new Account();
            if ((!empty($_SERVER['PHP_AUTH_USER'])) && $u->SetLoginName($_SERVER['PHP_AUTH_USER'])) {
                $this->open($u->id);
            } else {
                $this->open(Account::ANONYMOUS_ID); //anonymous session
                
            }
        }
        // set cookie session
        if (!empty($_SERVER['HTTP_HOST'])) {
            if (empty($_SERVER["REDIRECT_URL"])) {
                $this->setCookieSession($this->id, $this->SetTTL());
            }
        }
        return true;
    }
    
    private static function getWebRootPath()
    {
        if (!isset($_SERVER['SCRIPT_FILENAME'])) {
            return false;
        }
        if (!isset($_SERVER['SCRIPT_NAME'])) {
            return false;
        }
        /*
         * Get absolute context's pathname (with trailing '/'):
         *
         *     "/var/www/foo/"
         *
        */
        $contextRoot = realpath(DEFAULT_PUBDIR);
        if ($contextRoot === false) {
            return false;
        }
        $contextRoot.= '/';
        /*
         *  Get absolute script's filename:
         *
         *     "/var/www/foo/bar/baz.php"
         *s
        */
        $scriptFilename = $_SERVER['SCRIPT_FILENAME'];
        /*
         * Remove leading context's pathname from script's filename:
         *
         *     "/var/www/foo/bar/baz.php" - "^/var/www/foo/" => "bar/baz.php"
         *
         * This gives us the script's filename relative to the context's root.
        */
        $pos = strpos($scriptFilename, $contextRoot);
        if ($pos !== 0) {
            return false;
        }
        $relativeScriptFilename = substr($scriptFilename, strlen($contextRoot));
        /*
         * Remove trailing relative script's filename from script's name by finding the
         * relative script's filename by the right :
         *
         *     "/x/y/z/bar/baz.php" - "bar/baz.php$" => "/x/y/z/"
         *
         * This gives us the Web root directory.
        */
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $pos = strrpos($scriptName, $relativeScriptFilename);
        $webRootLen = (strlen($scriptName) - strlen($relativeScriptFilename));
        if ($pos !== $webRootLen) {
            return false;
        }
        $webRoot = substr($scriptName, 0, $webRootLen);
        return $webRoot;
    }
    function setCookieSession($id, $ttl = 0)
    {
        $webRootPath = self::getWebRootPath();
        if ($webRootPath !== false) {
            $cookiePath = preg_replace(':/+:', '/', $webRootPath);
            $this->setcookie($this->name, $id, $ttl, $cookiePath, null, null, true);
        } else {
            $this->setcookie($this->name, $id, $ttl, null, null, null, true);
        }
    }
    /**
     * Closes session and removes all datas
     */
    function close()
    {
        global $_SERVER; // use only cache with HTTP
        if (!empty($_SERVER['HTTP_HOST'])) {
            session_name($this->name);
            session_id($this->id);
            @session_unset();
            @session_destroy();
            @session_write_close();
            // delete session cookie
            $this->setcookie($this->name, false, time() - 3600, null, null, null, true);
            $this->Delete();
        }
        $this->status = self::SESSION_CT_CLOSE;
        return $this->status;
    }
    /**
     * Closes all session
     */
    function closeAll($uid = null)
    {
        if ($uid === null) {
            $this->exec_query(sprintf("delete from sessions where name = '%s';", pg_escape_string($this->session_name)));
        } else {
            $this->exec_query(sprintf("delete from sessions where name = '%s' and userid=%d;", pg_escape_string($this->session_name) , $uid));
        }
        $this->status = self::SESSION_CT_CLOSE;
        return $this->status;
    }
    /**
     * Closes all user's sessions
     */
    function closeUsers($uid = - 1)
    {
        if (!$uid > 0) return '';
        $this->exec_query("delete from sessions where userid= '" . pg_escape_string($uid) . "'");
        $this->status = self::SESSION_CT_CLOSE;
        return $this->status;
    }
    
    function open($uid = Account::ANONYMOUS_ID)
    {
        $idsess = $this->newId();
        global $_SERVER; // use only cache with HTTP
        if (!empty($_SERVER['HTTP_HOST'])) {
            session_name($this->session_name);
            session_id($idsess);
            @session_start();
            @session_write_close(); // avoid block
            //	$this->initCache();
            
        }
        $this->name = $this->session_name;
        $this->id = $idsess;
        $this->userid = $uid;
        $this->last_seen = strftime('%Y-%m-%d %H:%M:%S', time());
        $this->Add();
        $this->log->debug("Nouvelle Session : {$this->id}");
    }
    // --------------------------------
    // Stocke une variable de session args
    // $v est une chaine !
    // --------------------------------
    function register($k = "", $v = "")
    {
        if ($k == "") {
            $this->status = self::SESSION_CT_ARGS;
            return $this->status;
        }
        global $_SERVER; // use only cache with HTTP
        if (!empty($_SERVER['HTTP_HOST'])) {
            session_name($this->name);
            session_id($this->id);
            @session_start();
            $_SESSION[$k] = $v;
            @session_write_close(); // avoid block
            
        }
        
        return true;
    }
    // --------------------------------
    // Récupère une variable de session
    // $v est une chaine !
    // --------------------------------
    function read($k = "", $d = "")
    {
        if (empty($_SERVER['HTTP_HOST'])) {
            return ($d);
        }
        /* Load session's data only once as requested by #4825 */
        $sessionOpened = false;
        if (!isset($_SESSION)) {
            session_name($this->name);
            session_id($this->id);
            @session_start();
            $sessionOpened = true;
        }
        if (isset($_SESSION[$k])) {
            $val = $_SESSION[$k];
        } else {
            $val = $d;
        }
        if ($sessionOpened) {
            @session_write_close();
        }
        return $val;
    }
    // --------------------------------
    // Détruit une variable de session
    // $v est une chaine !
    // --------------------------------
    function unregister($k = "")
    {
        global $_SERVER; // use only cache with HTTP
        if ($this->name && !empty($_SERVER['HTTP_HOST'])) {
            session_name($this->name);
            session_id($this->id);
            @session_start();
            unset($_SESSION[$k]);
            @session_write_close(); // avoid block
            
        }
        return true;
    }
    /**
     * Get, or generate, a "cache busting" key
     *
     * @param string $prefix
     * @return string
     */
    public function getUKey($prefix = '')
    {
        $uKey = $this->read('_uKey_', false);
        if ($uKey === false) {
            $uKey = uniqid($prefix);
            $this->register('_uKey_', $uKey);
        }
        return $uKey;
    }
    // ------------------------------------------------------------------------
    // utilities functions (private)
    // ------------------------------------------------------------------------
    function newId()
    {
        $this->log->debug("newId");
        $byteLength = (int)getParam('CORE_SESSION_BYTE_LENGTH');
        if ($byteLength < self::SESSION_MIN_BYTE_LENGTH) {
            $byteLength = self::SESSION_MIN_BYTE_LENGTH;
        }
        return self::randomId($byteLength);
    }
    /**
     * Get a new cryptographically strong random id
     *
     * Throws an exception if no cryptographically strong random bytes could be
     * obtained from openssl: this might occurs on broken or old system.
     *
     * @param int $byteLength The number of bytes to get from the CSPRNG
     * @return string The random bytes in hexadecimal representation (e.g. "a7d1f43b")
     * @throws \Dcp\Exception
     */
    private static function randomId($byteLength)
    {
        $strong = false;
        $bytes = openssl_random_pseudo_bytes($byteLength, $strong);
        if ($bytes === false || $strong === false) {
            throw new \Dcp\Exception(sprintf("Unable to get cryptographically strong random bytes from openssl: your system might be broken or too old."));
        }
        return bin2hex($bytes);
    }
    /**
     * replace value of global parameter in session cache
     * @param string $paramName
     * @param string $paramValue
     * @return bool
     */
    function replaceGlobalParam($paramName, $paramValue)
    {
        global $_SERVER; // use only cache with HTTP
        if (!empty($_SERVER['HTTP_HOST'])) {
            session_name($this->name);
            session_id($this->id);
            @session_start();
            foreach ($_SESSION as $k => $v) {
                if (preg_match("/^sessparam[0-9]+$/", $k)) {
                    if (isset($v[$paramName])) {
                        $_SESSION[$k][$paramName] = $paramValue;
                    }
                }
            }
            @session_write_close(); // avoid block
            
        }
        return true;
    }
    function setTTL()
    {
        $ttliv = $this->getSessionTTL(0);
        if ($ttliv > 0) {
            //$ttli->CloseConnect();
            return (time() + $ttliv);
        }
        return 0;
    }
    
    function getSessionTTL($default = 0, $ttlParamName = '')
    {
        if ($ttlParamName == '') {
            if ($this->userid == Account::ANONYMOUS_ID) {
                $ttlParamName = 'CORE_GUEST_SESSIONTTL';
            } else {
                $ttlParamName = 'CORE_SESSIONTTL';
            }
        }
        return intval(getParam($ttlParamName, $default));
    }
    
    function getSessionGcProbability($default = "0.01")
    {
        return getParam("CORE_SESSIONGCPROBABILITY", $default);
    }
    
    function touch()
    {
        $this->last_seen = strftime('%Y-%m-%d %H:%M:%S', time());
        $err = $this->modify();
        return $err;
    }
    
    function deleteUserExpiredSessions()
    {
        $ttl = $this->getSessionTTL(0, 'CORE_SESSIONTTL');
        if ($ttl > 0) {
            return $this->exec_query(sprintf("DELETE FROM sessions WHERE userid != %s AND last_seen < timestamp 'now()' - interval '%s seconds'", Account::ANONYMOUS_ID, pg_escape_string($ttl)));
        }
        return '';
    }
    
    function deleteGuestExpiredSessions()
    {
        $ttl = $this->getSessionTTL(0, 'CORE_GUEST_SESSIONTTL');
        if ($ttl > 0) {
            return $this->exec_query(sprintf("DELETE FROM sessions WHERE userid = %s AND last_seen < timestamp 'now()' - interval '%s seconds'", Account::ANONYMOUS_ID, pg_escape_string($ttl)));
        }
        return '';
    }
    
    function deleteMaxAgedSessions()
    {
        $maxage = getParam('CORE_SESSIONMAXAGE', '');
        if ($maxage != '') {
            return $this->exec_query(sprintf("DELETE FROM sessions WHERE last_seen < timestamp 'now()' - interval '%s'", pg_escape_string($maxage)));
        }
        return '';
    }
    
    function gcSessions()
    {
        $gcP = $this->getSessionGcProbability();
        if ($gcP <= 0) {
            return "";
        }
        $p = rand() / getrandmax();
        if ($p <= $gcP) {
            $err = $this->deleteUserExpiredSessions();
            if ($err != "") {
                error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "Error cleaning up user sessions: " . $err);
            }
            $err = $this->deleteGuestExpiredSessions();
            if ($err != "") {
                error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "Error cleaning up guest sessions: " . $err);
            }
            $err = $this->deleteMaxAgedSessions();
            if ($err != "") {
                error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "Error cleaning up max-aged sessions: " . $err);
            }
        }
        return "";
    }
    
    function setuid($uid)
    {
        if (!is_numeric($uid)) {
            $u = new Account();
            if ($u->SetLoginName($uid)) {
                $uid = $u->id;
            } else {
                $err = "Could not resolve login name '" . $uid . "' to uid";
                error_log(__CLASS__ . "::" . __FUNCTION__ . " " . $err);
                return $err;
            }
        }
        $this->userid = $uid;
        return $this->modify();
    }
    
    function sessionDirExistsAndIsWritable()
    {
        include_once ('WHAT/Lib.Prefix.php');
        
        global $pubdir;
        
        $sessionDir = sprintf("%s/var/session", $pubdir);
        if (!is_dir($sessionDir)) {
            trigger_error(sprintf("Session directory '%s' does not exists.", $sessionDir));
            return false;
        }
        
        if (!is_writable($sessionDir)) {
            trigger_error(sprintf("Session directory '%s' is not writable.", $sessionDir));
            return false;
        }
        
        return true;
    }
    
    function hasExpired()
    {
        include_once ('FDL/Lib.Util.php');
        $ttl = $this->getSessionTTL(0);
        if ($ttl > 0) {
            $now = time();
            $last_seen = stringDateToUnixTs($this->last_seen);
            if ($now > $last_seen + $ttl) {
                return true;
            }
        }
        return false;
    }
    
    function removeSessionFile($sessid = null)
    {
        include_once ('WHAT/Lib.Prefix.php');
        global $pubdir;
        if ($sessid === null) {
            $sessid = $this->id;
        }
        $sessionFile = sprintf("%s/var/session/sess_%s", $pubdir, $sessid);
        if (file_exists($sessionFile)) {
            unlink($sessionFile);
        }
    }
    /**
     * Delete all user's sessions except the current session.
     *
     * @param string $userId The user id (default is $this->userid)
     * @param string $exceptSessionId The session id to keep (default is $this->id)
     * @return string empty string on success, or the SQL error message
     */
    function deleteUserSessionsExcept($userId = '', $exceptSessionId = '')
    {
        if ($userId == '') {
            $userId = $this->userid;
        }
        if ($exceptSessionId == '') {
            $exceptSessionId = $this->id;
        }
        return $this->exec_query(sprintf("DELETE FROM sessions WHERE userid = %d AND id != '%s'", $userId, pg_escape_string($exceptSessionId)));
    }
    private function setcookie($name, $value = null, $expire = null, $path = null, $domain = null, $secure = null, $httponly = null)
    {
        if ($this->sendCookie) {
            return setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
        }
        return false;
    }
} // Class Session
