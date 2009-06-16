#!/usr/bin/php

<?php

$WIFF_CONTEXT_ROOT = gentenv("WIFF_CONTEXT_ROOT");
if( $WIFF_CONTEXT_ROOT === false ) {
  print "WIFF_CONTEXT_ROOT environment variable not set!\n";
  exit(1);
}

set_include_path(get_include_path().PATH_SEPARATOR.$WIFF_CONTEXT_ROOT);

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

$err=getCheckActions($pubdir,array($appname=>$app),$actions);
$premigr=array_filter($actions, create_function('$x',"return strstr(\$x,'/'.$appname.'_pmigr')!==false;"));
foreach ($premigr as $cmd) {
  exec ( $cmd , $out ,$ret );
  print implode("\n",$out);
  if ($ret!=0) {
    exit($ret);
  }  
}

?>