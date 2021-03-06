<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * User api access
 *
 * @author Anakeen 2009
 * @version $Id:  $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("DATA/Class.User.php");
/**
 * Display info before download
 * @param Action &$action current action
 * @global id Http var : document for file to edit (SIMPLEFILE family)
 */
function user(&$action)
{
    $id = getHttpVars("id");
    $method = getHttpVars("method");
    $err = "";
    
    $out = false;
    switch (strtolower($method)) {
        case '':
            $ou = new Fdl_User($action->user);
            $out = $ou->getUser();
            break;

        case 'ping':
            $out->status = 'ok';
            $out->time = time();
            break;

        case 'authent':
            $login = getHttpVars("login");
            $password = getHttpVars("password");
            $u = new User();
            if ($u->setLoginname($login)) {
                include_once ('WHAT/Class.htmlAuthenticator.php');
                
                $authProviderList = getAuthProviderList();
                foreach ($authProviderList as $provider) {
                    $auth = new htmlAuthenticator(getAuthType() , $provider);
                    $_POST[$auth->parms{'username'}] = $login;
                    $_POST[$auth->parms{'password'}] = $password;
                    if ($auth->checkAuthentication() != Authenticator::AUTH_OK) {
                        $out->error = sprintf(_("authentication failed for %s") , $login);
                    } else {
                        $ou = new Fdl_User($u);
                        $out = $ou->getUser();
                        break;
                    }
                }
            } else {
                $out->error = sprintf(_("user %s not found") , $login);
            }
            break;

        default:
            $out->error = sprintf(_("method %s not defined") , $method);
    }
    
    $action->lay->template = json_encode($out);
}
?>