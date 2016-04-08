<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: freedom_access.php,v 1.7 2005/06/28 08:37:46 eric Exp $
 * @package FDL
 * @subpackage GED
 */
/**
 */
// ---------------------------------------------------------------
// $Id: freedom_access.php,v 1.7 2005/06/28 08:37:46 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/freedom_access.php,v $
// ---------------------------------------------------------------
include_once ("FDL/Class.Doc.php");
include_once "FDL/editutil.php";
// -----------------------------------
function freedom_access(Action & $action)
{
    // -----------------------------------
    // export all selected card in a tempory file
    // this file is sent by dowload
    // -----------------------------------
    // Get all the params
    $dbaccess = $action->dbaccess;
    $docid = $action->getArgument("id");
    $userId = $action->getArgument("userid");
    if (!$userId) {
        $duid = $action->getArgument("_userid");
        if ($duid) {
            $userId = User::getUidFromFid($duid);
        }
    }
    if (!$userId) {
        $userId = $action->user->id;
    }
    
    $doc = new_Doc($dbaccess, $docid);
    // test if current user can view ACL
    $err = $doc->Control("viewacl");
    if ($err != "") $action->exitError($err);
    editmode($action);
    $action->lay->Set("title", $doc->getHtmlTitle());
    // contruct user id list
    $ouser = new Account('', $userId);
    if (!$ouser->isAffected()) $action->exitError(sprintf(_("unknow user #%s") , $userId));
    $tiduser = $ouser->GetUserAndGroupList();
    
    $action->lay->Set("docid", $doc->id);
    $action->lay->Set("userid", $ouser->id);
    
    $action->lay->Set("toProfil", $doc->getDocAnchor($doc->id, 'account', true, false, false, 'latest', true));
    if ($doc->dprofid) {
        $action->lay->Set("dynamic", true);
        $action->lay->Set("dprofid", $doc->dprofid);
        $action->lay->Set("ComputedFrom", _("Computed from profil"));
        $action->lay->Set("toDynProfil", $doc->getHTMLTitle($doc->dprofid));
    } elseif ($doc->profid != $doc->id) {
        $action->lay->Set("dynamic", true);
        $action->lay->Set("dprofid", $doc->profid);
        $action->lay->Set("ComputedFrom", _("Linked from profil"));
        $action->lay->Set("toDynProfil", $doc->getHTMLTitle($doc->profid));
    } else {
        $action->lay->Set("dynamic", false);
    }
    if ($doc->isRealProfile()) {
        $action->lay->Set("profprefix", _("Profile of"));
        $origin = $action->lay->get("toProfil");
        $action->lay->Set("toProfil", preg_replace('/href="([^"]*)"/', 'href="?app=FREEDOM&action=FREEDOM_GACCESS&id=' . $doc->id . '"', $origin));
    } else {
        $action->lay->Set("profprefix", _("Document Profile"));
    }
    
    $action->lay->Set("fid", $doc->getDocAnchor($ouser->fid, 'account', true, false, false, 'latest', true));
    $action->lay->Set("userid", ($userId == 1) ? $tiduser[0]->id : $userId);
    $action->lay->Set("username", User::getDisplayName($userId));
}
