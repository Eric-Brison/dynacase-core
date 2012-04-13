<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * PHP Authentification control
 *
 * @author Anakeen 1999
 * @version $Id: loginform.php,v 1.17 2008/10/10 07:16:07 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage CORE
 * @deprecated since HTTP Authentification
 */
/**
 */
include_once ('Class.Session.php');
include_once ('Class.User.php');
include_once ('Class.QueryDb.php');
include_once ('Lib.Http.php');
/**
 * PHP Authentification control
 *
 */
function loginform(Action & $action)
{
    $action->parent->AddCssRef("AUTHENT:loginform.css", true);
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/resizeimg.js");
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/geometry.js");
    $ulang = GetHttpVars("lang");
    if ($ulang) {
        setLanguage($ulang);
        //$action->setparamu("CORE_LANG",$ulang);
        
    } else {
        $ulang = getParam('CORE_LANG');
    }
    $action->lay->set("isEnglish", substr($ulang, 0, 2) == "en");
    $error = GetHttpVars("error", 0);
    $merr = "";
    if ($error > 0) {
        switch ($error) {
            case 2:
                $merr = _("Too many incorrect password attempts.") . _(" Please, see your manager");
                break;

            case 3:
                $merr = _("This account is deactivated.") . _(" Please, see your manager");
                break;

            case 4:
                $merr = _("This account has expired.") . _(" Please, see your manager");
                break;

            default:
                $merr = _("auth_failure");
        }
    }
    $auth_user = GetHttpVars("auth_user");
    $auth_pass = GetHttpVars("auth_pass");
    $app_redir = GetHttpVars("appd", "CORE");
    $act_redir = GetHttpVars("actd", "");
    $arg_redir = GetHttpVars("argd", ""); // redirect url
    $domain = GetHttpVars("domain");
    
    $action->lay->set("app_redir", $app_redir);
    $action->lay->set("act_redir", $act_redir);
    $action->lay->set("arg_redir", $arg_redir);
    $action->lay->set("title", _("welcome"));
    $action->lay->set("auth_user", $auth_user);
    $action->lay->set("passfocus", ($auth_user !== "" ? true : false));
    $action->lay->set("error", $merr);
    
    $action->lay->set('authent_show_reqpasswd', $action->getParam('AUTHENT_SHOW_REQPASSWD') != 'no');
    
    $action->lay->set('authent_show_lang_selection', $action->getParam('AUTHENT_SHOW_LANG_SELECTION') != 'no');
    
    $lang = array();
    include_once ('CORE/lang.php');
    $lang_block = array();
    
    foreach ($lang as $k => $v) {
        $lang_block[$k]['LANG_VALUE'] = $k;
        $lang_block[$k]['LANG_LABEL'] = $lang[$k]['label'];
        $lang_block[$k]['LANG_IS_SELECTED'] = ($ulang == $k);
    }
    $action->lay->setBlockData('LANG', $lang_block);
    $action->parent->short_name = sprintf(_("%s Authentification") , $action->getParam("CORE_CLIENT"));
}
?>
