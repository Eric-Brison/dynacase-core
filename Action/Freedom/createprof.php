<?php
/**
 * Create profile
 *
 * @author Anakeen 2009
 * @version $Id: $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */




include_once("FDL/Class.Doc.php");
include_once("FDL/Class.Dir.php");
include_once("FDL/Class.DocAttr.php");
include_once("FDL/freedom_util.php");  



function createprof(&$action) {
    
    
  // Get all the params      
  $docid=GetHttpVars("targetid");
  $famid = GetHttpVars("famid");
  $attrid = GetHttpVars("attrid");  
  $redirid = GetHttpVars("redirid");  
    
  if ( $docid == 0 ) $action->exitError(_("the document is not referenced: cannot apply profile access modification"));
    
  $dbaccess = $action->GetParam("FREEDOM_DB");
  
  
  // initialise object
  $doc = new_Doc($dbaccess,$docid);
  if ($attrid != "cprofid") {
    $oa=$doc->getAttribute($attrid);
    if (!$oa) $action->exitError(sprintf(_("attribut %s not found for document %s [%d]"),
				       $attrid,$doc->getTitle(),$doc->id));
  }
  // control modify acl
  $err= $doc->Control("modifyacl");
  if ($err != "")    $action-> ExitError($err);

  
  $tmpdoc=createTmpDoc($dbaccess,$famid);

  switch ($tmpdoc->defDoctype) {
  case 'D':    
    $pdoc=createDoc($dbaccess,"PDIR");
    break;
  case 'S':    
    $pdoc=createDoc($dbaccess,"PSEARCH");
    break;
  case 'F':   
    $pdoc=createDoc($dbaccess,"PDOC");
    break;
  default:
      $action->exitError(_("Automatic profil creation not possible for this kind of family"));
      break;;
  }

  if (! $pdoc) $action->exitError("not allowed to create profil");
  
  $pdoc->setValue("ba_title",sprintf(_("For %s of %s"),$oa?$oa->getLabel():_("document creation"),$doc->title));
  $err=$pdoc->add();

  if ($err=="") {
    $err=$pdoc->setControl($pdoc->id);// change profile    
  }

  if ($err=="") {
    if ($oa) $doc->setValue($attrid,$pdoc->id);
    else  $doc->$attrid=$pdoc->id;
    $err=$doc->modify();		    
    if ($err=="") $doc->addComment(sprintf(_("add new profil %s"),$pdoc->getTitle()));
  }
  
  if ( $err != "" ) $action->exitError($err);
  
  
  
  
  if (! $redirid) $redirid=$docid;
  redirect($action,"FDL","FDL_CARD&props=Y&id=$redirid",
	   $action->GetParam("CORE_STANDURL"));
}




?>
