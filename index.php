<?php
// ---------------------------------------------------------------
// $Id: index.php,v 1.4 2002/01/25 14:31:37 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/index.php,v $
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

#
# This is the main body of App manager
# It is used to launch application and 
# function giving them all necessary environment
# element
#
#


include_once('Class.Action.php');
include_once('Class.Application.php');
include_once('Class.Session.php');
include_once('Lib.Http.php');
include_once('Class.Log.php');
include_once('Class.Domain.php');
include_once('Class.DbObj.php');


// ----------------------------------------
// pre include for session cache
if (file_exists($HTTP_GET_VARS["app"]."/include.php")) {
        include($HTTP_GET_VARS["app"]."/include.php");
}

$log=new Log("","index.php");

$CoreNull = "";
global $CORE_LOGLEVEL;

global $HTTP_GET_VARS;
if (!isset($HTTP_GET_VARS["app"])) $HTTP_GET_VARS["app"]="CORE";
if (!isset($HTTP_GET_VARS["action"])) $HTTP_GET_VARS["action"]="";


$standalone = GetHttpVars("sole");

$sess_num=GetHttpVars("session");

$core = new Application();
$core->Set("CORE",$CoreNull);


$session=new Session($core->GetParam("CORE_SESSION_DB"));
$session->Set($sess_num);
$CORE_LOGLEVEL=$core->GetParam("CORE_LOGLEVEL", "IWEF");

// ----------------------------------------
// Init URL from script name
global $SERVER_NAME;
global $SCRIPT_NAME;

if (ereg("(.*)/index\.php", $SCRIPT_NAME, $reg)) {
  $puburl = "http://".$SERVER_NAME.$reg[1];
} else {
  // it is not allowed
  print "<B>:~(</B>";
  exit;
}


$core->SetVolatileParam("CORE_PUBURL", $puburl);
$core->SetVolatileParam("CORE_JSURL", $puburl."/WHAT/Layout");


$core->SetSession($session);



$core->SetVolatileParam("CORE_ROOTURL", $puburl."/index.php?session={$session->id}&sole=R&");
$core->SetVolatileParam("CORE_BASEURL", $puburl."/index.php?session={$session->id}&sole=A&");
$core->SetVolatileParam("CORE_STANDURL", $puburl."/index.php?session={$session->id}&sole=Y&");

// ----------------------------------------
// Init Application & Actions Objects
if (($standalone == "") || ($standalone == "N")) {
  $action = new Action();
  $action->Set("MAIN",$core,$session);
} else {
  $appl = new Application();
  $appl->Set($HTTP_GET_VARS["app"],$core);


  $action = new Action();
  $action->Set($HTTP_GET_VARS["action"],$appl,$session);

}

$nav=$HTTP_USER_AGENT;
$pos=strpos($nav,"MSIE");
if ($action->Read("navigator","") == "") {
  if ( $pos>0) {
    $action->Register("navigator","EXPLORER");
    if (ereg("MSIE ([0-9.]+).*",$nav,$reg)) {
      $action->Register("navversion",$reg[1]);      
    }
  } else {
    $action->Register("navigator","NETSCAPE");
    if (ereg("([a-zA-Z]+)/([0-9.]+).*",$nav,$reg)) {
      $action->Register("navversion",$reg[2]);      
    }
  }
}

// init for gettext
setlocale(LC_MESSAGES,$action->Getparam("CORE_LANG"));  
putenv ("LANG=".$action->Getparam("CORE_LANG")); // needed for old Linux kernel < 2.4
bindtextdomain ("what", "/home/httpd/what/locale");
textdomain ("what");

  
  $action->log->debug("gettext init for ".$action->parent->name.$action->Getparam("CORE_LANG"));

if (($standalone == "Y") || ($standalone == "N") || ($standalone == ""))
{
  echo ($action->execute ());
} 
else 
  if ($standalone == "R")
    {      
      $app = GetHttpVars("app","CORE");
      $act = GetHttpVars("action","");
      redirect($action, "CORE", "MAIN&appd=${app}&actd=${act}",$action->GetParam("CORE_STANDURL"));
    }
  else
    if ($standalone == "A")
      {
	
	if ((isset ($appl)) && ( $appl->with_frame != "Y" ))
	  {  
	    // This document is not completed : does not contain header and footer

	    // HTML body result
	    // achieve action
	    $body = ($action->execute ());
	    // write HTML header
	    $head = new Layout($action->GetLayoutFile("htmltablehead.xml"),$action);
	    // copy JS ref & code from action to header
	    $head->jsref = $action->parent->GetJsRef();
	    $head->jscode = $action->parent->GetJsCode();
	    
	    echo($head->gen());
	    // write HTML body
	    echo ($body);
	    // write HTML footer
	    $foot = new Layout($action->GetLayoutFile("htmltablefoot.xml"),$action);
	    echo($foot->gen());
	  }
	else
	  {
	    // This document is completed 
	    echo ($action->execute ());
	  }
	  
      }


?>
