<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: ng_rsschange.php,v 1.1 2005/10/31 15:33:36 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
include_once("XML/RSS.php");
include_once("CORE/Lib.Ng.php");

function ng_rsschange(&$action) {

  $newu = GetHttpVars("rss_url", "");
  $newt = GetHttpVars("rss_title", "");

  $myportal = ng_myportal();
  $rssf = $myportal->RssList();
  
  $rssu = $rsst = array();
  foreach ($rssf as $k => $v ) {
    if (!(is_array($_POST["rss_d"]) && $_POST["rss_d"][$k])) {
	$rssu[] = $v["url"];
	$rsst[] = $v["desc"];
    }
  }
  if ($newu!="") {
    $rssu[] = $newu;
    $rsst[] = ($newt==""?$newu:$newt);    
  }

  $myportal->setValue("ngp_rss_url",$rssu );
  $myportal->setValue("ngp_rss_desc",$rsst );
  $myportal->Modify();

  redirect($action, "CORE", "NGMAIN");
}
?>
