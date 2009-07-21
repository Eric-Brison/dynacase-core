<?php
/**
 * Main program to activate action in WHAT software in guest mode
 *
 * @author Anakeen 2000 
 * @version $Id: guest.php,v 1.24 2008/12/16 15:51:53 jerome Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage 
 */
 /**
 */

include_once('WHAT/Lib.Main.php');

#
# This is the main body of App manager
# It is used to launch application and 
# function giving them all necessary environment
# element
#
#
getmainAction($auth,$action);

if ($action->user->id != ANONYMOUS_ID) { 
  // reopen a new anonymous session
  setcookie ('freedom_param',$session->id,0,"/");
  unset($_SERVER['PHP_AUTH_USER']); // cause IE send systematicaly AUTH_USER & AUTH_PASSWD
  $session->Set("");
  $core->SetSession($session);
}
if ($action->user->id != ANONYMOUS_ID) { 
  // reverify
  print "<B>:~((</B>";
  exit;
}


executeAction($action);
?>
