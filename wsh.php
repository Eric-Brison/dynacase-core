#! /usr/bin/php -q

<?php
// ---------------------------------------------------------------
// $Id: wsh.php,v 1.1 2002/01/08 12:41:33 eric Exp $
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
include_once('Class.Action.php');
include_once('Class.Application.php');
include_once('Class.Session.php');
include_once('Class.Log.php');


$log=new Log("","index.php");

$CoreNull = "";
global $CORE_LOGLEVEL;





$core = new Application();
$core->Set("CORE",$CoreNull);
$core->user=new User("",1); //admin 

$core->session=new Session($core->GetParam("CORE_SESSION_DB"));

$CORE_LOGLEVEL=$core->GetParam("CORE_LOGLEVEL", "IWEF");


$puburl = $core->GetParam("CORE_PUBURL","");



  $appl = new Application();
  $appl->Set($argv[1],$core);


  $action = new Action();
  $action->Set($argv[2],$appl);





  // init for gettext
  //  setlocale(LC_MESSAGES,$this->Getparam("CORE_LANG"));
  
  putenv ("LC_MESSAGES=".$action->Getparam("CORE_LANG"));
  putenv ("LANG=".$action->Getparam("CORE_LANG"));
  bindtextdomain ("what", "/home/httpd/what/locale");
  textdomain ("what");
  
  $action->log->debug("gettext init for ".$action->parent->name.$action->Getparam("CORE_LANG"));

  echo ($action->execute ());



?>
