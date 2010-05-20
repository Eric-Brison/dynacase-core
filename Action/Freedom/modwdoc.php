<?php
/**
 * Affect a worflow
 *
 * @author Anakeen 2000 
 * @version $Id: modwdoc.php,v 1.6 2009/01/02 17:45:18 eric Exp $
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



// -----------------------------------
function modwdoc(&$action) {
  // -----------------------------------
    
    
    
  // Get all the params      
  $docid=GetHttpVars("docid");
  $current = (GetHttpVars("current","N")=="Y");
  $wid=  GetHttpVars("wid");
    
  if ( $docid == 0 ) $action->exitError(_("the document is not referenced: cannot apply profile access modification"));
    
  $dbaccess = $action->GetParam("FREEDOM_DB");
  
  
  
  // initialise object
  $doc = new_Doc($dbaccess,$docid);
  $doc->wid = $wid; // new default workflow
  
  
  // test object permission before modify values (no access control on values yet)
  $doc->lock(true); // enabled autolock
  $err=$doc-> CanUpdateDoc();
  if ($err != "") $action->ExitError($err);
  
  $doc->Modify();
  
  
  
  $doc->unlock(true); // disabled autolock

  // update document already created to be conform to new workflow
  $doc->exec_query("update doc".$doc->id." set wid=$wid where usefor != 'W'");

  $wdoc= new_Doc($dbaccess,$wid);
  $firststate=$wdoc->firstState;
  $doc->exec_query("update doc".$doc->id." set state='$firststate' where  usefor != 'W' and (state is null or state='')");
  
  
  redirect($action,"FDL","FDL_CARD&id=$docid",$action->GetParam("CORE_STANDURL"));
}




?>
