<?php
/**
 * Edition to affect document
 *
 * @author Anakeen 2000 
 * @version $Id: editaffect.php,v 1.6 2007/01/15 14:39:46 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage 
 */
 /**
 */


include_once("FDL/Class.Doc.php");
include_once("FDL/Lib.Dir.php");
include_once("FDL/editutil.php");

// -----------------------------------
// -----------------------------------
/**
 * Edition to affect document
 * @param Action &$action current action
 * @global id Http var : document id to affect
 * @global viewdoc Http var : with preview of affect document [Y|N]
 */
function editaffect(&$action) {
  $docid = GetHttpVars("id"); 
  $viewdoc = (GetHttpVars("viewdoc","N")=="Y"); 
  $dbaccess = $action->GetParam("FREEDOM_DB");

  $doc=new_doc($dbaccess,$docid);
  editmode($action);

  $action->lay->Set("id",$docid);
  $action->lay->Set("title",$doc->title);
  $action->lay->set("VIEWDOC",$viewdoc);
  $action->lay->set("affecttitle",sprintf(_("Affectation for %s"),$doc->title));
  
  // search free states
  $sqlfilters=array("(frst_famid='".$doc->fromid."') or (frst_famid is null) or (frst_famid='')");
  $tfree = getChildDoc($dbaccess,0,"0","ALL",$sqlfilters, $action->user->id, "TABLE","FREESTATE");
  $tstate=array();
  if ($doc->wid == 0) {
    foreach ($tfree as $k=>$v) {
      $tstate[]=array("fstate"=>$v["initid"],
		      "lstate"=>$v["title"],
		      "color"=>getv($v,"frst_color"),
		      "dstate"=>nl2br(getv($v,"frst_desc")));
    }
  }
  $action->lay->set("viewstate",($doc->wid == 0));
  $state=$doc->getState();
  if ($state) {
    $action->lay->set("textstate",sprintf(_("From %s state to"),$state));
    $action->lay->set("colorstate",$doc->getStateColor("transparent"));
  } else {
    $action->lay->set("textstate",_("New state"));
    $action->lay->set("colorstate","transparent");
  }

  $action->lay->setBlockData("freestate",$tstate);

  
}
?>