<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * freedomProvider class
 *
 * This class provides methods for autentication based on freeedom
 * @author Anakeen
 * @version $Id:  $
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
}
