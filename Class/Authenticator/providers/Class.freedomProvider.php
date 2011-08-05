<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * freedomProvider class
 *
 * This class provides methods for autentication based on freeedom
 * @author Anakeen 2009
 * @version $Id:  $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 */
include_once ("WHAT/Class.Provider.php");

class freedomProvider extends Provider
{
    
    public function validateCredential($username, $password)
    {
        $dbh = pg_connect($this->parms{'connection'});
        if ($dbh == FALSE) {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "Error: failed connection to database");
            $this->errno = 0;
            return FALSE;
        }
        $stmt = pg_prepare($dbh, "check_bug_639", "SELECT login FROM users WHERE login = \$1 AND password !~ '^[a-zA-Z0-9./]{2}'");
        if ($stmt == FALSE) {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "Error: pg_preapare(check_bug_639) returned false");
            $this->errno = 0;
            return FALSE;
        }
        $res = pg_execute($dbh, "check_bug_639", array(
            $username
        ));
        if ($res == FALSE) {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "Error: pg_execute(check_bug_639) returned false. User $username not found ?");
            $this->errno = 0;
            return FALSE;
        }
        if (pg_num_rows($res) > 0) {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "Error: found bug #639 for user '$username'");
            $this->errno = Provider::ERRNO_BUG_639;
            return FALSE;
        }
        
        $stmt = pg_prepare($dbh, "get_password", 'SELECT password FROM users WHERE login = $1');
        if ($stmt == FALSE) {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "Error: pg_prepare(get_password) returned false");
            $this->errno = 0;
            return FALSE;
        }
        $res = pg_execute($dbh, "get_password", array(
            $username
        ));
        if ($res == FALSE) {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "Error: pg_execute(get_password) returned false. User $username not found ?");
            $this->errno = 0;
            return FALSE;
        }
        $encrypted_password = pg_fetch_result($res, 0);
        if ($encrypted_password == "") {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "Error: User $username not found");
            $this->errno = 0;
            return FALSE;
        }
        $ret = preg_match("/^(..)/", $encrypted_password, $salt);
        if ($ret == 0) {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "Error: could not get salt from encrypted password for user $username");
            $this->errno = 0;
            return FALSE;
        }
        if ($encrypted_password == crypt($password, $salt[0])) {
            $this->errno = 0;
            return TRUE;
        }
        error_log(__CLASS__ . "::" . __FUNCTION__ . " " . sprintf("Password mismatch for user %s", $username));
        $this->errno = 0;
        return FALSE;
    }
    
    public function validateAuthorization($opt)
    {
        if (!array_key_exists('username', $opt)) {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "Missing username key in opt array");
            $this->errno = 0;
            return FALSE;
        }
        $dbh = pg_connect($this->parms{'connection'});
        if ($dbh == FALSE) {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "Error connecting to database");
            $this->errno = 0;
            return FALSE;
        }
        $stmt = pg_prepare($dbh, "get_status", 'SELECT status FROM users WHERE login = $1');
        if ($stmt == FALSE) {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "Error preparing select statement");
            $this->errno = 0;
            return FALSE;
        }
        $res = pg_execute($dbh, "get_status", array(
            $opt['username']
        ));
        if ($res == FALSE) {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "Error in result of get_status");
            $this->errno = 0;
            return FALSE;
        }
        $status = pg_fetch_result($res, 0);
        if ($status == 'D') {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " " . "Account " . $opt['username'] . " has been suspended");
            $this->errno = 0;
            return FALSE;
        }
        $this->errno = 0;
        return TRUE;
    }
}
?>
