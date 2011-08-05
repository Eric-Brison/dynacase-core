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
 * @version $Id: modprof.php,v 1.17 2007/10/17 12:01:32 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage GED
 */
/**
 */
// ---------------------------------------------------------------
// $Id: modprof.php,v 1.17 2007/10/17 12:01:32 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/modprof.php,v $
// ---------------------------------------------------------------
include_once ("FDL/Class.Doc.php");
include_once ("FDL/Class.Dir.php");
include_once ("FDL/Class.DocAttr.php");
include_once ("FDL/freedom_util.php");
// -----------------------------------
function modprof(&$action)
{
    // -----------------------------------
    
    // Get all the params
    $docid = GetHttpVars("docid");
    $createp = GetHttpVars("create", 0); // 1 if use for create profile (only for familly)
    $profid = GetHttpVars("profid");
    $cvid = GetHttpVars("cvid");
    $redirid = GetHttpVars("redirid");
    
    if ($docid == 0) $action->exitError(_("the document is not referenced: cannot apply profile access modification"));
    
    $dbaccess = $action->GetParam("FREEDOM_DB");
    // initialise object
    $doc = new_Doc($dbaccess, $docid);
    // control modify acl
    $err = $doc->Control("modifyacl");
    if ($err != "") $action->ExitError($err);
    
    $err = $doc->lock(true); // auto lock
    if ($err != "") $action->ExitError($err);
    // test object permission before modify values (no access control on values yet)
    $err = $doc->canEdit();
    if ($err != "") $action->ExitError($err);
    
    if ($profid == "private") {
        $prof = getMyProfil($dbaccess);
        $profid = $prof->id;
    }
    
    if ($createp) {
        // change creation profile
        if ($doc->cprofid != $profid) {
            $doc->AddComment(sprintf(_("Change creation profil to %s [%d]") , $doc->getTitle($profid) , $profid));
            $doc->cprofid = $profid; // new creation profile access
            
        }
        if ($doc->ccvid != $cvid) {
            $doc->ccvid = $cvid; //  default control view for creation
            $doc->AddComment(sprintf(_("Change creation view control to %s [%d]") , $doc->getTitle($cvid) , $cvid));
        }
    } else {
        
        if (($doc->profid == $doc->id) && ($profid == 0)) {
            // unset control
            $doc->UnsetControl();
        }
        if ($doc->profid != $profid) $doc->AddComment(sprintf(_("Change profil to %s [%d]") , $doc->getTitle($profid) , $profid));
        if ($doc->cvid != $cvid) $doc->AddComment(sprintf(_("Change view control  to %s [%d]") , $doc->getTitle($cvid) , $cvid));
        $doc->setProfil($profid); // change profile
        $doc->setCvid($cvid); // change view control
        // specific control
        if ($doc->profid == $doc->id) $doc->SetControl();
        
        $doc->disableEditControl(); // need because new profil is not enable yet
        
    }
    $err = $doc->Modify();
    
    if ($err != "") $action->exitError($err);
    
    $doc->unlock(true); // auto unlock
    
    if ($redirid) $docid = $redirid;
    redirect($action, "FDL", "FDL_CARD&props=Y&id=$docid", $action->GetParam("CORE_STANDURL"));
}
?>
