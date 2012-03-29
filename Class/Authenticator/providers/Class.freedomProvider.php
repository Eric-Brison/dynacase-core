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
    /**
     * checks user login and password
     *
     * @param string $username user login
     * @param string $password user password
     * @return bool true if ok
     */
    public function validateCredential($username, $password)
    {
        $user = new User();
        if ($user->setLoginName($username)) {
            return $user->checkpassword($password);
        }
        return FALSE;
    }
    /**
     * checks if a user can't connect to Dynacase
     *
     * @return bool true if ok
     */
    public function validateAuthorization($opt)
    {
        if (array_key_exists('username', $opt)) {
            $user = new User();
            if ($user->setLoginName($opt['username'])) return ($user->status != 'D');
        }
        return FALSE;
    }
}
?>
