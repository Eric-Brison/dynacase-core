<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Functions to un-affect document to an user
 *
 * @author Anakeen
 * @version $Id: desaffect.php,v 1.2 2006/08/11 15:48:17 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.Doc.php");
include_once ("FDL/mailcard.php");
/**
 * Edition to un-saffect document
 * @param Action &$action current action
 * @global id int Http var : document id to affect
 * @global _id_affectuser int Http var : user identifier to affect
 * @global _actioncomment string Http var : description of the action
 */
function desaffect(&$action)
{
    $docid = GetHttpVars("id");
    $dbaccess = $action->dbaccess;
    
    $doc = new_doc($dbaccess, $docid);
    if (!$doc->isAlive()) $action->exitError(sprintf(_("document #%s not found. Unaffectation aborded") , $docid));
    
    $err = $doc->unallocate();
    if ($err != "") $action->exitError($err);
    
    if ($err == "") {
        $action->AddActionDone("UNLOCKDOC", $doc->id);
        
        $action->addWarningMsg(sprintf(_("document %s has been unaffected") , $doc->title));
    }
    
    redirect($action, GetHttpVars("redirect_app", "FDL") , GetHttpVars("redirect_act", "FDL_CARD&latest=Y&refreshfld=Y&id=" . $doc->id) , $action->GetParam("CORE_STANDURL"));
}
