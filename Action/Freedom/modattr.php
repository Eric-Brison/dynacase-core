<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: modattr.php,v 1.31 2008/06/24 07:57:02 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage GED
 */
 /**
 */



include_once("FDL/Class.Doc.php");
include_once("FDL/Class.DocAttr.php");
include_once("FDL/Lib.Attr.php");
include_once("FDL/Class.DocFam.php");
include_once("FDL/freedom_util.php");  



// -----------------------------------
function modattr(&$action) {


  // Get all the params      
  $docid=GetHttpVars("docid");
  $dirid = GetHttpVars("dirid",0); // directory to place doc if new doc

  $orders= GetHttpVars("order");
  $names= GetHttpVars("name");
  $types= GetHttpVars("type");
  $abstracts= GetHttpVars("abstractyn");
  $needed= GetHttpVars("neededyn");
  $titles= GetHttpVars("titleyn");
  $attrids= GetHttpVars("attrid");
  $frameids= GetHttpVars("frameid");
  $visibilities= GetHttpVars("visibility");
  $links= GetHttpVars("link");
  $phpfiles= GetHttpVars("phpfile");
  $phpfuncs= GetHttpVars("phpfunc");
  $elinks= GetHttpVars("elink");
  $phpconstraint= GetHttpVars("phpconstraint");
  $options= GetHttpVars("options");
  $nattrids= GetHttpVars("nattrid"); // for new attributes

  
  
  $dbaccess = $action->GetParam("FREEDOM_DB");

  $bdfreedomattr = new DocAttr($dbaccess);
  if ( $docid == 0 )
    {
      $doc = new DocFam($dbaccess);
      //---------------------------
      // add new freedom familly
      //---------------------------
      $doc->title = _("new familly document");
      $doc->owner = $action->user->id;
      $doc->locked = $action->user->id; // lock for next modification
      $doc->doctype = 'C'; // it is a new class document
      $doc->fromid = GetHttpVars("classid"); // inherit from
      $doc->profid = "0"; // NO PROFILE ACCESS

      if (GetHttpVars("classid") >0) {
	$cdoc = new_Doc($dbaccess,GetHttpVars("classid") );
	$doc->classname = "";
	$doc->profid = $cdoc->cprofid; // inherit father profile
      }
      $err=$doc-> Add();
      if ($err != "") $action->exitError($err);
      
      

    } 
  else 
    {

      // initialise object
      $doc = new_Doc($dbaccess,$docid);
      
      $doc->lock(true);
      // test object permission before modify values (no access control on values yet)
      $err=$doc-> canEdit();
      if ($err != "")
	$action-> ExitError($err);

      // change class document
      $doc->fromid = GetHttpVars("classid"); // inherit from
      $doc-> Modify();
      
    }

  // ------------------------------
  // update POSGRES attributes
  $oattr0=new DocAttr($dbaccess);
  $oattr0->docid = $doc->initid;
  $tadd=array();
  $tmod=array();
  while(list($k,$v) = each($orders) ) {
      //  print $k.":".$v."<BR>";	  
	  if ($names[$k] != "") {
	    if ($attrids[$k]=="") {
	      $oattr=$oattr0;
	      $oattr->isset=false;
	      $oattr->id = $nattrids[$k];
	    } else {
	      $oattr=new DocAttr($dbaccess,array($doc->initid,strtolower($attrids[$k])));  
	      if (! $oattr->isAffected()) $oattr=new DocAttr($dbaccess,array($doc->initid,':'.strtolower($attrids[$k])));  	      	      
	    }

	    if ($oattr->id=="") print "Error line $k";

	    $oattr->labeltext=stripslashes($names[$k]);
	    $oattr->title=isset($titles[$k])?$titles[$k]:"N";
	    $oattr->abstract=isset($abstracts[$k])?$abstracts[$k]:"N";
	    $oattr->needed=isset($needed[$k])?$needed[$k]:"N";
	    $oattr->type=stripslashes($types[$k]);	    
	    $oattr->frameid=isset($frameids[$k])?$frameids[$k]:"0";
	    $oattr->ordered=isset($orders[$k])?$orders[$k]:"999";
	    $oattr->visibility=$visibilities[$k];
	    $oattr->link=stripslashes($links[$k]);
	    $oattr->phpfile=$phpfiles[$k];
	    $oattr->phpfunc=stripslashes($phpfuncs[$k]);
	    $oattr->elink=stripslashes($elinks[$k]);
	    $oattr->phpconstraint=stripslashes($phpconstraint[$k]);
	    $oattr->options=stripslashes($options[$k]);
	    $oattr->usefor='N';
	    if (!$oattr->isAffected()) {
	      // print "add $names[$k]<BR>";
	      if (isset($nattrids[$k]) && ($nattrids[$k] != ""))
		$oattr->id = $nattrids[$k];
	      $err = $oattr->Add();
	      if ($err=="") $tadd[]=$oattr->id;
	      //	      print($err);
	    } else {
	      //print_r2( "mod $names[$k] ".$visibilities[$k]);
	      $err=$oattr->Modify();
	      if ($err=="") $tmod[]=$oattr->id;
	    }

	  }
	  

	
      
    }

  if (count($tmod)>0) $doc->AddComment(_("Modify Attributes"),HISTO_INFO,"MODATTR");
  if (count($tadd)>0) $doc->AddComment(sprintf(_("Add Attributes : %s"),implode(", ",$tadd)),HISTO_INFO,"MODATTR");
  $wsh = getWshCmd();
  $cmd = $wsh . "--userid={$action->user->id} --api=fdl_adoc --docid=".$doc->initid;

  $err= exec($cmd, $out, $ret);

  $doc->unlock(true);
  if ($ret) $action->exitError($err);
       


  
  redirect($action,"FDL","FDL_CARD&id=".$doc->id,
	   $action->GetParam("CORE_STANDURL"));
}




?>
