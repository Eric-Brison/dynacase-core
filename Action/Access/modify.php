<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: modify.php,v 1.7 2007/02/14 15:13:16 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage ACCESS
 */
/**
 */
// ---------------------------------------------------------------
// $Id: modify.php,v 1.7 2007/02/14 15:13:16 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Access/modify.php,v $
// ---------------------------------------------------------------
include_once ("Class.QueryDb.php");
include_once ("Class.Application.php");
include_once ("Class.Acl.php");
include_once ("Class.Permission.php");
include_once ("Class.ObjectPermission.php");
// -----------------------------------
function modify(&$action)
{
    // -----------------------------------
    $coid = GetHttpVars("oid"); // oid for controlled object
    if ($coid > 0) modify_oid($action);
    else modify_app($action);
}
// -----------------------------------
function modify_app(&$action)
{
    // -----------------------------------
    // get all parameters
    $userId = GetHttpVars("userid");
    $appId = GetHttpVars("appid");
    $aclp = GetHttpVars("aclup"); // ACL + (more access)
    $acln = GetHttpVars("aclun"); // ACL - (less access)
    $returnact = GetHttpVars("returnact");
    // modif permission for a uncontrolled object
    $p = new Permission($action->dbaccess, array(
        $userId,
        $appId
    ));
    if (!$p->IsAffected()) {
        $p->Affect(array(
            "id_user" => $userId,
            "id_application" => $appId
        ));
    }
    // delete old permissions
    $p->deletePermission($userId, $appId, null, null);
    $p->deletePermission(null, $appId, null, true);
    
    if (is_array($aclp)) {
        // create new permissions
        while (list($k, $v) = each($aclp)) {
            $p->id_acl = $v;
            $p->computed = false;
            $p->Add();
        }
    }
    
    if (is_array($acln)) {
        // create new permissions
        while (list($k, $v) = each($acln)) {
            $p->id_acl = - $v;
            $p->computed = false;
            $p->Add();
        }
    }
    
    $action->parent->session->closeAll();
    $action->parent->session->set(""); // reset session to save current
    if ($returnact == "") exit(0);
    redirect($action, "ACCESS", $returnact . "&uid=" . $userId);
}
// -----------------------------------
function modify_oid(&$action)
{
    // -----------------------------------
    // get all parameters
    $userId = GetHttpVars("userid");
    $appId = GetHttpVars("appid");
    $aclp = GetHttpVars("aclup"); // ACL + (more access)
    $acln = GetHttpVars("aclun"); // ACL - (less access)
    $coid = GetHttpVars("oid"); // oid for controlled object
    $returnact = GetHttpVars("returnact");
    // test if current user could modify ACL
    $p = new ObjectPermission($action->dbaccess, array(
        $action->parent->user->id,
        $coid,
        $appId
    ));
    if (($err = $p->ControlOid($appId, "modifyacl")) != "") {
        $action->ExitError($err);
    }
    // modif permission for a particular object
    $p = new ObjectPermission($action->dbaccess, array(
        $userId,
        $coid,
        $appId
    ));
    // delete old permissions
    $p->Delete();
    
    if (is_array($aclp)) {
        // create new permissions
        while (list($k, $v) = each($aclp)) {
            $p->AddAcl($v);
        }
    }
    
    if (is_array($acln)) {
        // create new permissions
        while (list($k, $v) = each($acln)) {
            $p->AddAcl(-$v);
        }
    }
    
    $p->Add();
    
    if ($returnact == "") exit(0);
    redirect($action, "ACCESS", $returnact);
}
?>
