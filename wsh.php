#! /usr/bin/php -q
<?php
/**
 * WHAT SHELL
 *
 * @author Anakeen 2002
 * @version $Id: wsh.php,v 1.17 2004/08/05 09:31:22 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 */
/**
 */
// ---------------------------------------------------------------
// $Id: wsh.php,v 1.17 2004/08/05 09:31:22 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/wsh.php,v $
// ---------------------------------------------------------------
//  O   Anakeen - 2001
// O*O  Anakeen development team
//  O   dev@anakeen.com
// ---------------------------------------------------------------
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or (at
//  your option) any later version.
//
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
// or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License
// for more details.
//
// You should have received a copy of the GNU General Public License along
// with this program; if not, write to the Free Software Foundation, Inc.,
// 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
// ---------------------------------------------------------------

// WHAT SHELL
ini_set("include_path", ".:/home/httpd/what:/home/httpd/what/WHAT:/usr/share/pear");
ini_set("max_execution_time", "3600");

include_once('Class.Action.php');
include_once('Class.Application.php');
include_once('Class.Session.php');
include_once('Class.Log.php');


$log=new Log("","index.php");

$CoreNull = "";
global $CORE_LOGLEVEL;




// get param
global $_GET;
global $_SERVER;

if ($_SERVER['HTTP_HOST'] != "")     {
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
$core->Set("CORE",$CoreNull);
if (isset($_GET["userid"])) $core->user=new User("",$_GET["userid"]); //special user
else $core->user=new User("",1); //admin 

$core->session=new Session($core->GetParam("CORE_SESSION_DB"));

$CORE_LOGLEVEL=$core->GetParam("CORE_LOGLEVEL", "IWEF");


$puburl = $core->GetParam("CORE_PUBURL","");


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
  echo ($action->execute ());
}

return(0);
?>
