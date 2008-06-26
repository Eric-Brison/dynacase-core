<?php
/**
 * PHP Authentification control
 *
 * @author Anakeen 1999
 * @version $Id: loginform.php,v 1.10 2008/06/26 09:07:30 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 * @deprecated since HTTP Authentification
 */
/**
 */
include_once('Class.Session.php');
include_once('Class.User.php');
include_once('Class.Domain.php');
include_once('Class.QueryDb.php');
include_once('Lib.Http.php');

/**
 * PHP Authentification control
 *
 * @deprecated since HTTP Authentification
 */
function loginform(&$action) {
  $action->parent->AddCssRef("AUTHENT:loginform.css",true);
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/resizeimg.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/geometry.js");

    $error = GetHttpVars("error");
    if ($error) $error=_("auth_failure");
    $auth_user = GetHttpVars("auth_user");
    $auth_pass = GetHttpVars("auth_pass");
    $app_redir = GetHttpVars("appd","CORE");
    $act_redir = GetHttpVars("actd","");
    $arg_redir = GetHttpVars("argd",""); // redirect url
    $domain = GetHttpVars("domain");



    
    $action->lay->set("app_redir",$app_redir);
    $action->lay->set("act_redir",$act_redir);
    $action->lay->set("arg_redir",$arg_redir);
    $action->lay->set("title",_("welcome"));
    $action->lay->set("error",$error);

      
}
      
?>
