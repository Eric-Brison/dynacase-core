<?php
/**
 * Common util functions
 *
 * @author Anakeen 2002
 * @version $Id: Lib.Common.php,v 1.17 2005/06/14 03:50:34 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
/**
 */


// library of utilies functions

function print_r2($z) {
  print "<PRE>";
  print_r($z);
  print "</PRE>";
}

function AddLogMsg($msg) {
    global $action;
    if (isset($action->parent))
      $action->parent->AddLogMsg($msg);
}
function AddWarningMsg($msg) {
    global $action;
    if (isset($action->parent))
      $action->parent->AddWarningMsg($msg);
}

function getMailAddr($userid, $full=false) {

    $user = new User("",$userid);
    if ($user->isAffected()) {
      $pren = $postn = "";
      if ($full) {
	$pren = ucfirst(strtolower($user->lastname))." ".ucfirst(strtolower($user->firstname))." <";
	$postn = ">";
      }
      return $pren.$user->getMail().$postn;
    }
    return false;
}


function GetParam($name, $def="") {
  global $action;
  if ($action)  return $action->getParam($name,$def);

  // case of without what context
  include_once("Class.Action.php");
  $core = new Application();
  $core->Set("CORE",$CoreNull);
  $act = new Action();
  $act->Set("",$core);
  return  $act->getParam($name, $def);

}

function getLayoutFile($app, $layfile) {
  $socStyle = Getparam("CORE_SOCSTYLE");
  if ($socStyle != "") {
    $root = Getparam("CORE_PUBDIR");
    $file = $root."/$app/Layout/$socStyle/$layfile";

    if (file_exists($file))  return($file);
    
  }
  return $app."/Layout/".$layfile;
}

function microtime_diff($a,$b) {
    list($a_micro, $a_int)=explode(' ',$a);
     list($b_micro, $b_int)=explode(' ',$b);
     if ($a_int>$b_int) {
        return ($a_int-$b_int)+($a_micro-$b_micro);
     } elseif ($a_int==$b_int) {
        if ($a_micro>$b_micro) {
          return ($a_int-$b_int)+($a_micro-$b_micro);
        } elseif ($a_micro<$b_micro) {
           return ($b_int-$a_int)+($b_micro-$a_micro);
        } else {
          return 0;
        }
     } else { // $a_int<$b_int
        return ($b_int-$a_int)+($b_micro-$a_micro);
     }
}

function getDbid($dbaccess) {
    global $CORE_DBID;
	if (!isset($CORE_DBID) || !isset($CORE_DBID["$dbaccess"])) {
           $CORE_DBID["$dbaccess"] = pg_connect("$dbaccess");
        } 
    return $CORE_DBID["$dbaccess"];
}

function getDbAccess() {
  global $CORE_DBANK;;

  if ($CORE_DBANK != "") return $CORE_DBANK;
  $dbaccess="";

  $root = "/home/httpd/what";
  $dbank=getenv("dbanakeen");
  
 
  if ($dbank != "") {
    $filename="$root/virtual/$dbank/dbaccess.php";    
    if (file_exists($filename)) {
      include($filename);
    }    
  }
  if ($dbaccess=="") include("dbaccess.php");
  $CORE_DBANK=$dbaccess;
  return $CORE_DBANK;
  
}


function getDbName($dbaccess) {
  if (ereg("dbname=([a-z]+)",$dbaccess,$reg)) {
    return $reg[1];
  }
}


function getDbUser($dbaccess) {
  if (ereg("user=([a-z]+)",$dbaccess,$reg)) {
    return $reg[1];
  }
}


function getWshCmd($nice=false) {
  $dbname=getDbName(getDbAccess());
  $wsh="export dbanakeen=$dbname;";
  if ($nice) $wsh.= "nice -n +10 ";
  $wsh.=GetParam("CORE_PUBDIR")."/wsh.php  ";
  return $wsh;
}

/**
 * exec list of unix command in background
 * @param array $tcmd unix command strings
 */
function bgexec($tcmd,&$result,&$err) {
  $foutname = uniqid("/tmp/bgexec");
  $fout = fopen($foutname,"w+");
  fwrite($fout,"#!/bin/bash\n");
  foreach ($tcmd as $v) {
    fwrite($fout,"$v\n");
  }
  fclose($fout);
  chmod($foutname,0700);


  //  if (session_id()) session_write_close(); // necessary to close if not background cmd 
  exec("exec nohup $foutname > /dev/null 2>&1 &",$result,$err); 
  //if (session_id()) @session_start();
}

function wbar($reste,$total,$text="",$fbar=false) {
if (!$fbar) $fbar = GetHttpVars("bar"); // for progress bar
 if ($fbar) {
   
      
      if (file_exists("$fbar.lck")) {
	$wmode="w";
	unlink("$fbar.lck");
      } else {
	$wmode="a";	
      }
      $ffbar=fopen($fbar,$wmode);
      fputs($ffbar,"$reste/$total/$text\n");
      fclose($ffbar);      
    }
}

function getJsVersion() {
  include_once("Class.QueryDb.php");
  $q=new QueryDb("","param");
  $q->AddQuery("name='VERSION'");
  $l=$q->Query(0,0,"TABLE");
  $nv=0;
  foreach ($l as $k=>$v) {  
    $nv+=intval(str_replace('.','',$v["val"]));
  }

  return $nv;
}
?>
