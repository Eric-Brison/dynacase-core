<?php
/**
 * View tab in portfolio
 *
 * @author Anakeen 2000 
 * @version $Id: foliotab.php,v 1.9 2008/01/22 16:42:49 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage GED
 */
 /**
 */





include_once("FDL/Lib.Dir.php");
include_once("FDL/freedom_util.php");
include_once('FREEDOM/Lib.portfolio.php');




// -----------------------------------
function foliotab(&$action) {
  // -----------------------------------

  // Get all the params      
  $docid=GetHttpVars("id",0); // portfolio id

  $dbaccess = $action->GetParam("FREEDOM_DB");

  include_once("FDL/popup_util.php");
  $nbfolders=1;
  $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FDL/Layout/common.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/AnchorPosition.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/resizeimg.js");
 
  $doc = new_Doc($dbaccess,$docid);
  if (! $doc->isAffected()) $action->exitError(sprintf(_("document %s not exists"),$docid));
  $action->lay->set("docid",$docid);
  $action->lay->set("dirid",$doc->initid);
  $action->lay->set("title",$doc->title);


  $child = getChildDir($dbaccess,$action->user->id,$doc->initid, false,"TABLE");
  
  if ($action->Read("navigator")=="EXPLORER") { // different tab class for PNG transparency
    $tabonglet = "ongletvgie";
    $tabongletsel = "ongletvsie";
  } else {
    $tabonglet = "ongletvg";
    $tabongletsel = "ongletvs";
  }

  $linktab=$doc->getParamValue("pfl_idlinktab");
  if ($linktab) {
    $linktab=$doc->_val2array($linktab);
    foreach ($linktab as $k=>$id) {
      $tdoc=getTDoc($dbaccess,$id);
      if (controlTdoc($tdoc,"view"))  $child[]=$tdoc;
    }
  }

  $action->lay->set("tabonglets",$tabongletsel);
  $action->lay->set("icon",$doc->getIcon());
  $ttag=array();
  while(list($k,$v) = each($child)) {
	$icolor=getv($v,"gui_color");
      if ($v["initid"] != $doc->initid) {
      $ttag[$v["initid"]] = array(
		      "tabid"=>$v["initid"],
		      "doctype"=>$v["doctype"],
		      "TAG_LABELCLASS" => $v["doctype"]=="S"?"searchtab":"",
		      "tag_cellbgclass"=>($v["id"] ==$docid)?$tabongletsel:$tabonglet,
		      "icolor"=>$icolor,
		      "icontab"=>$doc->getIcon($v["icon"]),
		      "tabtitle"=>str_replace(" ","&nbsp;",$v["title"]));
      }   
  }

  $action->lay->setBlockData("TAG",$ttag);
  $action->lay->setBlockData("nbcol",count($ttag)+1);
}

?>