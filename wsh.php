#! /usr/bin/php -q

<?php
// ---------------------------------------------------------------
// $Id: wsh.php,v 1.3 2002/01/09 16:22:47 eric Exp $
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

while (list($k, $v) = each($argv)) {
  
  if (ereg("--(.+)=(.+)", $v , $reg)) {
    $HTTP_GET_VARS[$reg[1]]=$reg[2];
  }
}



$core = new Application();
$core->Set("CORE",$CoreNull);
$core->user=new User("",1); //admin 

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
  
  bindtextdomain ("what", "/home/httpd/what/locale");
  textdomain ("what");
  
  

if (isset($HTTP_GET_VARS["api"])) {
  include "API/".$HTTP_GET_VARS["api"].".php";
} else {
  echo ($action->execute ());
}


?>
