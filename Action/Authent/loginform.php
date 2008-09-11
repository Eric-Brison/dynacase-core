<?php
/**
 * PHP Authentification control
 *
 * @author Anakeen 1999
 * @version $Id: loginform.php,v 1.14 2008/09/11 12:24:01 eric Exp $
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
  $ulang=GetHttpVars("lang");
  if ($ulang)   setLanguage($ulang);
  else $ulang=getParam('CORE_LANG');

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
    $action->lay->set("auth_user",$auth_user);
    $action->lay->set("passfocus", ($auth_user!=="" ? true : false ));
    $action->lay->set("error",$error);

    if( $action->getParam('AUTHENT_SHOW_REQPASSWD') == 'yes' ) {
      $action->lay->set('AUTHENT_SHOW_REQPASSWD', True);
    } else {
      $action->lay->set('AUTHENT_SHOW_REQPASSWD', False);
    }

    if( $action->getParam('AUTHENT_SHOW_LANG_SELECTION') == 'yes' ) {
      $action->lay->set('AUTHENT_SHOW_LANG_SELECTION', True);
    } else {
      $action->lay->set('AUTHENT_SHOW_LANG_SELECTION', False);
    }
 
    include_once('CORE/lang.php');
    $lang_block = array();
  
    
    foreach( $lang as $k => $v ) {
      $lang_block[$k]['LANG_VALUE'] = $k;
      $lang_block[$k]['LANG_LABEL'] = $lang[$k]['label'];
      $lang_block[$k]['LANG_IS_SELECTED'] = ($ulang==$k);
    }
    $action->lay->setBlockData('LANG', $lang_block);

}
      
?>
