<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: Method.nPortal.php,v 1.3 2005/10/31 15:26:14 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */
var $defaultview= "FDL:VIEWBODYCARD";
var $defaultedit= "FDL:EDITBODYCARD";


function postCreated() {
    $this->SetProfil($this->id);
    $this->SetControl();
    $err = $this->Modify();
}

function MailChange( $name="", $login="", $passwd="", $server="", $mode="", $flag="", $checked=1, $mlisted=1 ) {

  if ($name=="" || $login=="" || $passwd=="" || $server=="" || $mode=="") return;

  $mname = $this->getTValue("ngp_ma_name");
  $mchecked = $this->getTValue("ngp_ma_checked");
  $mshowlist = $this->getTValue("ngp_ma_mlist");
  $mlogin = $this->getTValue("ngp_ma_login");
  $mpass = $this->getTValue("ngp_ma_password");
  $minbox = $this->getTValue("ngp_ma_inboxname");
  $mserver = $this->getTValue("ngp_ma_server");
  $mmode = $this->getTValue("ngp_ma_mode");
  $mflag = $this->getTValue("ngp_ma_flags");
  
  $idx = count($mname);
  if (count($mname)>0) {
    foreach ($mname as $k => $v) {
      if ($v == $name) $idx = $k;
    }
  }

  $mname[$idx] = $name;
  $mchecked[$idx] = $checked;
  $mshowlist[$idx] = $mlisted;
  $mlogin[$idx] = $login;
  $mpass[$idx] = $passwd;
  $minbox[$idx] = "";
  $mserver[$idx] = $server;
  $mmode[$idx] = $mode;
  $mflag[$idx] = $flag;

    
  $this->setValue("ngp_ma_name", $mname);
  $this->setValue("ngp_ma_checked", $mchecked);
  $this->setValue("ngp_ma_mlist", $mshowlist);
  $this->setValue("ngp_ma_login", $mlogin);
  $this->setValue("ngp_ma_password", $mpass);
  $this->setValue("ngp_ma_inboxname", $minbox);
  $this->setValue("ngp_ma_server", $mserver);
  $this->setValue("ngp_ma_mode", $mmode);
  $this->setValue("ngp_ma_flags", $mflag);

  $this->Modify();

}

function MailAccounts() {
  $macc = array();
  
  $mname = $this->getTValue("ngp_ma_name");
  $mchecked = $this->getTValue("ngp_ma_checked");
  $mshowlist = $this->getTValue("ngp_ma_mlist");
  $mlogin = $this->getTValue("ngp_ma_login");
  $mpass = $this->getTValue("ngp_ma_password");
  $minbox = $this->getTValue("ngp_ma_inboxname");
  $mserver = $this->getTValue("ngp_ma_server");
  $mmode = $this->getTValue("ngp_ma_mode");
  $mflag = $this->getTValue("ngp_ma_flags");
  if (count($mname)>0) {
    foreach ($mname as $k => $v) {
      $macc[] = array( $mname[$k],  
		       $mchecked[$k],
		       $mshowlist[$k],
		       $mlogin[$k],
		       $mpass[$k],
		       $minbox[$k],
		       $mserver[$k],
		       $mmode[$k],
		       $mflag[$k] );
    }
  }
  return $macc;
}

function RssList() {
  $urls = $this->getTValue("ngp_rss_url");
  $descr = $this->getTValue("ngp_rss_desc");
  $rss = array();
  foreach ($urls as $k => $v) {
    $rss[] = array( "desc" => $descr[$k], "url" => $urls[$k] );
  }
  return $rss;
}

?>