<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: ng_rss.php,v 1.3 2005/10/31 15:26:14 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
include_once("XML/RSS.php");
include_once("CORE/Lib.Ng.php");

function ng_rss(&$action) {

  $action->parent->addCssRef("CORE:NG.CSS",true);

  $myportal = ng_myportal();
  $rssf = $myportal->RssList();
//   print_r2($rssf);
    
  $rss = GetHttpVars("rss", "");

  if ($rss=="") {

    $inforss = array();
    $itrss = 1;
    foreach ($rssf as $k => $v) {
      $inforss[$itrss] = array( "rssid" => $itrss, "rsslink" => $v["url"], 
				"rsstitle" => ($v["desc"]=="" ? substr($v["url"],0,30)."..." : $v["desc"]) );    
      $itrss++;
    }
    $action->lay->setBlockData("RSSLINK", $inforss);
    $action->lay->set("ShowHeader", true);
    $action->lay->set("ShowContent", false);

  } else { 
    $rss--;
//     print_r2($rssf); echo "RSS=$rss";
//     echo "Fil   ".$rssf[$rss]["url"]."<br>";
    $tr = array();
    $rss =& new XML_RSS($rssf[$rss]["url"]);
    $rss->parse();
    $rssc = $rss->getItems();
    $max = 100;
    $ic = 0;
    while ($ic<=$max && list($k, $v) = each($rssc)) {
      $tr[$ic] = $v;
      $tr[$ic]["title"] = utf8_decode($v["title"]);
      $ic++;
    }
    $action->lay->setBlockData("RSSNEWS", $tr);
    $action->lay->set("ShowHeader", false);
    $action->lay->set("ShowContent", true);

  }
}
?>
