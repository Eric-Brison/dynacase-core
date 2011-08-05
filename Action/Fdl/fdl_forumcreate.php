<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 * FDL Forum creation
 *
 * @author Anakeen 2000
 * @version $Id: fdl_forumcreate.php,v 1.2 2007/10/17 10:15:39 marc Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage
 */
/**
 */
include_once ("FDL/Class.Doc.php");
include_once ("FDL/freedom_util.php");

function fdl_forumcreate(&$action)
{
    
    $docid = GetHttpVars("docid", "");
    
    $dbaccess = GetParam("FREEDOM_DB");
    
    if ($docid == "") $action->exitError(_("no document reference"));
    $doc = new_Doc($dbaccess, $docid);
    if (!$doc->isAffected()) $action->exitError(sprintf(_("cannot see unknow reference %s") , $docid));
    
    if ($doc->Control("edit") != "" && $doc->Control("forum") != "") $action->exitError(sprintf(_("you don't have privilege to edit forum for document %s") , $doc->title));
    
    $doc->disableEditControl();
    
    $forum = createDoc($dbaccess, "FORUM");
    $forum->setValue("forum_docid", $doc->id);
    $forum->setProfil($doc->profid);
    $forum->Add();
    $forum->postModify();
    
    $doc->forumid = $forum->id;
    $doc->modify(true, array(
        "forumid"
    ));
    
    redirect($action, "FDL", "FDL_CARD&sole=Y&id=" . $doc->id);
}
?>
