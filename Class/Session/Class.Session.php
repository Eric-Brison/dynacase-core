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
 * @package WHAT
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

Class Session extends DbObj
{
    
    var $fields = array(
        "id",
        "userid",
        "name"
    );
    
    var $id_fields = array(
        "id"
    );
    
    var $dbtable = "sessions";
    
    var $sqlcreate = "create table sessions ( id         varchar(100),
                        userid   int,
                        name text not null,
                        last_seen timestamp with time zone not null DEFAULT now() );
                  create index sessions_idx on sessions(id);";
    
    var $isCacheble = false;
    var $sessiondb;
    
    var $session_name = 'freedom_param';
    
    function __construct($session_name = 'freedom_param')
    {
        parent::__construct();
        if ($session_name != '') $this->session_name = $session_name;
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
        $query->addQuery("name = '" . pg_escape_string($this->session_name) . "'");
        $query->addQuery("last_seen > timestamp 'now' - interval '" . pg_escape_string($this->getSessionMaxAge()) . "'");
        $list = $query->Query(0, 0, "TABLE");
        if ($query->nb != 0) {
            $this->Affect($list[0]);
            $this->touch();
            session_name($this->session_name);
            session_id($id);
            @session_start();
            @session_write_close(); // avoid block
            //print_r2("@session_write_close");
            //	$this->initCache();
            
        } else {
            // Init the database with the app file if it exists
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
                setcookie($this->name, $this->id, $this->SetTTL());
            }
        }
        return true;
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
            setcookie($this->name, "", 0);
            $this->Delete();
        }
        $this->status = $this->SESSION_CT_CLOSE;
        return $this->status;
    }
    /** 
     * Closes all session
     */
    function CloseAll()
    {
        $this->exec_query("delete from sessions where name = '" . pg_escape_string($this->name) . "'");
        $this->status = $this->SESSION_CT_CLOSE;
        return $this->status;
    }
    /** 
     * Closes all user's sessions
     */
    function CloseUsers($uid = - 1)
    {
        if (!$uid > 0) return;
        $this->exec_query("delete from sessions where userid= '" . pg_escape_string($uid) . "'");
        $this->status = $this->SESSION_CT_CLOSE;
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
            $this->status = $this->SESSION_CT_ARGS;
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
    
    function getSessionMaxAge($default = "1 week")
    {
        $err = $this->exec_query("SELECT val FROM paramv WHERE name = 'CORE_SESSIONMAXAGE'");
        if ($err != "") {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "exec_query returned with error: " . $err);
            return $default;
        }
        if ($this->numrows() <= 0) {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "exec_query returned an empty result set");
            return $default;
        }
        $res = $this->fetch_array(0);
        if (is_numeric($res['val'])) {
            return $res['val'] . " seconds";
        }
        return $res['val'];
    }
    
    function getSessionMaxAgeSeconds($default = "1 week")
    {
        $session_maxage = $this->getSessionMaxAge($default);
        if (preg_match('/^(\d+)\s+(\w+)/i', $session_maxage, $m)) {
            $maxage = $m[1];
            $unit = strtolower($m[2]);
            switch (substr($unit, 0, 1)) {
                case 'y':
                    $maxage = $maxage * 364 * 24 * 60 * 60;
                    break; # years
                    
                case 'm':
                    if (substr($unit, 0, 2) == 'mo') {
                        $maxage = $maxage * 30 * 24 * 60 * 60;
                        break; # months
                        
                    } else {
                        $maxage = $maxage * 60;
                        break; # minutes
                        
                    }
                case 'w':
                    $maxage = $maxage * 7 * 24 * 60 * 60;
                    break; # weeks
                    
                case 'd':
                    $maxage = $maxage * 24 * 60 * 60;
                    break; # days
                    
                case 'h':
                    $maxage = $maxage * 60 * 60;
                    break; # hours
                    
                case 's':
                    break; # seconds
                    
                default:
                    return FALSE;
            }
            return $maxage;
        }
        return FALSE;
    }
    
    function getSessionTTL($default = 0)
    {
        $err = $this->exec_query("SELECT val FROM paramv WHERE name = 'CORE_SESSIONTTL'");
        if ($err != "") {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "exec_query returned with error: " . $err);
            return $default;
        }
        if ($this->numrows() <= 0) {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "exec_query returned an empty result set");
            return $default;
        }
        $res = $this->fetch_array(0);
        if (!is_numeric($res['val'])) {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "result value is not numeric");
            return $default;
        }
        return $res['val'];
    }
    
    function getSessionGcProbability($default = "0.01")
    {
        $err = $this->exec_query("SELECT val FROM paramv WHERE name = 'CORE_SESSIONGCPROBABILITY'");
        if ($err != "") {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "exec_query returned with error: " . $err);
            return $default;
        }
        if ($this->numrows() <= 0) {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "exec_query returned an empty result set");
            return $default;
        }
        $res = $this->fetch_array(0);
        if (!is_numeric($res['val'])) {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "result value is not numeric");
            return $default;
        }
        return $res['val'];
    }
    
    function touch()
    {
        $err = $this->exec_query("UPDATE sessions SET last_seen = now() WHERE id = '" . pg_escape_string($this->id) . "' AND name = '" . pg_escape_string($this->name) . "'");
        return $err;
    }
    
    function deleteExpiredSessions()
    {
        $err = $this->exec_query("DELETE FROM sessions WHERE last_seen < timestamp 'now()' - interval '" . pg_escape_string($this->getSessionMaxAge()) . "'");
        if ($err != "") {
            return $err;
        }
        
        return "";
    }
    
    function deleteExpiredSessionFiles()
    {
        include_once ('Lib.Prefix.php');
        
        global $pubdir;
        
        $session_maxage = $this->getSessionMaxAgeSeconds();
        if ($session_maxage === false) {
            $err = sprintf("Malformed CORE_SESSIONMAXAGE");
            return $err;
        }
        $maxage = time() - $session_maxage;
        
        $sessionDir = sprintf("%s/session", $pubdir);
        $dir = opendir($sessionDir);
        if ($dir === false) {
            $err = sprintf("Error opening directory '%s'.", $sessionDir);
            return $err;
        }
        
        $sessions = array();
        while ($file = readdir($dir)) {
            if (preg_match("/^sess_(.+)$/", $file, $m)) {
                $sessions[$m[1]] = sprintf("%s/%s", $sessionDir, $file);
            }
        }
        closedir($dir);
        
        if (count(array_keys($sessions)) <= 0) {
            return "";
        }
        
        foreach ($sessions as $sess_id => $sess_file) {
            $stat = @stat($sess_file);
            if ($stat === false) {
                $err = sprintf("Error stat(%s).", $sess_file);
                return $err;
            }
            if ($stat['mtime'] < $maxage) {
                @unlink($sess_file);
            }
        }
        
        return "";
    }
    
    function gcSessions()
    {
        $gcP = $this->getSessionGcProbability();
        if ($gcP <= 0) {
            return "";
        }
        $p = rand() / getrandmax();
        if ($p <= $gcP) {
            $err = $this->deleteExpiredSessions();
            if ($err != "") {
                error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "Error cleaning up sessions : " . $err);
                return $err;
            }
            $err = $this->deleteExpiredSessionFiles();
            if ($err != "") {
                error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "Error cleaning up session files : " . $err);
                return $err;
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
} // Class Session

?>
