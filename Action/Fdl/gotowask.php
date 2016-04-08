<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Goto ask for a document
 *
 * @author Anakeen
 * @version $Id: fdl_card.php,v 1.42 2008/12/02 15:20:52 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.Dir.php");
/**
 * View a document
 * @param Action &$action current action
 * @global id int Http var : document identifier where use the ask
 * @global waskid int Http var : document identifier of the ask
 */
function gotowask(Action & $action)
{
    $docid = GetHttpVars("id");
    $dbaccess = $action->dbaccess;
    if ($docid == "") $action->exitError(_("no document reference"));
    $doc = new_Doc($dbaccess, $docid);
    if (!$doc->isAlive()) $action->exitError(sprintf(_("unknow document reference '%s'") , GetHttpVars("docid")));
    
    $err = $doc->control("view");
    if ($err) $action->exitError($err);
    
    if (!$doc->state) $action->exitError(sprintf(_("document '%s' [revision %d] has no state") , $doc->getTitle() , $doc->revision));
    
    $action->lay->eSet("comment", sprintf(_("The latest document %s waiting for a answer") , _($doc->state)));
    $action->lay->rSet("title", urlencode($doc->getTitle()));
    $action->lay->eSet("doctitle", sprintf(_("goto latest %s") , _($doc->state)));
    $action->lay->rSet("id", $doc->id);
}
