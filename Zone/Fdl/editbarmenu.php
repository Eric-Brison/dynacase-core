<?php
/**
 * Specific menu for family
 *
 * @author Anakeen 2000 
 * @version $Id: editbarmenu.php,v 1.6 2008/08/14 09:59:14 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */


include_once("FDL/popupdocdetail.php");
include_once("FDL/popupfamdetail.php");


function editbarmenu(&$action) {
  $docid = GetHttpVars("id");
  $zonebodycard = GetHttpVars("zone"); // define view action  
  $rzone = GetHttpVars("rzone"); // special zone when finish edition
  $rvid = GetHttpVars("rvid"); // special zone when finish edition
  $usefor = GetHttpVars("usefor"); // default values for a document
  $rtarget = GetHttpVars("rtarget","_self"); // special zone when finish edition return target
  $classid = GetHttpVars("classid",getDefFam($action)); // use when new doc or change class
  $action->lay->Set("SELFTARGET",($rtarget=="_self"));
  $dbaccess = $action->GetParam("FREEDOM_DB");

  $action->lay->Set("id",$docid);
  if (($docid === 0) || ($docid === "") || ($docid === "0") ) {
    $action->lay->Set("editaction", _("Create"));
    if ($usefor=="D") $action->lay->Set("editaction", _("Save default values"));
    if ($usefor=="Q") $action->lay->Set("editaction", _("Save parameters"));
      $doc= createDoc($dbaccess,$classid);
  } else {
    $doc = new_Doc($dbaccess, $docid);
    $action->lay->Set("editaction", _("Save"));
    $action->lay->Set("id",$doc->id);
  }

  if ($zonebodycard == "") {
    if ((! $docid) && $doc->defaultcreate!="") $zonebodycard = $doc->defaultcreate;
    else $zonebodycard = $doc->defaultedit;
  }

  $action->lay->Set("boverdisplay", "none");
  $action->lay->Set("INPUTCONSTRAINT",false);
  $action->lay->Set("rzone", $rzone);
  $action->lay->Set("rvid", $rvid);
  $action->lay->Set("admin", ($action->user->id==1));
  $action->lay->Set("NOSAVE", (preg_match("/[A-Z]+:[^:]+:V/", $zonebodycard, $reg)));
  if (GetHttpVars("viewconstraint")=="Y") {
    $action->lay->Set("bconsdisplay", "");
    if ($action->user->id==1) {
      $action->lay->Set("INPUTCONSTRAINT",true);
      $action->lay->Set("boverdisplay", ""); // only admin can do this
    }
    
  } else {
    // verify if at least on attribute constraint
    
    $action->lay->Set("bconsdisplay", "none");
    /*
    $listattr = $doc->GetNormalAttributes();
    foreach ($listattr as $k => $v) {
      if ($v->phpconstraint != "")  {
	$action->lay->Set("bconsdisplay", "");
	break;
      }
    }
    */
  }

  $taction=array();
  
  $listattr = $doc->GetActionAttributes();
  foreach ($listattr as $k => $v) {
    if (($v->mvisibility != "H")&&($v->mvisibility != "R")) {
      $mvis=MENU_ACTIVE;
      if ($v->precond != "") $mvis=$doc->ApplyMethod($v->precond,MENU_ACTIVE);
      if ($mvis == MENU_ACTIVE) {
	$taction[$k]=array("wadesc"=>$v->getOption("llabel"),
			   "walabel"=>ucfirst($v->getLabel()),
			   "waction"=>$v->waction,
			   "wtarget"=>$v->id,
			   "wapplication"=>$v->wapplication);
      }
    }
  }

  $action->lay->setBlockData("WACTION",$taction);
 
 

}