<?php
/**
 * Document State modification
 *
 * @author Anakeen 2000 
 * @version $Id: modstate.php,v 1.11 2008/10/30 16:11:44 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage GED
 */
 /**
 */

include_once("FDL/Class.Doc.php");
include_once("FDL/modcard.php");

/**
 * Edition to send mail
 * @param Action &$action current action
 * @global id Http var : document id to change
 * @global state Http var : new state
 * @global comment Http var : additionnal comment for history
 * @global force Http var : to force transition [Y|N]
 */
function modstate(&$action) {
  // Get all the params      
  $docid=GetHttpVars("id");
  $state = GetHttpVars("newstate"); // new state
  $comment = GetHttpVars("comment"); // comment
  $comment = rawurldecode($comment);
  $force = (GetHttpVars("fstate","no")=="yes"); // force change

    
  if ( $docid == 0 ) $action->exitError(_("the document is not referenced: cannot apply state modification"));
    
  $dbaccess = $action->GetParam("FREEDOM_DB");
    
  
  // initialise object
  $doc = new_Doc($dbaccess,$docid);
 
  if ($doc->wid > 0) {
    if ($state != "-") {
      $wdoc = new_Doc($dbaccess,$doc->wid);
      $wdoc->Set($doc); 
      $wdoc->disableEditControl(); // only to pass ask parameters
      setPostVars($wdoc);
      $wdoc->enableEditControl();
      $err=$wdoc->ChangeState($state,$comment,$force);
      if ($err != "")  $action->AddWarningMsg($err);
      else $action->info(sprintf("Change state %s [%d] : %s",$doc->title,$doc->id,$state));
    } else {
      if ($comment != "") {
	$doc->addComment($comment); 
	$action->log->info(sprintf("Add comment %s [%d] : %s",$doc->title,$doc->id,$comment));
      }
    }
  } else {
    $action->AddLogMsg(sprintf(_("the document %s is not related to a workflow"),$doc->title));
  }
  
  
  
  redirect($action,GetHttpVars("redirect_app","FDL"),
	   GetHttpVars("redirect_act","FDL_CARD&refreshfld=Y&id=".$doc->id),
	   $action->GetParam("CORE_STANDURL"));
    
 
}

?>
