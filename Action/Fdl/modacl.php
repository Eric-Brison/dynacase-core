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
 * @version $Id: modacl.php,v 1.7 2005/06/28 08:37:46 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage
 */
/**
 */
// ---------------------------------------------------------------
// $Id: modacl.php,v 1.7 2005/06/28 08:37:46 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Fdl/modacl.php,v $
// ---------------------------------------------------------------
include_once ("FDL/Class.Doc.php");
// -----------------------------------
function modacl(&$action)
{
    // -----------------------------------
    // get all parameters
    $userid = GetHttpVars("userid");
    
    $aclp = GetHttpVars("aclup"); // ACL + (more access)
    $acln = GetHttpVars("aclun"); // ACL - (less access)
    $docid = GetHttpVars("docid"); // oid for controlled object
    
    $dbaccess = $action->GetParam("FREEDOM_DB");
    
    $doc = new_Doc($dbaccess, $docid);
    // test if current user can modify ACL
    $err = $doc->Control("modifyacl");
    if ($err != "") $action->exitError($err);
    
    $perm = new DocPerm($dbaccess, array(
        $docid,
        $userid
    ));
    
    $perm->UnSetControl();
    
    if (is_array($aclp)) {
        while (list($k, $v) = each($aclp)) {
            $perm->SetControlP($v);
        }
    }
    if (is_array($acln)) {
        while (list($k, $v) = each($acln)) {
            $perm->SetControlN($v);
        }
    }
    
    if ($perm->isAffected()) $perm->modify();
    else $perm->Add();
    
    RedirectSender($action);
}
?>
