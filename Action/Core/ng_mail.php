<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: ng_mail.php,v 1.1 2005/10/19 17:24:11 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
function ng_mail(&$action) {

$mailaccounts = array( 
  array(
      "descr"  => "Free, perso",
      "check"  => true,
      "details"  => true,
      "server"  => "pop.free.fr", 
      "box"  => "",
      "account" => "marc.claverie",
      "pass"    => "39h404gv",
      "flag"    => "",
      "port"    =>  "110/pop3" ),
  array(
      "descr"  => "Gooooooooooogle",
      "check"  => true,
      "details"  => true,
      "server"  => "pop.gmail.com", 
      "box"  => "INBOX",
      "account" => "marc.claverie@gmail.com",
      "pass"    => "39h404gv",
      "flag"    => "",
      "port"    =>  "995/pop3/ssl/novalidate-cert" ),
  array(
      "descr"  => "Césam",
      "check"  => true,
      "details"  => true,
      "server"  => "mail.tlse.i-cesam.com", 
      "box"  => "",
      "account" => "marc.claverie",
      "pass"    => "39h404gv",
      "flag"    => "",
      "port"    =>  "143/imap/notls" ),
  array(
      "descr"  => "Césam (mcw)",
      "check"  => true,
      "details"  => true,
      "server"  => "mail.tlse.i-cesam.com", 
      "box"  => "",
      "account" => "mcw",
      "pass"    => "emmalea",
      "flag"    => "",
      "port"    =>  "110/pop3/notls" )
);


 $mailbox = array();
 while (list($k,$v) = each($mailaccounts)) {
   $mboxspec = "{".$v["server"].":".$v["port"].($v["flag"]==""?"":"/".$v["mode"])."}".$v["box"];
   $cnew = $cinbox = "-";
   $name = $v["descr"];
   $account = $v["account"];
   if ( $v["check"])  {
     
     $mbox = imap_open($mboxspec, $v["account"], $v["pass"]);
     
     if (!$mbox) {
       // erro message ?
     } else {
       $s = imap_check($mbox);
       $cnew = $cinbox = 0;
       $new = array();
       $old = array();
       if ($s->Nmsgs>0) {
	 for ($i = imap_num_msg($mbox); $i >= 1; $i--) {
	   $cinbox++;
	   $header = imap_headerinfo($mbox, $i, 80, 80);
	   if ($header->Unseen == 'U' || $header->Recent == 'N') {
	     $new[]= array( "from" => textfrom($header->fromaddress),
			      "title" => textmsg($header->fetchsubject));
	     $cnew++;
	   } else {
	     $old[]= array( "from" => textfrom($header->fromaddress),
			      "title" => textmsg($header->fetchsubject));
	   }
	   $action->lay->setBlockData("HEAD$k", $new);
	 }
       }
       imap_close($mbox);
     }
     $mailbox[] = array( "imbox" => $k,
			 "showh" => ($cnew>0 ? true : false),
			 "newM" => ($cnew>0 ? true : false),
			 "name" => $name, 
			 "account" => $account, 
			 "new" => $cnew, "inbox" => $cinbox );
   }
 }
 $action->lay->setBlockData("MBOX", $mailbox);
 
 
}

function textfrom($s) {
    return imap_utf8($s);
}
function textmsg($s) {
    return utf8_decode(imap_utf8($s));
}

?>