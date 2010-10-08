<?php
/**
 * Display interface to change state
 *
 * @author Anakeen 2007
 * @version $Id: editchangestate.php,v 1.8 2008/10/02 15:41:45 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



include_once("FDL/Lib.Dir.php");
include_once("FDL/editutil.php");
include_once("FDL/editcard.php");


/**
 * Display editor to fix a document version
 * @param Action &$action current action
 * @global id Http var : document id 
 * @global nstate Http var : next state id
 */
function editchangestate(&$action) {
  $docid = GetHttpVars("id");
  $nextstate = GetHttpVars("nstate");
  $viewext = GetHttpVars("viewext");

  $dbaccess = $action->GetParam("FREEDOM_DB");

  editmode($action);
  $doc=new_doc($dbaccess,$docid,true);
  if (!$doc->isAlive()) $action->exitError(sprintf(_("Document %s is not alive"),$docid));
  if ($doc->wid > 0) {
    $tneed=array();
    $err = $doc->lock(true); // autolock
    if ($err=="") $action->AddActionDone("LOCKFILE",$doc->id);
  
    $wdoc = new_Doc($dbaccess,$doc->wid);
    $wdoc->Set($doc);
  $action->lay->set("noreason",false);
        $action->lay->set("realtransition",true);
    $fstate = $wdoc->GetFollowingStates();  
    foreach ($fstate as $k=>$v) {
      if ($v == $nextstate) {
	$tr=$wdoc->getTransition($doc->state,$v);
$tinputs=array(); 
	if (is_array($tr["ask"])) {
	  foreach ($tr["ask"] as $ka=>$va) {
	    $oa = $wdoc->getAttribute($va);
	    if ($oa) {
	      if ($oa->needed) $tneed[$oa->id]=$oa->getLabel();
	      if ($oa->usefor=='Q') {
		$wval=$wdoc->getParamValue($oa->id);
		$wval=$wdoc->getValueMethod($wval);
	      } else {
		$wval=$wdoc->getValue($oa->id);
	      }
	      if ($edittpl=$oa->getOption("edittemplate")) {
	           $input=sprintf("[ZONE FDL:EDITTPL?id=%d&famid=%d&wiid=%d&zone=%s]",$wdoc->id, $wdoc->fromid,$doc->id,$edittpl);	          
	      } else {
	          $input=getHtmlInput($wdoc,$oa,$wval,"","",true);
	      }
	      $tinputs[]=array("alabel"=>$oa->getLabel(),
			       "labelclass"=>($oa->needed)?"FREEDOMLabelNeeded":"FREEDOMLabel",
			       "avalue"=>$input,
			       "aid"=>$oa->id,
			       "idisplay"=>($oa->visibility=="H")?"none":"");
	      if ($oa->needed) $tneed[$oa->id]=$oa->getLabel();
	    }
	  }
	}
	$action->lay->set("noreason",($tr["nr"]==true));
    $action->lay->set("viewext",$viewext);
	$action->lay->setBlockData("FINPUTS",$tinputs);
      }
    }

    setNeededAttributes($action,$wdoc);
    $action->lay->set("tonewstate",sprintf(_("to the %s state"),_($nextstate)));
    if ($tr) {
    if ( _($tr["id"]) == $tr["id"]) $lnextstate=sprintf(_("to %s"),_($nextstate));
    else $lnextstate=_($tr["id"]);
    } else {
        $action->lay->set("realtransition",false);
        $lnextstate=sprintf(_("to %s"),_($nextstate));
    }

    $action->lay->set("tostate",ucfirst($lnextstate));
    $action->lay->set("wcolor",	$wdoc->getColor($nextstate));
		      
    $action->lay->Set("Wattrntitle",	 "'".implode("','",str_replace("'","&rsquo;",$tneed))."'");
    $action->lay->Set("Wattrnid",	 "'".implode("','",array_keys($tneed))."'");
    $action->lay->set("docid",$doc->id);
    $action->lay->set("thetitle",sprintf(_("Change state to %s"),_($nextstate)));
    $action->lay->set("nextstate",$nextstate);

  	$style = $action->parent->getParam("STYLE");

	$action->parent->AddCssRef("STYLE/DEFAULT/Layout/EXT-ADAPTER-SYSTEM.css");
	if(file_exists($action->parent->rootdir."/STYLE/$style/Layout/EXT-ADAPTER-USER.css")) {
		$action->parent->AddCssRef("STYLE/$style/Layout/EXT-ADAPTER-USER.css");
	}
	else {
		$action->parent->AddCssRef("STYLE/DEFAULT/Layout/EXT-ADAPTER-USER.css");
	}
    
  }
}
?>