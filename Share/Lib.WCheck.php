<?php
/**
 * Util function for update and initialize application
 *
 * @author Anakeen 2005
 * @version $Id: Lib.WCheck.php,v 1.9 2005/11/16 16:36:04 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
/**
 */
//---------------------------------------------------
function GetDbVersion($dbid,&$tmachine) {

  $tver=array();
  $tmachine=array();
  $rq=pg_exec ($dbid, "select paramv.val, application.name, application.machine from paramv, application  where paramv.name='VERSION' and paramv.appid=application.id");

  if ($rq === false) return GetDbOldVersion($dbid);

  for ($i=0; $i<pg_numrows($rq); $i++) {
    $row= pg_fetch_array($rq,$i);
    $tver[$row["name"]]=$row["val"];
    $tmachine[$row["name"]]=$row["machine"];
  }


  return ($tver);
}


//---------------------------------------------------
function GetDbOldVersion($dbid) {
  print "GetDbOldVersion";
  $tver=array();
  $rq=pg_exec ($dbid, "select param.val, application.name from param, application  where param.name='VERSION' and param.key=application.id");

  for ($i=0; $i<pg_numrows($rq); $i++) {
    $row= pg_fetch_array($rq,$i);
    $tver[$row["name"]]=$row["val"];
    if ($row["name"] == "USERS") {
      $tver["CORE"]   =$row["val"];
      $tver["ACCESS"] =$row["val"];
      $tver["APPMNG"] =$row["val"];
      $tver["AUTHENT"]=$row["val"];
    }
  }


  return ($tver);
}

//---------------------------------------------------
function GetFileVersion($topdir) {

  $tver=array();
  if ($dir = @opendir($topdir)) {
    while (($file = readdir($dir)) !== false) {
      $inifile = $topdir."/$file/${file}_init.php";
      if (@is_file($inifile)) {

	$fini = fopen($inifile,"r");
	while (! feof($fini)) {
	  $line = fgets($fini,256);
	  if (ereg("VERSION.*=>[ \t]*\"[ \t]*([0-9\.\-]+)", $line, $reg)) {
	    if (isset($reg[1])) $tver[$file]=$reg[1];

	  }
	}
	fclose($fini);
      }
    }  
    closedir($dir);
  }
  return ($tver);
}
/**
 * get iorder value in .app files
 * @param string $topdir publish directory
 * @return array of iorder
 */
function getAppOrder($topdir) {

  $tiorder=array();
  if ($dir = @opendir($topdir)) {
    while (($file = readdir($dir)) !== false) {
      $inifile = $topdir."/$file/${file}.app";
      if (@is_file($inifile)) {
	unset($app_desc);
	include($inifile);

	if (isset($app_desc)) {
	  if (isset($app_desc["iorder"]))  $tiorder[$file]=$app_desc["iorder"];
	}


      }
    }  
    closedir($dir);
  }
  return ($tiorder);
}
/** compare version like 1.2.3-4 
 * @param string $v1 version one
 * @param string $v2 version two
 * @return int 0 if equal -1 if ($v1<$v2) 1 if ($v2>$1)
 */
function vercmp($v1,$v2) {
  if ($v1==$v2) return 0;
  $tv1=array_reverse(explode(".",str_replace("-",".",$v1)));
  $tv2=array_reverse(explode(".",str_replace("-",".",$v2)));
  $iv1=0;
  $iv2=0;
  foreach ($tv1 as $k=>$v) $iv1+=$v*(pow(100,$k));
  foreach ($tv2 as $k=>$v) $iv2+=$v*(pow(100,$k));
  if ($iv1 > $iv2) return 1;
  else return -1;
}

function checkPGConnection() {
  $dbaccess="user=postgres dbname=template1";
  $dbid=@pg_connect($dbaccess);

  if (!$dbid) {
    $err= _("cannot access to default database [$dbaccess]");
    exec("psql -c '\q' anakeen anakeen",$out);
    $err.=implode(",",$out);
  } else {
    pg_close($dbid);
  }
  return $err;
}
function getCheckApp($pubdir,&$tapp) {
  global $_SERVER;
  $dbaccess=getDbAccess();
  $dbank=getDbName($dbaccess);
  $IP=chop(`hostname -i`);
  $dbid=@pg_connect($dbaccess);

  if (!$dbid) {
    $err= _("cannot access to default database [$dbaccess]");
    exec("psql -c '\q' anakeen anakeen",$out);
    $err.=implode(",",$out);
    
  
  } else {
  
    $tvdb= GetDbVersion($dbid,$tmachine);
    $tvfile=GetFileVersion("$pubdir");

  
    pg_close($dbid);
    $ta = array_unique(array_merge(array_keys($tvdb), array_keys($tvfile)));


    foreach ($ta as $k=>$v) {
      if (($tmachine[$v] != "") && (gethostbyname($tmachine[$v]) != gethostbyname($_SERVER["HOSTNAME"])))
	$chk[$v]="?";
      else if ($tvdb[$v] == $tvfile[$v]) {
	$chk[$v]="";
      } else if ($tvdb[$v] == "" ) {
	$chk[$v]="I";
      } else if ( $tvfile[$v] == "") {
	$chk[$v]="D";    
      } else if (vercmp($tvdb[$v], $tvfile[$v])== 1) {
	$chk[$v]="R";    
      } else {
	$chk[$v]="U";    
      }
      $tapp[$v]=array("name"=>$v,
		    "vdb"=>$tvdb[$v],
		    "vfile"=>$tvfile[$v],
		    "chk"=>$chk[$v],
		    "machine"=>$tmachine[$v]);
  
    }

  }
  return $err;
}






function getCheckActions($pubdir,$tapp,&$tact) {

  $wsh=array(); // application update



  $cmd=array(); // pre/post install 
  $dump=array();
  $dbaccess=getDbAccess();
  $dbank=getDbName($dbaccess);
  $dbid=@pg_connect($dbaccess);

  $tvdb= GetDbVersion($dbid,$tmachine);
  $tiorder=getAppOrder($pubdir);
  
  foreach ($tiorder as $k=>$v) {
    $tapp[$k]["iorder"]=$v;
  }
  uasort($tapp,"cmpapp");
  foreach ($tapp as $k=>$v) {
    // search Migration file
    if ($dir = @opendir("$pubdir/$k")) {
      while (($file = readdir($dir)) !== false) {
	if (ereg("{$k}_migr_([0-9\.]+)$", $file, $reg)) {

	  if (($tvdb[$k] != "") && ($tvdb[$k] < $reg[1]))
	    $cmd[]="$pubdir/$k/$file";
	}
      }
    }   

    // search PRE install
    if (($v["chk"] != "") && (is_file("$pubdir/$k/{$k}_post"))) {
      if ($v["chk"] == "I") {
	$cmd[] = "$pubdir/$k/{$k}_post  ".$v["chk"];
      } 
    }
    switch ($v["chk"]) {
    case "I":
      $cmd[] = "$pubdir/wsh.php  --api=appadmin --method=init --appname=$k";
      $cmd[] = "$pubdir/wsh.php  --api=appadmin --method=update --appname=$k";
      break;
    case "U":
      $cmd[] = "$pubdir/wsh.php  --api=appadmin --method=update --appname=$k";
      break;
    case "D":
      $cmd[] = "#$pubdir/wsh.php  --api=appadmin --method=delete --appname=$k";
      break;
    case "R":
      $cmd[] = "#rpm -Uvh $k-".$v["vdb"];
      break;

    }

    // search POST install
    if (($v["chk"] != "") && (is_file("$pubdir/$k/{$k}_post"))) {
      if ($v["chk"] == "I")  {
	$cmd[] = "$pubdir/$k/{$k}_post  U";
      } else {
	if (($v["chk"] != "R") && ($v["chk"] != "?")) {
	  if ($v["chk"] == "D") $cmd[] = "#$pubdir/$k/{$k}_post ".$v["chk"];
	  else $cmd[] = "$pubdir/$k/{$k}_post ".$v["chk"];
	}
      }
    }
    

    // search Post Migration file
    if ($dir = @opendir("$pubdir/$k")) {
      while (($file = readdir($dir)) !== false) {
	if (ereg("{$k}_pmigr_([0-9\.]+)$", $file, $reg)) {

	  if (($tvdb[$k] != "") && ($tvdb[$k] < $reg[1]))
	    $cmd[]="$pubdir/$k/$file";
	}
      }
    }   
    
  }
  
  $dump[] = "pg_dumpall -U postgres -D > /var/tmp/".uniqid("whatdb");
  //  $dump[] = "/etc/rc.d/init.d/httpd stop";
  $dump[] = "$pubdir/wstop";
  $dump[] = "$pubdir/whattext";
  

  $tact = array_merge($dump,$cmd);
  
  if ($dbank != "anakeen") $tact[] = "echo \"update paramv set val= str_replace(val,'dbname=anakeen','dbname=$dbank') where val ~ 'dbname'\" | psql $dbank anakeen";
  $tact[] = "$pubdir/wsh.php  --api=freedom_clean";
  $tact[] = "$pubdir/wstart";
  $tact[] = "sudo $pubdir/admin/shttpd";
  
}

function cmpapp($a,$b) {
  if (isset($a["iorder"]) && isset($b["iorder"])) {
    if ($a["iorder"]>$b["iorder"]) return 1;
    else if ($a["iorder"]<$b["iorder"]) return -1;
    return 0;
  }
  if (isset($a["iorder"])) return -1;
  if (isset($b["iorder"])) return 1;
  return 0;
}
?>