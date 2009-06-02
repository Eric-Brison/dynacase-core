#!/usr/bin/php

<?php

$prefix=getenv("WIFF_CONTEXT_ROOT")."/WHAT/Lib.Prefix.php";
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