<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Close session
 *
 * @author Anakeen
 * @version $Id: logout.php,v 1.12 2008/06/24 16:05:51 jerome Exp $
 * @package FDL
 * @subpackage CORE
 * @deprecated since HTTP Authentification
 */
/**
 */
include_once ('WHAT/Class.AuthenticatorManager.php');
/**
 * Close session
 *
 */
function logout(&$action)
{
    global $_SERVER;
    global $_POST;
    
    $action->session->close();
    AuthenticatorManager::closeAccess();
    exit(0);
}
?>
