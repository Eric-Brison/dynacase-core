#! /usr/bin/php -q
<?php
/**
 * WHAT SHELL
 *
 * @author Anakeen 2002
 * @version $Id: wsh.php,v 1.23 2005/07/28 16:45:38 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 */
/**
 */


ini_set("include_path", ".:/home/httpd/what:/home/httpd/what/WHAT:/usr/share/pear");
ini_set("max_execution_time", "3600");

include_once('Class.Action.php');
include_once('Class.Application.php');
include_once('Class.Session.php');
include_once('Class.Log.php');


wbar(1,-1,"initialisation");
$log=new Log("","index.php");

$CoreNull = "";
global $CORE_LOGLEVEL;

define("DEFAULT_PUBDIR","/home/httpd/what");


// get param
global $_GET;
global $_SERVER;

if (isset($_SERVER['HTTP_HOST']))     {
  print "<BR><H1>:~(</H1>";
  exit;
}
if (count($argv) == 1) {
  print "Usage\twsh.php --app=APPLICATION --action=ACTION [--ARG=VAL] ...:  execute an action\n".
    "\twsh.php --api=API [--ARG=VAL] ....   :  execute an api function\n".
    "\twsh.php --listapi                     : view api list\n";
  exit;
}

while (list($k, $v) = each($argv)) {
  
  if (ereg("--([^=]+)=(.+)", $v , $reg)) {
    $_GET[$reg[1]]=$reg[2];
  }  else if (ereg("--(.+)", $v , $reg)) {
    if ($reg[1] == "listapi") {
      print "application list :\n";
      echo "\t- ";
      echo str_replace("\n","\n\t- ",shell_exec ("cd /home/httpd/what/API;ls -1 *.php| cut -f1 -d'.'"));
      echo "\n";
      exit;
    }
    $_GET[$reg[1]]=true;
  }
}



$core = new Application();
if ($core->dbid < 0){
  print "Cannot access to main database";
  exit(1);
}

$core->Set("CORE",$CoreNull);
if (isset($_GET["userid"])) $core->user=new User("",$_GET["userid"]); //special user
else $core->user=new User("",1); //admin 

$core->session=new Session();

$CORE_LOGLEVEL=$core->GetParam("CORE_LOGLEVEL", "IWEF");


$puburl = $core->GetParam("CORE_PUBURL","http://".`hostname -f`."/what");



$core->SetVolatileParam("CORE_PUBURL", "."); // relative links
$core->SetVolatileParam("CORE_ABSURL", $puburl."/"); // absolute links
$core->SetVolatileParam("CORE_JSURL", "WHAT/Layout");
$core->SetVolatileParam("CORE_ROOTURL", "index.php?sole=R&");
$core->SetVolatileParam("CORE_BASEURL", "index.php?sole=A&");
$core->SetVolatileParam("CORE_SBASEURL","index.php?sole=A&");
$core->SetVolatileParam("CORE_STANDURL","index.php?sole=Y&");
$core->SetVolatileParam("CORE_SSTANDURL","index.php?sole=Y&");

if (isset($_GET["app"])) {
  $appl = new Application();
  $appl->Set($_GET["app"],
	     $core);
} else {
  $appl = $core;
}

$action = new Action();
if (isset($_GET["action"])) {
  $action->Set($_GET["action"],
	       $appl);
} else {
  $action->Set("",$appl);
}




  // init for gettext
  
// init for gettext
setlocale(LC_MESSAGES,$core->Getparam("CORE_LANG"));  
putenv ("LANG=".$core->Getparam("CORE_LANG")); // needed for old Linux kernel < 2.4
bindtextdomain ("what", "/home/httpd/what/locale");
textdomain ("what");
  


if (isset($_GET["api"])) {
  if (!include "API/".$_GET["api"].".php") {
    echo sprintf(_("API file %s not found"),"API/".$_GET["api"].".php");
  }
} else {
  if (! isset($_GET["wshfldid"])) {
    echo ($action->execute ());
  } else {
    // REPEAT EXECUTION FOR FREEDOM FOLDERS
    $dbaccess=$appl->GetParam("FREEDOM_DB");
    if ($dbaccess == "") {
      print "Freedom Database not found : param FREEDOM_DB";
      exit;
    }
    include_once("FDL/Class.Doc.php");
    $http_iddoc="id"; // default correspondance
    if (isset($_GET["wshfldhttpdocid"])) $http_iddoc=$_GET["wshfldhttpdocid"];
    $fld=new_Doc($dbaccess,$_GET["wshfldid"]);
    $ld=$fld->getContent();
    foreach ($ld as $k=>$v) {
      $_GET[$http_iddoc]=$v["id"];
      echo ($action->execute ());
    }

  }
}

wbar(0,0,"completed");

return(0);
?>
