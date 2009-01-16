<?php

include_once("WHAT/Class.Crontab.php");
include_once("FDL/Lib.Util.php");

$cmd = getHttpVars("cmd", NULL);
$file = getHttpVars("file", NULL);
$user = getHttpVars("user", NULL);

function usage() {
  print "\n";
  print "wsh --api=crontab --cmd=list [--user=<uid>]\n";
  print "wsh --api=crontab --cmd=<register|unregister> --file=<path/to/cronfile> [--user=<uid>]\n";
  print "\n";
}

switch( $cmd ) {
case 'list' :
  $crontab = new Crontab($user);
  $ret = $crontab->listAll();
  if( $ret === FALSE ) {
    exit(1);
  }
  break;
case 'register' :
  if( $file === NULL ) {
    error_log("Error: missing --file argument");
    exit(1);
  }
  $crontab = new Crontab($user);
  $ret = $crontab->registerFile($file);
  if( $ret === FALSE ) {
    exit(1);
  }
  break;
case 'unregister' :
  if( $file === NULL ) {
    error_log("Error: missing --file argument");
    exit(1);
  }
  $crontab = new Crontab($user);
  $ret = $crontab->unregisterFile($file);
  if( $ret === FALSE ) {
    exit(1);
  }
  break;
default:
  usage();
}

exit(0);

?>