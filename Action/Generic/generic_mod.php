<?php
/**
 * Modify a document
 *
 * @author Anakeen 2000 
 * @version $Id: generic_mod.php,v 1.34 2008/03/14 13:58:03 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */




include_once("FDL/modcard.php");

include_once("FDL/Class.DocFam.php");
include_once("FDL/Class.Dir.php");


// -----------------------------------
function generic_mod(&$action) {
  // -----------------------------------

  // Get all the params      
  $dirid=GetHttpVars("dirid",0);
  $docid=GetHttpVars("id",0); 
  $catgid=GetHttpVars("catgid",0); 
  $retedit=(GetHttpVars("retedit","N")=="Y"); // true  if return need edition
  $noredirect=(GetHttpVars("noredirect")=="1"); // true  if return need edition
  $quicksave=(GetHttpVars("quicksave")=="1"); // true  if return need edition
  $rzone = GetHttpVars("rzone"); // special zone when finish edition
  $rvid = GetHttpVars("rvid"); // special zone when finish edition
  $viewext = GetHttpVars("viewext")=="yes"; // special zone when finish edition

  $dbaccess = $action->GetParam("FREEDOM_DB");
  
  $err = modcard($action, $ndocid, $info); // ndocid change if new doc
  if (!$noredirect)  $action->AddWarningMsg($err);
  
  if ($err=="") {
    $doc= new_Doc($dbaccess, $ndocid);
    if ($docid > 0) AddLogMsg(sprintf(_("%s has been modified"),$doc->title));

    if ($docid == 0) { // new file => add in a folder
   
      AddLogMsg(sprintf(_("%s has been created"),$doc->title));
   
      $cdoc = $doc->getFamDoc();


      //if (($cdoc->dfldid>0) && ($dirid==0))  $dirid=$cdoc->dfldid;// we not insert in defaut folder
    

      if ($dirid > 0) {
	$fld = new_Doc($dbaccess, $dirid);
	if ($fld->locked == -1) { // it is revised document
	  $dirid= $fld->latestId();
	  if ($dirid != $fld->id) $fld = new_Doc($dbaccess, $dirid);
	}
	if (method_exists($fld,"AddFile")) {
	  $err=$fld->AddFile($doc->id); 
	  if ($err!="") {
	    //try in home folder	    
	    $home = $fld->getHome(false);      
	    if ($home && ($home->id>0)) {
	      $fld = $home; 
	      $err=$fld->AddFile($doc->id);
	    }
	  }

	  if ($err != "") {
	    $action->AddLogMsg($err);
	  } else {
	    if (($doc->doctype=='D')|| ($doc->doctype=='S')) $action->AddActionDone("ADDFOLDER",$fld->initid);
	    else $action->AddActionDone("ADDFILE",$fld->initid);
	  }
	} else {
	  //try in home folder	 
	  $fld = new_Doc($dbaccess,UNCLASS_FLD);
	    $home = $fld->getHome(false);      
	    if ($home && ($home->id>0)) {
	      $fld = $home; 
	      $err=$fld->AddFile($doc->id);
	    }
	}
      }     
    } 
  }
  


  if ($noredirect) {
    $action->lay->set("id",$ndocid);
    $action->lay->set("constraintinfo",json_encode($info));
    $action->lay->set("quicksave",$quicksave);
    if ($err=="-") $err="";
    $action->lay->set("error",json_encode($err));
    if ($retedit) $action->lay->set("url",sprintf("?app=%s&action=%s",getHttpVars("redirect_app","GENERIC"),getHttpVars("redirect_act","GENERIC_EDIT")));
    else {
        if ($viewext) $action->lay->set("url",sprintf("?app=%s&action=%s",getHttpVars("redirect_app","FDL"),getHttpVars("redirect_act","VIEWEXTDOC$zone&refreshfld=Y&id=$ndocid")));
        else $action->lay->set("url",sprintf("?app=%s&action=%s",getHttpVars("redirect_app","FDL"),getHttpVars("redirect_act","FDL_CARD$zone&refreshfld=Y&id=$ndocid")));
    }
    return;
  }
  
  
  if ($ndocid==0) {
    redirect($action,GetHttpVars("redirect_app","GENERIC"),
             GetHttpVars("redirect_act","GENERIC_LOGO"),
             $action->GetParam("CORE_STANDURL"));
  }
  if ($retedit) {
    redirect($action,GetHttpVars("redirect_app","GENERIC"),
	     GetHttpVars("redirect_act","GENERIC_EDIT&id=$ndocid"),
	     $action->GetParam("CORE_STANDURL"));
  } else {
  
    if ($rzone != "") $zone="&zone=$rzone";
    else $zone="";
    if ($rvid != "") $zone="&vid=$rvid";

    // $action->register("reload$ndocid","Y"); // to reload cached client file
    redirect($action,GetHttpVars("redirect_app","FDL"),
	     GetHttpVars("redirect_act","FDL_CARD$zone&refreshfld=Y&id=$ndocid"),
	     $action->GetParam("CORE_STANDURL"));
  }
  
}


?>
