<?php
/**
 * Functions to un-affect document to an user
 *
 * @author Anakeen 2000 
 * @version $Id: desaffect.php,v 1.2 2006/08/11 15:48:17 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage 
 */
 /**
 */



include_once("FDL/Class.Doc.php");
include_once("FDL/mailcard.php");

/**
 * Edition to un-saffect document
 * @param Action &$action current action
 * @global id Http var : document id to affect
 * @global _id_affectuser Http var : user identificator to affect
 * @global _actioncomment Http var : description of the action
 */
function desaffect(&$action) {  
  $docid = GetHttpVars("id"); 
  $dbaccess = $action->GetParam("FREEDOM_DB");

  $doc=new_doc($dbaccess,$docid);
  if (! $doc->isAlive()) $action->exitError(sprintf(_("document #%s not found. Unaffectation aborded"),$docid));


  $err=$doc->unallocate();
  if ($err != "") $action->exitError($err);
  
  if ($err == "") {
    $action->AddActionDone("UNLOCKDOC",$doc->id);

    $action->addWarningMsg(sprintf(_("document %s has been unaffected"),$doc->title,$docu->title));

  }

  redirect($action,GetHttpVars("redirect_app","FDL"),
	   GetHttpVars("redirect_act","FDL_CARD&latest=Y&refreshfld=Y&id=".$doc->id),
	   $action->GetParam("CORE_STANDURL"));

}
?>