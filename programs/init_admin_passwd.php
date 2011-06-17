#!/usr/bin/env php
<?php

$WIFF_ROOT = getenv("WIFF_ROOT");
if( $WIFF_ROOT === false ) {
  print "WIFF_ROOT environment variable is not set!\n";
  exit(1);
}

$WIFF_CONTEXT_ROOT = getenv("WIFF_CONTEXT_ROOT");
if( $WIFF_CONTEXT_ROOT === false ) {
  print "WIFF_CONTEXT_ROOT environment variable not set!\n";
  exit(1);
}

set_include_path(get_include_path().PATH_SEPARATOR.$WIFF_CONTEXT_ROOT.PATH_SEPARATOR."$WIFF_ROOT/include");

$prefix=$WIFF_CONTEXT_ROOT."/WHAT/Lib.Prefix.php";
if (! include($prefix)) {
  print "cannot include file $prefix";
  exit(1);
}

include_once('lib/Lib.Cli.php');
include_once('WHAT/Lib.Common.php');
include_once('WHAT/Class.User.php');

$admin_passwd = wiff_getParamValue('core_admin_passwd');
if( $admin_passwd == '' ) {
  print "Empty core_admin_passwd.";
  exit(1);
}

$dbaccess = getParam('CORE_DB');

$user = new User($dbaccess, 1);
if( ! is_object($user) || ! $user->isAffected() ) {
  print "Could not find user with id '1' (admin).";
  exit(1);
}

$user->computepass($admin_passwd, $user->password);
$err = $user->modify(true, '', true);
if( $err != '' ) {
  print sprintf("Modify returned with error: %s", $err);
  exit(1);
}

exit(0);

?>