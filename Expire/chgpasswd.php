<?php

global $HTTP_POST_VARS;

if ($HTTP_POST_VARS["login"] == "") {
  print _("no login : passwd unchanged");
  exit;
}
include_once("Class.SessionCache.php");
include_once("Class.User.php");
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

  Header("Location:index.php?sole=R");
  exit;
?>