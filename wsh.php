#! /usr/bin/php -q

<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wsh.php,v 1.11 2003/08/14 09:44:46 eric Exp $
 * @package 
 * @subpackage /home/eric/anakeen/what/What/guest.php
 */

// ---------------------------------------------------------------
// $Id: wsh.php,v 1.11 2003/08/14 09:44:46 eric Exp $
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
ini_set("include_path", ".:/home/httpd/what:/home/httpd/what/WHAT");
ini_set("max_execution_time", "3600");

include_once('Class.Action.php');
include_once('Class.Application.php');
include_once('Class.Session.php');
include_once('Class.Log.php');


$log=new Log("","index.php");

$CoreNull = "";
global $CORE_LOGLEVEL;




// get param
global $HTTP_GET_VARS;
global $HTTP_CONNECTION;

if ($HTTP_CONNECTION != "")     {
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
  
  if (ereg("--(.+)=(.+)", $v , $reg)) {
    $HTTP_GET_VARS[$reg[1]]=$reg[2];
  }  else if (ereg("--(.+)", $v , $reg)) {
    if ($reg[1] == "listapi") {
      print "application list :\n";
      echo "\t- ";
      echo str_replace("\n","\n\t- ",shell_exec ("cd /home/httpd/what/API;ls -1 *.php| cut -f1 -d'.'"));
      echo "\n";
      exit;
    }
    $HTTP_GET_VARS[$reg[1]]=true;
  }
}



$core = new Application();
$core->Set("CORE",$CoreNull);
if (isset($HTTP_GET_VARS["userid"])) $core->user=new User("",$HTTP_GET_VARS["userid"]); //special user
else $core->user=new User("",1); //admin 

$core->session=new Session($core->GetParam("CORE_SESSION_DB"));

$CORE_LOGLEVEL=$core->GetParam("CORE_LOGLEVEL", "IWEF");


$puburl = $core->GetParam("CORE_PUBURL","");


if (isset($HTTP_GET_VARS["app"])) {
  $appl = new Application();
  $appl->Set($HTTP_GET_VARS["app"],
	     $core);
} else {
  $appl = $core;
}

$action = new Action();
if (isset($HTTP_GET_VARS["action"])) {
  $action->Set($HTTP_GET_VARS["action"],
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
  
  


if (isset($HTTP_GET_VARS["api"])) {
  include "API/".$HTTP_GET_VARS["api"].".php";
} else {
  echo ($action->execute ());
}

return(0);
?>
