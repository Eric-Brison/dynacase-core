<?php
/**
 * Display interface to change password in case of expiration
 *
 * @author Anakeen 2003
 * @version $Id: chgpasswd.php,v 1.6 2004/03/22 15:21:40 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage 
 */
/**
 */

global $_POST;

if ($_POST["login"] == "") {
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
$u->setLoginName($_POST["login"]);

if (! $u->isAffected()) {
  print _("unknown login : passwd unchanged");
  exit;
}

if ($_POST["passwd1"] != $_POST["passwd2"]) {
  print _("password are not identicals : not changed");
  exit;
}
if ($_POST["passwd1"] == "") {
  print _("empty password : not changed");
  exit;
}
$u->password_new=$_POST["passwd1"];
$u->expires = 0;
$u->modify();

global $_SERVER;

Header("Location: http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/what/index.php?sole=R");
exit;
?>