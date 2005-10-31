<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: ng_mailedit.php,v 1.1 2005/10/31 15:33:36 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
include_once("CORE/Lib.Ng.php");

function ng_mailedit(&$action) {

  $acc = GetHttpVars("acc", -1);
  $atgt = GetHttpVars("atgt", "addmail");
  $myportal = ng_myportal();

  $mbox = $myportal->MailAccounts();
  if ($acc==-1 || !isset($mbox[$acc])) {

    $action->lay->set("edit", false);
    $accname = "";
    $acclogin = "";
    $accpassword = "";
    $accserver = "";
    $accmodk=0;

  } else {
    
    $action->lay->set("edit", true);
    $accname = $mbox[$acc][0];
    $acclogin = $mbox[$acc][3];
    $accpassword = $mbox[$acc][4];
    $accserver = $mbox[$acc][6];
    $accmodk = $mbox[$acc][7];

  }
  $action->lay->set("accchecked",1);
  $action->lay->set("accmlisted",1);
  $action->lay->set("accflag","");
  $action->lay->set("accname",$accname);
  $action->lay->set("acclogin",$acclogin);
  $action->lay->set("accpassword",$accpassword);
  $action->lay->set("accserver",$accserver);
  $mode = array( "Pop3" => "110/pop3", "Pop3 sécurisé (tls)" => "995/pop3/ssl/novalidate-cert", "Imap4" => "143/imap/notls", "Imap4 sécurisé (tls)" => "993/imap/ssl/novalidate-cert");
  $accmode = array();
  foreach ($mode as $k => $v ) {
    $accmode[] = array( "accmodek" => $v, 
		      "accmodev" => $k, 
		      "accmodes" => ($v==$accmodk?"selected":"") );
  }
  $action->lay->setBlockData("MODE",$accmode);
  $action->lay->set("atgt",$atgt);
}
?>

  