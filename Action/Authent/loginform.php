<?php
/**
 * PHP Authentification control
 *
 * @author Anakeen 1999
 * @version $Id: loginform.php,v 1.7 2008/06/11 14:11:26 jerome Exp $
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
    return true;

    global $_COOKIE;

    $auth_user = GetHttpVars("auth_user");
    $auth_pass = GetHttpVars("auth_pass");
    $app_redir = GetHttpVars("appd","CORE");
    $act_redir = GetHttpVars("actd","");
    $arg_redir = GetHttpVars("argd",""); // redirect url
    $domain = GetHttpVars("domain");

    $session = $action->session;

    switch ($session->status) {
    case $session->SESSION_CT_EXIST:
    case $session->SESSION_CT_ACTIVE:
      if ($auth_user != "") {
        if ($auth_user == "admin") $domain = 1;
        $u = new User();
        $auth = FALSE;
        $u->SetLogin($auth_user,$domain);
        // Send cookies for user and domain....
        setcookie("WHAT_USERNAME", $u->login);
        $d = new Domain('',$u->iddomain);
        setcookie("WHAT_USERDOMAIN", $d->name);
        if (isset($u->id)) {
          $auth = $u->checkpassword($auth_pass);
        }

        if (!$auth) {
          $action->log->debug("Unsuccessfull Authent");
          $action->lay->set("MESSAGE",$action->text("auth_failure"));
        } else {
          $session->activate($u->id);  
          $root=$action->parent->GetRootApp();
          $root->SetVolatileParam("CORE_BASEURL",$root->GetParam("CORE_PUBURL")."/index.php?session=".$session->id."&sole=R&");
	  
          redirect($action,$app_redir,$act_redir.$arg_redir,$action->GetParam("CORE_BASEURL"));
        }
        break;
      }

    default:
      if ($session->status == $session->SESSION_CT_TIMEOUT) {
        $action->lay->set("MESSAGE",$action->text("auth_expired"));
      } else {
        $session->DeActivate();
        $action->lay->set("MESSAGE",$action->text("auth_welcome"));
      }
    }


    
    $action->lay->set("app_redir",$app_redir);
    $action->lay->set("act_redir",$act_redir);
    $action->lay->set("arg_redir",$arg_redir);
    $action->lay->set("title",$action->text("welcome"));


    // in title, write action to go : case of lose session
    if ($app_redir != "CORE") {
      $appd = new Application();
      $appd = new Application("", $appd->GetIdFromName($app_redir));
      

      $actd =new Action();
      $actd->Set($act_redir, $appd);
      $action->lay->set("title",_($appd->description).">"._($actd->short_name));
      
    }
      

    $def_user = GetHttpCookie("WHAT_USERNAME", "");
    $def_domain = GetHttpCookie("WHAT_USERDOMAIN", "");
    $action->debug("Default user   = {$def_user}");
    $action->debug("Default domain = {$def_domain}");

    $dl = new Domain("");
    $dl->ListAll();
    $action->debug("Domaines :{$dl->qcount}");
    $sel = 0; $il = 0;
    if ($dl->qcount > 0) {
      while (list($k,$v) = each($dl->qlist)) {
        $list[$il]["name"] = $v->name;
        $list[$il]["iddomain"] = $v->iddomain;
        if ($def_domain == $v->name) {
          $list[$il]["selected"] = "selected";
          $sel++;
        } else $list[$il]["selected"] = "";
        $il++;
      }
    }
    $list[]= array( "iddomain" => 1,"name" => $action->text("nodomain"));
    if ($sel==0) $list[0]["selected"]="selected";
    reset($list);
    $action->lay->SetBlockCorresp("SELDOMAIN","selected");
    $action->lay->SetBlockData("SELDOMAIN",$list);
    $action->lay->Set("defuser",$def_user);
      
}
      
?>
