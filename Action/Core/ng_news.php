<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: ng_news.php,v 1.3 2005/10/31 14:05:56 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
include_once('WGCAL/Lib.wTools.php');

function ng_news(&$action) {

  $dbaccess = $action->GetParam("FREEDOM_DB");
  $fam = createDoc($dbaccess, "NGNEWS", false);
  $action->lay->set("icon", $fam->getIcon());

  $filter = array();
  $d = strftime("%d/%m/%Y 00:00:00", time());
  $filter[] = "(ngn_peremption is null) OR (ngn_peremption >= '$d' )";
  $news = getChildDoc($dbaccess, 0, 0, "ALL", $filter, $action->user->id, 
		      "TABLE", "NGNEWS", false, "revdate desc", true);

  $markcolor = array( "orange", "red" );
  $tn = array();
  $it = 0;
  if (count($news)>0) {
    foreach ($news as $k => $v) {
      $tn[] = array( "id" => $v["id"],
		     "title" => $v["ngn_title"],
		     "mark" => ($v["ngn_state"]>0?true:false),
		     "color" => $markcolor[$v["ngn_state"]],
		     "icon" => Doc::getIcon($v["icon"]),
		     "content" => $v["ngn_text"], //		     "content" => substr($v["ngn_text"],0,100),
		     "mail" => $v["ngn_authormail"],
		     "author" => $v["ngn_author"],
		     "date" => strftime("%d/%m/%y",$v["revdate"] ),
		     "edit" => ($action->user->fid==$v["ngn_authorid"]?true:false)
		     );
      $it++;
    }
  }
  $action->lay->setBlockData("NEWS", $tn);
}
?>
      

