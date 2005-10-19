<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: ng_todo.php,v 1.1 2005/10/19 17:24:11 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
include_once('WGCAL/Lib.wTools.php');

function ng_todo(&$action) {

  $dbaccess = $action->GetParam("FREEDOM_DB");
  $todoviewday = $action->getParam("WGCAL_U_TODODAYS", -1);
  $todowarn = $action->getParam("WGCAL_U_TODOWARN", 2);

  $today = time();
  
  $filter = array();
  $filter[] = "todo_idowner=".$action->user->fid;
  $todos = getChildDoc($dbaccess, 0, 0, "ALL", $filter, $action->user->id, "TABLE", "TODO", false, "todo_date asc", true);
  $td = array(); $itd = 0;
  foreach ($todos as $k => $v) {
    $d = $v["todo_date"];
    $ctime = mktime(0,0,0,substr($d,3,2),substr($d,0,2),substr($d,6,4));
    if ($ctime<$today) {
      $td[$itd]["color"] = "red";
    } else if ($ctime<($today+($todowarn*24*3600))) {
      $td[$itd]["color"] = "orange";
    } else {
      $td[$itd]["color"] = "#00ff00";
    }
    $td[$itd]["title"] = $v["todo_title"];
    $td[$itd]["date"] = strftime("%d %B %y", $ctime);
    $itd++;
  }
  $action->lay->setBlockData("TODO", $td);
}
?>