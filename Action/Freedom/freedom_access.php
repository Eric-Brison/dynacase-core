<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000
 * @version $Id: freedom_access.php,v 1.7 2005/06/28 08:37:46 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage GED
 */
/**
 */
// ---------------------------------------------------------------
// $Id: freedom_access.php,v 1.7 2005/06/28 08:37:46 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/freedom_access.php,v $
// ---------------------------------------------------------------
include_once ("FDL/Class.Doc.php");
// -----------------------------------
function freedom_access(&$action)
{
    // -----------------------------------
    // export all selected card in a tempory file
    // this file is sent by dowload
    // -----------------------------------
    // Get all the params
    $dbaccess = $action->GetParam("FREEDOM_DB");
    $docid = GetHttpVars("id");
    $userId = GetHttpVars("userid", $action->user->id);
    
    $doc = new_Doc($dbaccess, $docid);
    // test if current user can view ACL
    $err = $doc->Control("viewacl");
    if ($err != "") $action->exitError($err);
    
    $action->lay->Set("title", $doc->title);
    // contruct user id list
    
    $ouser = new User();
    $tiduser = $ouser->GetUserAndGroupList();
    $userids = array();
    while (list($k, $v) = each($tiduser)) {
        if ($v->id == 1) continue; // except admin : don't need privilege
        if ($v->id == $userId) $userids[$k]["selecteduser"] = "selected";
        else $userids[$k]["selecteduser"] = "";
        $userids[$k]["suserid"] = $v->id;
        $userids[$k]["descuser"] = $v->firstname . " " . $v->lastname;
    }
    
    $action->lay->Set("docid", $doc->id);
    $action->lay->Set("userid", ($userId == 1) ? $tiduser[0]->id : $userId);
    
    $action->lay->SetBlockData("USER", $userids);
}
?>
