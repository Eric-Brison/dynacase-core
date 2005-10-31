<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: ng_rssedit.php,v 1.1 2005/10/31 15:33:36 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
include_once("XML/RSS.php");
include_once("CORE/Lib.Ng.php");

function ng_rssedit(&$action) {

  $tgt = GetHttpVars("tg", "rssfed");
  $action->lay->set("atgt", $tgt);

  $myportal = ng_myportal();
  $rssf = $myportal->RssList();
  $rsst = array();
  if (count($rssf)>0) {
    foreach ($rssf as $k => $v) {
      $rsst[] = array( "rssid" => $k, "rsstitle" => ($v["desc"]==""?$v["url"]:$v["desc"]), "rssurl" => $v["url"]);
    }
  }
  $action->lay->setBlockData("RSS", $rsst);

}

?>
