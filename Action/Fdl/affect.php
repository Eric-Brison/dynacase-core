<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 * Functions to allocate document to an user
 *
 * @author Anakeen 2000
 * @version $Id: affect.php,v 1.6 2009/01/08 17:47:08 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.Doc.php");
include_once ("FDL/mailcard.php");
/**
 * Edition to allocate document
 * @param Action &$action current action
 * @global id Http var : document id to affect
 * @global _id_affectuser Http var : user identificator to affect
 * @global _actioncomment Http var : description of the action
 */
function affect(&$action)
{
    $docid = GetHttpVars("id");
    $uid = GetHttpVars("_id_affectuser");
    $newstate = GetHttpVars("newstate", -1);
    $commentstate = GetHttpVars("_statecomment");
    $commentaction = GetHttpVars("_actioncomment");
    $dbaccess = $action->GetParam("FREEDOM_DB");
    $revstate = true;
    
    $doc = new_doc($dbaccess, $docid);
    if (!$doc->isAlive()) $action->exitError(sprintf(_("document #%s not found. Affectation aborded") , $docid));
    $wuid = 0;
    if ($uid) {
        $docu = new_doc($dbaccess, $uid);
        if (!$docu->isAlive()) $action->addWarningMsg(sprintf(_("user #%s not found. Affectation aborded") , $uid));
        
        $wuid = $docu->getValue("us_whatid");
        if (!($wuid > 0)) $action->addWarningMsg(sprintf(_("user #%s has not a real account. Affectation aborded") , $uid));
    }
    if ($newstate >= 0) {
        $err = $doc->changeFreeState($newstate, $commentstate);
        if ($err != "") $action->addWarningMsg($err);
        else {
            $revstate = false; // no need to revision one more
            $action->addWarningMsg(sprintf(_("document %s has the new state %s") , $doc->title, $doc->getState()));
        }
    }
    if ($wuid > 0) {
        $err = $doc->allocate($wuid, $commentaction, $revstate);
        if ($err != "") $action->addWarningMsg($err);
        
        if ($err == "") {
            $action->AddActionDone("LOCKFILE", $doc->id);
            
            $action->addWarningMsg(sprintf(_("document %s has been allocate to %s") , $doc->title, $docu->title));
            
            $to = $docu->getValue("us_mail");
            $subject = sprintf(_("allocation for %s document") , $doc->title);
            $err = sendCard($action, $doc->id, $to, "", $subject, "", true, $commentaction, "", "", "htmlnotif");
            if ($err != "") $action->addWarningMsg($err);
        }
    }
    
    redirect($action, GetHttpVars("redirect_app", "FDL") , GetHttpVars("redirect_act", "FDL_CARD&latest=Y&refreshfld=Y&id=" . $doc->id) , $action->GetParam("CORE_STANDURL"));
}
?>