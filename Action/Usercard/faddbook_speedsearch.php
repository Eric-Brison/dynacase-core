<?php
/**
 * Freedom Address Book
 *
 * @author Anakeen 2000
 * @version $Id: faddbook_speedsearch.php,v 1.8 2007/09/28 15:24:27 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage USERCARD
 */
 /**
 */

include_once("FDL/freedom_util.php");
include_once("FDL/Lib.Dir.php");

function faddbook_speedsearch(&$action) 
{ 
  $dbaccess = $action->getParam("FREEDOM_DB");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");

  $ws = (GetHttpVars("sallf", "")=="on"?1:0);
  $vtext = GetHttpVars("vtext", "");
  if ($vtext=="") {
    $action->lay->set("vtext", _("search"));
    $action->lay->set("first", "true");
  } else {
    $action->lay->set("vtext", $vtext);
    $action->lay->set("first", "false");
  }

  $searchuser = GetHttpVars("vsuser", 1);
  $action->lay->set("vsuser", $searchuser);
  $action->lay->set("USEL", ($searchuser==1?true:false));
  $searchsoc = GetHttpVars("ssoc", 0);
  $action->lay->set("ssoc", $searchsoc);
  $action->lay->set("SOCSEL", ($searchsoc==1?true:false));

  $sfam1 = GetHttpVars("dfam", $action->getParam("USERCARD_FIRSTFAM"));
  $action->lay->set("dfam", $sfam1);
  $fam1 = new_Doc($dbaccess, $sfam1);
  $action->lay->set("icon1", $fam1->getIcon());

  $sfam2 = $action->getParam("USERCARD_SECONDFAM");
  if ($sfam2) {
    $fam2 = new_Doc($dbaccess, $sfam2);
    $action->lay->set("icon2", $fam2->getIcon());
  }
  $action->lay->set("Result", false);
  $action->lay->set("bCount", false);
  $action->lay->set("Count", "-");


  $searchfam = array();
  if ($searchuser==1) $searchfam[] = $sfam1;
  if ($sfam2 && ($searchsoc==1)) $searchfam[] = $sfam2;

  if (count($searchfam)==0 || $vtext=="") return;

  $filter = array();
  if ($ws) $filter[] = "(svalues ~* '".$vtext."')";
  else $filter[] = "(title ~* '".$vtext."')";
  $cu = array();
  foreach ($searchfam as $ks => $vs) {
    $rq = getChildDoc($dbaccess, 0, 0, 25, $filter, $action->user->id, "TABLE", $vs, true, "title");
    foreach ($rq as $k => $v) {
      $vo=getDocObject($dbaccess,$v);
      $pzabstract = isset($vo->faddbook_resume)?$vo->faddbook_resume:"FDL:VIEWTHUMBCARD";
      $pzcard = (isset($vo->faddbook_card)?$vo->faddbook_card:$vo->defaultview);
      $cu[] = array( "id" => $vo->id, "title" => $vo->title, "fabzone" => $pzcard, "resume" => $vo->viewdoc($pzabstract));
    }
  }
  if (count($cu)>0) {
    $action->lay->set("Result", true);
    $action->lay->set("bCount", true);
    $action->lay->set("Count", count($cu));
  }
  usort($cu, "sortmya");
  $action->lay->setBlockData("Contacts", $cu);

}
function sortmya($a, $b) {
  return strcmp($a["title"], $b["title"]);
}
?>
