<?php
/**
 * UnTrash document
 *
 * @author Anakeen 2006
 * @version $Id: restoredoc.php,v 1.1 2007/10/16 14:07:14 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



include_once("FDL/Lib.Dir.php");


/**
 * Get a doc from the trash
 * @param Action &$action current action
 * @global id Http var : document id to restore
 * @global reload Http var : [Y|N] if Y not xml but redirect to fdl_card
 * @global containt Http var : if 'yes' restore also folder items 
 */
function restoredoc(&$action) {

  $docid = GetHttpVars("id");
  $dbaccess = $action->GetParam("FREEDOM_DB");


  $doc=new_doc($dbaccess,$docid);

  if ($doc->isAffected()) {
    if (! $doc->isAlive()) {
      $err=$doc->revive();
    }
  } else $err=sprintf(_("document [%s] not found"));

  if ($err) $action->lay->set("warning",$err);
    
  redirect($action,"FDL","FDL_CARD&sole=Y&refreshfld=Y&latest=Y&id=$docid");
    				

}
?>