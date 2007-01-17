<?php

include_once("WHAT/Lib.Prefix.php");

function rezizelocalimage($img,$size,$basedest) {
  $source=$img;
  
  $dest=DEFAULT_PUBDIR.$basedest;

  $cmd=sprintf("convert  -thumbnail %d $source $dest",$size);
  //$cmd=sprintf("convert  -scale %dx%d $source $dest",$size,$size);
  system($cmd);
    print($cmd);
  if (file_exists($dest)) return $basedest;
  return false;
}
function getVaultPauth($vid) {
  include_once("WHAT/Lib.Common.php");
  
  $dbaccess=getDbAccess();
  $rcore = pg_connect($dbaccess);
  if ($rcore) {
    $result = pg_query($rcore, "SELECT val from paramv where name='FREEDOM_DB' and type='G'");
    if ($result) {
      $row = pg_fetch_row($result);
      $dbfree=current($row);

      if ($dbfree) {
	$rfree = pg_connect($dbfree);
	if ($rfree) {
	  $result = pg_query("select id_dir,name from vaultdiskstorage where id_file=$vid");
	  if ($result) {
	    $row = pg_fetch_row($result);
	    $iddir=$row[0];
	    $name=$row[1];

	    if (preg_match('/\.([^\.]*)$/',$name,$reg)) {
	      $ext=$reg[1];
	    }

	    $result = pg_query("SELECT l_path,id_fs from vaultdiskdirstorage where id_dir = $iddir");
	    $row = pg_fetch_row($result);
	    $lpath=$row[0];
	    $idfs=$row[1];
	    $result = pg_query("SELECT r_path from vaultdiskfsstorage where id_fs = $idfs");
	    $row = pg_fetch_row($result);
	    $rpath=$row[0];


	    $localimg="$rpath/$lpath/$vid.$ext";
	    if (file_exists($localimg)) return $localimg;
	
	
	  }
	}      
      }
    }
  }
  return false;
}

function getVaultCacheImage($vid,$size) {
  $basedest="/img-cache/$size-vid$vid.png";
  return $basedest;
}

$size=$_GET["size"];
$img=$_GET["img"];
$dir=dirname($_SERVER["SCRIPT_NAME"]);
if (ereg("vaultid=([0-9]+)",$img,$vids)) {
  // vault file
  $vid=$vids[1];
  $basedest=getVaultCacheImage($vid,$size);
  $dest=DEFAULT_PUBDIR.$basedest;
  if (file_exists($dest)) {
    $location=$dir."/".$basedest;
  } else {
    $localimage=getVaultPauth(intval($vid));
    if ($localimage) {    
      $newimg=rezizelocalimage($localimage,$size,$basedest);
      if ($newimg) $location="$dir/$newimg";
    } 
  }
 } else {
  // local file
  $turl=(parse_url($img));
  $path=$turl["path"];

  if (strstr($path, $dir ) == $path) {
    $localimage=substr($path,strlen($dir));

    $basedest="/img-cache/$size-".basename($localimage).".png";
    $dest=DEFAULT_PUBDIR.$basedest;

    if (file_exists($dest)) $location= "$dir/$basedest";
    else {
      $newimg=rezizelocalimage(DEFAULT_PUBDIR."/$localimage",$size,$basedest);
      if ($newimg) $location="$dir/$newimg";
    }
  }
 }
//print("<hr>Location: $location");
if ($location) $location="/".ltrim($location,"/");
else $location=$img;
Header("Location: $location");

?>