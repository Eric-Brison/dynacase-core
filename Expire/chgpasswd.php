<?php
/**
 * Display interface to change password in case of expiration
 *
 * @author Anakeen 2003
 * @version $Id: chgpasswd.php,v 1.5 2003/12/18 09:44:41 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage 
 */
/**
 */

global $HTTP_POST_VARS;

if ($HTTP_POST_VARS["login"] == "") {
  print _("no login : passwd unchanged");
  exit;
}
include_once("Class.Application.php");
include_once("Class.User.php");
include_once('Class.SessionCache.php');

bindtextdomain ("what", "/home/httpd/what/locale");
textdomain ("what");
setlocale(LC_MESSAGES,getenv("LANG"));
$u = new User();
$u->setLoginName($HTTP_POST_VARS["login"]);

if (! $u->isAffected()) {
  print _("unknown login : passwd unchanged");
  exit;
}

if ($HTTP_POST_VARS["passwd1"] != $HTTP_POST_VARS["passwd2"]) {
  print _("password are not identicals : not changed");
  exit;
}
if ($HTTP_POST_VARS["passwd1"] == "") {
  print _("empty password : not changed");
  exit;
}
$u->password_new=$HTTP_POST_VARS["passwd1"];
$u->expires = 0;
$u->modify();

global $SERVER_NAME;
global $SERVER_PORT;

Header("Location: http://".$SERVER_NAME.":".$SERVER_PORT."/what/index.php?sole=R");
exit;
?>