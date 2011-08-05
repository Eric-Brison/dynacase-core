<?php
/**
 * Goto ask for a document
 *
 * @author Anakeen 2000 
 * @version $Id: fdl_card.php,v 1.42 2008/12/02 15:20:52 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



include_once("FDL/Class.Dir.php");


/**
 * View a document
 * @param Action &$action current action
 * @global id Http var : document identificator where use the ask
 * @global waskid Http var : document identificator of the ask
 */
function gotowask(&$action) {
  $docid = GetHttpVars("id");
  $dbaccess = $action->GetParam("FREEDOM_DB");
  if ($docid=="") $action->exitError(_("no document reference"));
  $doc = new_Doc($dbaccess, $docid);
  if (!$doc->isAlive()) $action->exitError(sprintf(_("unknow document reference '%s'"),GetHttpVars("docid")));
  
  $err=$doc->control("view"); 
  if ($err) $action->exitError($err);

  if (! $doc->state) $action->exitError(sprintf(_("document '%s' [revision %d] has no state"),$doc->getTitle(),$doc->revision));
    
  $action->lay->set("comment",sprintf(_("The latest document %s waiting for a answer"),_($doc->state)));
  $action->lay->set("lstate",_($doc->state));
  $action->lay->set("title",$doc->getTitle());
  $action->lay->set("doctitle",sprintf(_("goto latest %s"),_($doc->state)));
  $action->lay->set("id",$doc->id);
}
?>