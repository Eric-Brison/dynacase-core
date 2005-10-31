<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: ng_rss.php,v 1.2 2005/10/31 14:05:56 marc Exp $
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
    
  $rss = GetHttpVars("rss", "");

  if ($rss=="") {

    $inforss = array();
    $trss = array();
    $itrss = 0;
    foreach ($rssf as $k => $v) {
      $rss =& new XML_RSS($v);
      $rss->parse();
      
      $rinfo =  $rss->getChannelInfo();
      $inforss[$itrss] = array( "rssid" => $itrss, "rsslink" => $v, "rsstitle" => utf8_decode($rinfo["title"]));    
      $trss[$itrss] = array( "rlink" => $rinfo["link"], "rtitle" => utf8_decode($rinfo["title"]), "rdescr" => $rinfo["descr"] );    
      $trss[$itrss]["ix"] = $itrss;
      
      $itrss++;
    }
    $action->lay->setBlockData("LISTRSS", $trss);
    $action->lay->setBlockData("RSSLINK", $inforss);
    $action->lay->set("ShowHeader", true);
    $action->lay->set("ShowContent", false);

  } else {
    
    $rss =& new XML_RSS($rssf[$rss]);
    $rss->parse();
    $rssc = $rss->getItems();
    $max = 100;
    $ic = 0;
    $tr = array();
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
