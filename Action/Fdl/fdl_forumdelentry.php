<?php

/**
 * FDL Forum edition action
 *
 * @author Anakeen 2000 
 * @version $Id: fdl_forumdelentry.php,v 1.3 2008/02/19 14:08:53 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */
include_once("FDL/Class.Doc.php");
include_once("FDL/freedom_util.php");

function fdl_forumdelentry(&$action) {

  $docid  = GetHttpVars("docid", "");
  $entrid = GetHttpVars("eid",   -1);
  $start  = GetHttpVars("start", -1);

  $dbaccess = GetParam("FREEDOM_DB");


  if ($docid=="") $action->exitError(_("no document reference"));
  $doc = new_Doc($dbaccess, $docid);
  if (! $doc->isAffected()) $action->exitError(sprintf(_("cannot see unknow reference %s"),$docid));
  if ($doc->locked == -1) { // it is revised document
    $docid = $doc->latestId();
    if ($docid != $doc->id) $doc = new_Doc($dbaccess, $docid);
  }
  if ($doc->Control("edit")!="" && $doc->Control("forum")!="") 
    $action->exitError(sprintf(_("you don't have privilege to edit forum for document %s"),$doc->title));

  $forid = abs(intval($doc->forumid));
  $forum = new_Doc($dbaccess, $forid);
  if (! $forum->isAffected()) $action->exitError(sprintf(_("cannot see unknow forum reference %s"),$forid));

  $doc->disableEditControl();
  
  $forum->removeentry($entrid);

  redirect($action,"FDL","IMPCARD&sole=Y&zone=FDL:FORUM_VIEW:S&id=".$forum->id."&start=".$start);
}


?>