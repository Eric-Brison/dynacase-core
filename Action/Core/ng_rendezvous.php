<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: ng_rendezvous.php,v 1.2 2005/10/19 17:19:58 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
include_once("WGCAL/Api.WGCal.php");

function ng_rendezvous(&$action) {


  $rvw = WgcalGetWaitingRendezVous();
  if (count($rvw)==0) {
    $action->lay->set("noWRv", true);
  } else {
    $te = array(); $ite = 0;
    $action->lay->set("noWRv", false);
    foreach ($rvw as $ke => $ve) {
      $te[$ite]["hour"] = substr($ve["calev_start"], 11, 5);
      $te[$ite]["title"] = $ve["calev_evtitle"];
      $te[$ite]["owner"] = ($action->user->fid==$ve["calev_ownerid"] ? "" : $ve["calev_owner"]);
      $ite++;
    }
    $action->lay->setBlockData("RVW", $te );
  }


  $rv = WgcalGetMyNextRendezVous();
  if (count($rv)==0) {
    $action->lay->set("noRv", true);
  } else {
    $action->lay->set("noRv", false);
    $tday = array();
    foreach ($rv as $k => $v) {
      $tday[] = array ( "date" => strftime("%A %d %B %Y", $v["date"]), "iday" => $v["date"]);
      $te = array(); $ite = 0;
      foreach ($v["event"] as $ke => $ve) {
	$te[$ite]["hour"] = substr($ve["calev_start"], 11, 5);
	$te[$ite]["title"] = $ve["calev_evtitle"];
	$te[$ite]["owner"] = ($action->user->fid==$ve["calev_ownerid"] ? "" : $ve["calev_owner"]);
	$ite++;
      }
      $action->lay->setBlockData("RV".$v["date"], $te );
    }
    $action->lay->setBlockData("DAYS", $tday);
  }

}
?>