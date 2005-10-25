<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: ng_rss.php,v 1.1 2005/10/25 08:39:35 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
include_once("XML/RSS.php");

function ng_rss(&$action) {


  $action->parent->addCssRef("CORE:NG.CSS",true);
  $rssf = array( "--",
		 "http://linuxfr.org/backend/news/rss20.rss", 
		 "http://planet.mozilla.org/rss20.xml" 
		 );
    
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
