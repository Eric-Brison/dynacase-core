<?php
/**
 * Util function for update and initialize application
 *
 * @author Anakeen 2005
 * @version $Id: Lib.WCheck.php,v 1.21 2009/01/07 15:35:07 jerome Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package WHAT
 * @subpackage CORE
 */
/**
 */

include_once("WHAT/Lib.System.php");

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
	  if (preg_match("/VERSION.*=>[ \t]*\"[ \t]*([0-9\.\-]+)/", $line, $reg)) {
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
 
  if (version2float($v1) > version2float($v2)) return 1;
  else return -1;
}

function checkPGConnection() {
  $dbaccess_core = getDbAccessCore();
  $pgservice_core = getServiceCore();

  $dbid=@pg_connect($dbaccess_core);
  
  if (!$dbid) {
    $err= _("cannot access to core database service [service='$pgservice_core']");
    exec("PGSERVICE=\"$pgservice_core\" psql -c '\q'",$out);
    $err.=implode(",",$out);
  } else {
    pg_close($dbid);
  }
  return $err;
}

function getCheckApp($pubdir,&$tapp) {
  global $_SERVER;

  $dbaccess_core = getDbAccessCore();
  $pgservice_core = getServiceCore();
  $pgservice_freedom = getServiceFreedom();

  $IP=LibSystem::getHostIPAddress();
  $dbid=@pg_connect($dbaccess_core);
  
  if (!$dbid) {
    $err= _("cannot access to core database service [service='$pgservice_core']");
    exec("PGSERVICE=\"$pgservice_core\" psql -c '\q'",$out);
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

function version2float($ver) {
  if (preg_match_all('/([0-9]+)/', $ver , $matches)) {
    $matches=($matches[0]);
    $sva='';
    $c=count($matches);
    if ($c < 4) {
      for ($i=0;$i < (4-$c);$i++) {
	$matches[]='0';
      }
    }
    foreach ($matches as $k=>$v)    $sva.=sprintf("%02d",$v);
    return floatval($sva);
  }
}

function getCheckActions($pubdir,$tapp,&$tact) {

  $wsh=array(); // application update
  $cmd=array(); // pre/post install 
  $dump=array();

  $pgservice_core = getServiceCore();
  $pgservice_freedom = getServiceFreedom();

  $dbid=@pg_connect("service='$pgservice_core'");

  $tvdb= GetDbVersion($dbid,$tmachine);
  $tiorder=getAppOrder($pubdir);
  
  foreach ($tiorder as $k=>$v) {
    $tapp[$k]["iorder"]=$v;
  }
  uasort($tapp,"cmpapp");
  foreach ($tapp as $k=>$v) {
    $migr=array();
    $pattern = preg_quote($k);
    // search Migration file
    if ($dir = @opendir("$pubdir/$k")) {
      while (($file = readdir($dir)) !== false) {
	if (preg_match("/{$pattern}_migr_([0-9\.]+)$/", $file, $reg)) {
	  if (($tvdb[$k] != "") && (version2float($tvdb[$k]) < version2float($reg[1])))
	    $migr[]="$pubdir/$k/$file";
	}
      }
    }   
    sort($migr);
    $cmd=array_merge($cmd,$migr);
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
    $migr=array();
    if ($dir = @opendir("$pubdir/$k")) {
      while (($file = readdir($dir)) !== false) {
	if (preg_match("/{$pattern}_pmigr_([0-9\.]+)$/", $file, $reg)) {

	  if (($tvdb[$k] != "") && (version2float($tvdb[$k]) < version2float($reg[1])))
	    $migr[]="$pubdir/$k/$file";
	}
      }
    }   
    sort($migr);
    $cmd=array_merge($cmd,$migr);
  }
  
  $dump[] = "PGSERVICE=\"$pgservice_core\" pg_dump > ".getTmpDir()."/".uniqid($pgservice_core);
  $dump[] = "PGSERVICE=\"$pgservice_freedom\" pg_dump -D > ".getTmpDir()."/".uniqid($pgservice_freedom);
  //  $dump[] = "/etc/rc.d/init.d/httpd stop";
  $dump[] = "$pubdir/wstop";
  $dump[] = "$pubdir/whattext";
  
  $tact = array_merge($dump,$cmd);
  
  $tact[] = "$pubdir/wsh.php  --api=freedom_clean";
  $tact[] = "$pubdir/wstart";
  global $_SERVER;
  if( empty($_GET['httpdrestart']) || ($_GET['httpdrestart'] != 'no') ) {
    if ($_SERVER['HTTP_HOST'] != "")  $tact[] = "sudo $pubdir/admin/shttpd";
    else $tact[] = "service httpd restart";  
  }
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
