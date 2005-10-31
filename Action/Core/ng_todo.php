<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: ng_todo.php,v 1.3 2005/10/31 15:26:14 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
include_once('WGCAL/Lib.wTools.php');

function ng_todo(&$action) {

  $dbaccess = $action->GetParam("FREEDOM_DB");
  $todoviewday = $action->getParam("WGCAL_U_TODODAYS", -1);
  $todowarn = $action->getParam("WGCAL_U_TODOWARN", 2);
  
  $fam = createDoc($dbaccess, "TODO", false);
  $action->lay->set("icon", $fam->getIcon());

  $today = time();
  $filter = array();
  $filter[] = "todo_idowner=".$action->user->fid;
  $todos = getChildDoc($dbaccess, 0, 0, "ALL", $filter, $action->user->id, "TABLE", "TODO", false, "todo_date desc", true);
  $td = array(); $itd = 0;
  foreach ($todos as $k => $v) {
    $cdate = w_dbdate2ts($v["todo_date"]);
    if ($cdate<$today) {
      $td[$itd]["color"] = "red";
    } else if ($cdate<($today+($todowarn*24*3600))) {
      $td[$itd]["color"] = "orange";
    } else {
      $td[$itd]["color"] = "#00ff00";
    }
    $td[$itd]["title"] = $v["todo_title"];
    $td[$itd]["date"] = substr($v["todo_date"],0,10);
  }
  $action->lay->setBlockData("TODO", $td);
}
?>