<?php
/**
 * Util function for update and initialize application
 *
 * @author Anakeen 2005
 * @version $Id: Lib.WCheck.php,v 1.7 2005/11/15 08:00:58 eric Exp $
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
	print_r($app_const);
      }
    }  
    closedir($dir);
  }
  return ($tver);
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
  $migr=array();// migration
  $pmigr=array();// post migration
  $post=array();// post install 
  $pre=array(); // pre install 
  $dump=array();
  $dbaccess=getDbAccess();
  $dbank=getDbName($dbaccess);
  $dbid=@pg_connect($dbaccess);

  $tvdb= GetDbVersion($dbid,$tmachine);
  foreach ($tapp as $k=>$v) {
    switch ($v["chk"]) {
    case "I":
      $wsh[] = "$pubdir/wsh.php  --api=appadmin --method=init --appname=$k";
      $wsh[] = "$pubdir/wsh.php  --api=appadmin --method=update --appname=$k";
      break;
    case "U":
      $wsh[] = "$pubdir/wsh.php  --api=appadmin --method=update --appname=$k";
      break;
    case "D":
      $wsh[] = "#$pubdir/wsh.php  --api=appadmin --method=delete --appname=$k";
      break;
    case "R":
      $wsh[] = "#rpm -Uvh $k-".$v["vdb"];
      break;

    }

    // search POST install
    if (($v["chk"] != "") && (is_file("$pubdir/$k/{$k}_post"))) {
      if ($v["chk"] == "I") {
	$pre[] = "$pubdir/$k/{$k}_post  ".$v["chk"];
	$post[] = "$pubdir/$k/{$k}_post  U";
      } else {
	if (($v["chk"] != "R") && ($v["chk"] != "?")) {
	  if ($v["chk"] == "D") $post[] = "#$pubdir/$k/{$k}_post ".$v["chk"];
	  else $post[] = "$pubdir/$k/{$k}_post ".$v["chk"];
	}
      }
    }
    

    // search Migration file
    if ($dir = @opendir("$pubdir/$k")) {
      while (($file = readdir($dir)) !== false) {
	if (ereg("{$k}_migr_([0-9\.]+)$", $file, $reg)) {

	  if (($tvdb[$k] != "") && ($tvdb[$k] < $reg[1]))
	    $migr[]="$pubdir/$k/$file";
	}
      }
    }   


    // search Post Migration file
    if ($dir = @opendir("$pubdir/$k")) {
      while (($file = readdir($dir)) !== false) {
	if (ereg("{$k}_pmigr_([0-9\.]+)$", $file, $reg)) {

	  if (($tvdb[$k] != "") && ($tvdb[$k] < $reg[1]))
	    $pmigr[]="$pubdir/$k/$file";
	}
      }
    }   
    
  }
  
  $dump[] = "pg_dumpall -U postgres -D > /var/tmp/".uniqid("whatdb");
  //  $dump[] = "/etc/rc.d/init.d/httpd stop";
  $dump[] = "$pubdir/wstop";
  $dump[] = "$pubdir/whattext";
  

  $tact = array_merge($dump,
		      array_merge($pre,
				  array_merge($migr,
					      array_merge($wsh, 
							  array_merge($post,$pmigr)))));
  
  if ($dbank != "anakeen") $tact[] = "echo \"update paramv set val= str_replace(val,'dbname=anakeen','dbname=$dbank') where val ~ 'dbname'\" | psql $dbank anakeen";
  $tact[] = "$pubdir/wsh.php  --api=freedom_clean";
  $tact[] = "$pubdir/wstart";
  $tact[] = "sudo $pubdir/admin/shttpd";
  
}
?>