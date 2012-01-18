<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000
 * @version $Id: Class.Session.php,v 1.38 2009/01/12 15:15:31 jerome Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage CORE
 */
/**
 */
// ---------------------------------------------------------------------------
// Marc Claverie (marc.claverie@anakeen.com)- anakeen 2000
// ---------------------------------------------------------------------------
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or (at
//  your option) any later version.
//
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
// or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License
// for more details.
//
// You should have received a copy of the GNU General Public License along
// with this program; if not, write to the Free Software Foundation, Inc.,
// 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
// ---------------------------------------------------------------------------
// $Id: Class.Session.php,v 1.38 2009/01/12 15:15:31 jerome Exp $
//
// ---------------------------------------------------------------------------
// Syntaxe :
// ---------
//     $session = new Session();
//
// ---------------------------------------------------------------------------
$CLASS_SESSION_PHP = '$Id: Class.Session.php,v 1.38 2009/01/12 15:15:31 jerome Exp $';
include_once ('Class.QueryDb.php');
include_once ('Class.DbObj.php');
include_once ('Class.Log.php');
include_once ('Class.User.php');
include_once ('Class.SessionConf.php');
include_once ("Class.SessionCache.php");

class Session extends DbObj
{
    const SESSION_CT_CLOSE = 2;
    const SESSION_CT_ARGS = 3;
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
    var $dbtable = "sessions";
    
    var $sqlcreate = "create table sessions ( id         varchar(100),
                        userid   int,
                        name text not null,
                        last_seen timestamp with time zone not null DEFAULT now() );
                  create unique index sessions_idx on sessions(id);
                  create index sessions_idx_name on sessions(name);
                  create index sessions_idx_userid on sessions(userid);";
    
    var $isCacheble = false;
    var $sessiondb;
    
    var $session_name = 'freedom_param';
    
    function __construct($session_name = 'freedom_param')
    {
        parent::__construct();
        if ($session_name != '') $this->session_name = $session_name;
        $this->last_seen = strftime('%d/%m/%Y %H:%M:%S %Z', time());
    }
    
    function Set($id = "")
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
            $u = new User();
            if ($u->SetLoginName($_SERVER['PHP_AUTH_USER'])) {
                $this->open($u->id);
            } else {
                $this->open(ANONYMOUS_ID); //anonymous session
                
            }
        }
        // set cookie session
        if ($_SERVER['HTTP_HOST'] != "") {
            if (!$_SERVER["REDIRECT_URL"]) {
                $this->setCookieSession($this->id, $this->SetTTL());
            }
        }
        return true;
    }
    
    function setCookieSession($id, $ttl = 0)
    {
        $turl = @parse_url($_SERVER["REQUEST_URI"]);
        if ($turl['path']) {
            if (substr($turl['path'], -1) != '/') {
                $path = dirname($turl['path']) . '/';
            } else {
                $path = $turl['path'];
            }
            $path = preg_replace(':/+:', '/', $path);
            setcookie($this->name, $id, $ttl, $path);
        } else {
            setcookie($this->name, $id, $ttl);
        }
    }
    /** 
     * Closes session and removes all datas
     */
    function Close()
    {
        global $_SERVER; // use only cache with HTTP
        if ($_SERVER['HTTP_HOST'] != "") {
            session_name($this->name);
            session_id($this->id);
            @session_unset();
            @session_destroy();
            @session_write_close();
            // delete session cookie
            setcookie($this->name, false, time() - 3600);
            $this->Delete();
        }
        $this->status = self::SESSION_CT_CLOSE;
        return $this->status;
    }
    /** 
     * Closes all session
     */
    function CloseAll()
    {
        $this->exec_query("delete from sessions where name = '" . pg_escape_string($this->name) . "'");
        $this->status = self::SESSION_CT_CLOSE;
        return $this->status;
    }
    /** 
     * Closes all user's sessions
     */
    function CloseUsers($uid = - 1)
    {
        if (!$uid > 0) return '';
        $this->exec_query("delete from sessions where userid= '" . pg_escape_string($uid) . "'");
        $this->status = self::SESSION_CT_CLOSE;
        return $this->status;
    }
    
    function Open($uid = ANONYMOUS_ID)
    {
        $idsess = $this->newId();
        global $_SERVER; // use only cache with HTTP
        if ($_SERVER['HTTP_HOST'] != "") {
            session_name($this->session_name);
            session_id($idsess);
            @session_start();
            @session_write_close(); // avoid block
            //	$this->initCache();
            
        }
        $this->name = $this->session_name;
        $this->id = $idsess;
        $this->userid = $uid;
        $this->last_seen = strftime('%d/%m/%Y %H:%M:%S %Z', time());
        $this->Add();
        $this->log->debug("Nouvelle Session : {$this->id}");
    }
    // --------------------------------
    // Stocke une variable de session args
    // $v est une chaine !
    // --------------------------------
    function Register($k = "", $v = "")
    {
        
        if ($k == "") {
            $this->status = self::SESSION_CT_ARGS;
            return $this->status;
        }
        //      global $_SESSION;
        //      $$k=$v;
        global $_SERVER; // use only cache with HTTP
        if ($_SERVER['HTTP_HOST'] != "") {
            //	session_register($k);
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
    function Read($k = "", $d = "")
    {
        if ($_SERVER['HTTP_HOST'] != "") {
            session_name($this->name);
            session_id($this->id);
            @session_start();
            if (isset($_SESSION[$k])) {
                $val = $_SESSION[$k];
                @session_write_close();
                return $val;
            } else {
                @session_write_close();
                return ($d);
            }
        }
        return ($d);
    }
    // --------------------------------
    // Détruit une variable de session
    // $v est une chaine !
    // --------------------------------
    function Unregister($k = "")
    {
        global $_SERVER; // use only cache with HTTP
        if ($_SERVER['HTTP_HOST'] != "") {
            session_name($this->name);
            session_id($this->id);
            @session_start();
            unset($_SESSION[$k]);
            @session_write_close(); // avoid block
            
        }
        return;
    }
    // ------------------------------------------------------------------------
    // utilities functions (private)
    // ------------------------------------------------------------------------
    function newId()
    {
        $this->log->debug("newId");
        $magic = new SessionConf($this->dbaccess, "MAGIC");
        $m = $magic->val;
        unset($magic);
        return md5(uniqid($m));
    }
    
    function SetTTL()
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
            if ($this->userid == ANONYMOUS_ID) {
                $ttlParamName = 'CORE_GUEST_SESSIONTTL';
            } else {
                $ttlParamName = 'CORE_SESSIONTTL';
            }
        }
        return getParam($ttlParamName, $default);
    }
    
    function getSessionGcProbability($default = "0.01")
    {
        return getParam("CORE_SESSIONGCPROBABILITY", $default);
    }
    
    function touch()
    {
        $this->last_seen = strftime('%d/%m/%Y %H:%M:%S %Z', time());
        $err = $this->modify();
        return $err;
    }
    
    function deleteUserExpiredSessions()
    {
        $ttl = $this->getSessionTTL(0, 'CORE_SESSIONTTL');
        if ($ttl > 0) {
            return $this->exec_query(sprintf("DELETE FROM sessions WHERE userid != %s AND last_seen < timestamp 'now()' - interval '%s seconds'", ANONYMOUS_ID, pg_escape_string($ttl)));
        }
        return '';
    }
    
    function deleteGuestExpiredSessions()
    {
        $ttl = $this->getSessionTTL(0, 'CORE_GUEST_SESSIONTTL');
        if ($ttl > 0) {
            return $this->exec_query(sprintf("DELETE FROM sessions WHERE userid = %s AND last_seen < timestamp 'now()' - interval '%s seconds'", ANONYMOUS_ID, pg_escape_string($ttl)));
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
        }
        return "";
    }
    
    function setuid($uid)
    {
        if (!is_numeric($uid)) {
            $u = new User();
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
        
        $sessionDir = sprintf("%s/session", $pubdir);
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
        $sessionFile = sprintf("%s/session/sess_%s", $pubdir, $sessid);
        if (file_exists($sessionFile)) {
            unlink($sessionFile);
        }
    }
} // Class Session

?>
