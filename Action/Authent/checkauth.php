<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * PHP Authentification control
 *
 * @author Anakeen
 * @package FDL
 * @subpackage CORE
 * @deprecated since HTTP Authentification
 */

function checkauth(Action & $action)
{
    
    include_once ('WHAT/Lib.Common.php');
    include_once ('WHAT/Class.AuthenticatorManager.php');
    include_once ('WHAT/Class.htmlAuthenticator.php');
    include_once ('WHAT/Class.User.php');
    include_once ('WHAT/Class.Log.php');
    
    $redirect_uri = GetHttpVars('redirect_uri', '');
    
    $status = AuthenticatorManager::checkAccess();
    //error_log("checkauth: AuthenticatorManager::checkAccess() = {$status}");
    switch ($status) {
        case AuthenticatorManager::AccessOk: // it'good, user is authentified, just log the connexion
            AuthenticatorManager::secureLog("success", "welcome", AuthenticatorManager::$auth->provider->parms['type'] . "/" . AuthenticatorManager::$auth->provider->parms['provider'], $_SERVER["REMOTE_ADDR"], AuthenticatorManager::$auth->getAuthUser() , $_SERVER["HTTP_USER_AGENT"]);
            break;

        case AuthenticatorManager::AccessBug:
            // User must change his password
            $action->session->close();
            global $_POST;
            Redirect($action, 'AUTHENT', 'ERRNO_BUG_639');
            exit(0);
            break;

        default:
            AuthenticatorManager::$auth->askAuthentication(array(
                'error' => $status,
                'auth_user' => $_POST['auth_user'],
                'redirect_uri' => $redirect_uri
            ));
            exit(0);
    }
    
    if (($redirect_uri == "") || (preg_match('/app=AUTHENT/', $redirect_uri))) {
        $redirect_uri = ".";
    } else if ($redirect_uri[0] != '/') {
        /*
         * $redirect_uri is normally constructed from REQUEST_URI, so
         * it should start with "/" and be a local absolute pathname.
         *
         * If it does not start with a "/", then it might indicate a
         * malicious manipulation to perform a cross-site redirect.
        */
        $redirect_uri = ".";
    }
    $lang = array();
    include_once ('CORE/lang.php');
    $core_lang = getHttpVars('CORE_LANG');
    if ($core_lang != "" && array_key_exists($core_lang, $lang)) {
        //     error_log(__CLASS__."::".__FUNCTION__." "."Registering vaviable CORE_LANG = '".$core_lang."' in session_auth");
        AuthenticatorManager::$session->register('CORE_LANG', $core_lang);
    }
    $redirect_uri = preg_replace('!//+!', '/', $redirect_uri);
    $redirect_uri = preg_replace('!&&+!', '&', $redirect_uri);
    // Redirect to initial page
    header('Location: ' . $redirect_uri);
    exit(0);
}
