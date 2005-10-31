<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: ng_mailcheck.php,v 1.1 2005/10/31 15:33:36 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
include_once("CORE/Lib.Ng.php");

function ng_mailcheck(&$action) {

  $acc = GetHttpVars("acc", -1);
  $myportal = ng_myportal();

  $mbox = $myportal->MailAccounts();
  if ($acc==-1 || !isset($mbox[$acc])) {
    $action->lay->set("noaccount", true);
    return;
  }

  $action->lay->set("noaccount", false);

  $mboxspec = "{".$mbox[$acc][6].":".$mbox[$acc][7].($mbox[$acc][8]==""?"":"/".$mbox[$acc][8])."}".$mbox[$acc][5];
  $minfos = getMbox($mboxspec, $mbox[$acc][3],$mbox[$acc][4]);

  $action->lay->set("nomessage", true);
  
  if (count($minfos)>0) {
    $mails = array();
    $action->lay->set("nomessage", false);
    $action->lay->set("newmail", (count($minfos["newmails"])>0?true:false));
    $action->lay->set("newmails", count($minfos["newmails"]));
    $action->lay->set("oldmails", count($minfos["oldmails"]));
    foreach ($minfos["newmails"] as $k => $v) {
      $mails[] = array( "from" => $v->from,
			"subject" => $v->subject,
			"date" => $v->date  );
    }
    $action->lay->setBlockData("MAILS", $mails);
  }
  $action->lay->set("imbox", $acc);
  $action->lay->set("name", $mbox[$acc][0]);
  return;
}

function getMbox($mbox, $login, $pass) {
  $mailbox = array();
  $newh = $oldh = array();
  $err = "";
  $otime = time();
  $mbx = @imap_open($mbox, $login, $pass );
  if (!$mbx) {
    $err = imap_last_error();
  } else {
    $s = imap_check($mbx);
    if (!$s) {
      $err = imap_last_error();
    } else {
      $ni = imap_num_msg($mbx);
      $ovv = imap_fetch_overview($mbx, "$ni:1");
      foreach ($ovv as $k => $v) {
        if ($v->deleted) continue;
        if (!$v->seen) {
          $newh[]= $v;
        } else {
          $oldh[]= $v;
        }
      }
    }
    imap_close($mbx);
    $ftime = time() - $otime;
  }
  $mailbox = array( "newmails" => $newh,
                    "oldmails" => $oldh,
                    "error"    => $err,
                    "elapsed"  => $ftime );
  return $mailbox;
}

?>