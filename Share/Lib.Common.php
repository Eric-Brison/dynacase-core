<?php
/**
 * Common util functions
 *
 * @author Anakeen 2002
 * @version $Id: Lib.Common.php,v 1.11 2004/08/05 09:31:22 eric Exp $
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

function getMailAddr($userid) {
    include_once("Class.MailAccount.php");

    $from="";
    $ma = new MailAccount("",$userid);
    if ($ma->isAffected()) {
      $dom = new Domain("",$ma->iddomain);
      $from = $ma->login."@".$dom->name;
    } else {
      $user = new User("",$userid);

      if ($user->isAffected() && ($user->isgroup=="Y") && ($user->iddomain > 1)) {
	$dom = new Domain("",$user->iddomain);
	$from = $user->login."@".$dom->name;
	
      }
    }
    return $from;
}


function GetParam($name, $def="") {
  global $action;
  if ($action)  return $action->getParam($name,$def);
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


?>