<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: ng_mailsave.php,v 1.1 2005/10/31 15:33:36 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
include_once("CORE/Lib.Ng.php");

function ng_mailsave(&$action) {

  $acc = GetHttpVars("accid", -1);
  $name = GetHttpVars("accname", "");
  $login = GetHttpVars("acclogin", "");
  $passwd = GetHttpVars("accpassword", "");
  $server = GetHttpVars("accserver", "");
  $mode = GetHttpVars("accmode", 0);
  $flag = GetHttpVars("accflag", "");
  $checked = GetHttpVars("accchecked", 1);
  $mlisted = GetHttpVars("accmlisted", 1);

  $myportal = ng_myportal();
  $myportal->MailChange( $name, $login, $passwd, $server, $mode, $flag, $checked, $mlisted );

  redirect($action,"CORE","NGMAIN");
}
