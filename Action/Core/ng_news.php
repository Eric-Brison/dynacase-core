<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: ng_news.php,v 1.1 2005/10/19 17:24:11 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
include_once('WGCAL/Lib.wTools.php');

function ng_news(&$action) {

  $dbaccess = $action->GetParam("FREEDOM_DB");
  $filter = array();
  $d = strftime("%d/%m/%Y 00:00:00", time());
  $filter[] = "(ngn_peremption is null) OR (ngn_peremption >= '$d' )";
  $news = getChildDoc($dbaccess, 0, 0, "ALL", $filter, $action->user->id, 
		      "TABLE", "NGNEWS", false, "revdate desc", true);

  $markcolor = array( "orange", "red" );
  $tn = array();
  if (count($news)>0) {
    foreach ($news as $k => $v) {
      $tn[] = array( "title" => $v["ngn_title"],
		     "mark" => ($v["ngn_state"]>0?true:false),
		     "color" => $markcolor[$v["ngn_state"]],
		     "icon" => Doc::getIcon($v["icon"]),
		     "content" => substr($v["ngn_text"],0,100),
		     "mail" => $v["ngn_authormail"],
		     "author" => $v["ngn_author"],
		     "date" => strftime("%a %d %B %Y",$v["revdate"] ) 
		     );
    }
  }
  $action->lay->setBlockData("NEWS", $tn);
}
?>
      

