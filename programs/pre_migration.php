#!/usr/bin/env php
<?php

/**
 * detect pre migration script
 *
 * @author Anakeen 2009
 * @version $Id:  $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 */
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

include("WHAT/Lib.Common.php");
include("WHAT/Lib.WCheck.php");

if ($argc != 2) {
  printf("program %s need application parameter\n",$argv[0]);
  exit(1);
}
$appname=$argv[1];

$err=getCheckApp($pubdir,&$tapp);
if ($err) {
  print $err;
  exit(1);
}

$app=$tapp[$appname];
if (! $app) {
  printf("application %s not found\n",$argv[1]);
  exit(1);
  
}

require('lib/Lib.Cli.php');

$HTTPUSER = wiff_getParamValue('apacheuser');
$PGSERVICE_CORE = wiff_getParamValue('core_db');
$FREEDOM_CONTEXT = 'default';

putenv(sprintf("wpub=%s", $WIFF_CONTEXT_ROOT));
putenv(sprintf("httpuser=%s", $HTTPUSER));
putenv(sprintf("pgservice_core=%s", $PGSERVICE_CORE));
putenv(sprintf("pgservice_freedom=%s", $PGSERVICE_CORE));
putenv(sprintf("freedom_context=%s", $FREEDOM_CONTEXT));

$err=getCheckActions($pubdir,array($appname=>$app),$actions);
$premigr=array_filter($actions, create_function('$x',"return strstr(\$x,'/'.$appname.'_migr')!==false;"));
foreach ($premigr as $cmd) {
  error_log(sprintf("Executing [%s]...", $cmd));
  exec ( $cmd , $out ,$ret );
  print implode("\n",$out);
  if ($ret!=0) {
    error_log(sprintf("Failed!"));
    exit($ret);
  }
  error_log(sprintf("Done."));
}

?>