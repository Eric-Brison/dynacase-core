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
 * @version $Id: modify_object.php,v 1.6 2003/08/18 15:46:41 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage ACCESS
 */
/**
 */
// ---------------------------------------------------------------
// $Id: modify_object.php,v 1.6 2003/08/18 15:46:41 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Access/modify_object.php,v $
// ---------------------------------------------------------------
include_once ("Class.QueryDb.php");
include_once ("Class.Application.php");
include_once ("Class.Acl.php");
include_once ("Class.Permission.php");
include_once ("Class.ObjectPermission.php");
// -----------------------------------
function modify_object(&$action)
{
    // -----------------------------------
    // get all parameters
    $appId = GetHttpVars("appid");
    $acls = GetHttpVars("acls", array());
    $coid = GetHttpVars("oid"); // oid for controlled object
    $returnact = GetHttpVars("returnact");
    // test if current user can modify ACL
    $op = new ObjectPermission($action->dbaccess, array(
        $action->parent->user->id,
        $coid,
        $appId
    ));
    if (($err = $op->ControlOid($appId, "modifyacl")) != "") {
        $action->ExitError($err);
    }
    // serach ACL of the oid class
    $acl = new Acl($action->dbaccess);
    $defacls = $acl->getAclApplication($appId);
    
    while (list($userId, $aclon) = each($acls)) {
        // modif permission for a particular user
        $p = new ObjectPermission($action->dbaccess, array(
            $userId,
            $coid,
            $appId
        ));
        $p->GetGroupPrivileges();
        
        $gp = array_unique($p->gprivileges);
        // delete old permissions
        $p->Delete();
        reset($defacls);
        while (list($k, $acl) = each($defacls)) {
            // change only if needed :: not already in group privileges
            if ((in_array($acl->id, $gp)) && (!isset($aclon[$acl->id]))) {
                //		print "moins $p->id_user $acl->id $acl->name<BR>";
                $p->AddAcl(-$acl->id);
            } else if ((!in_array($acl->id, $gp)) && (isset($aclon[$acl->id]))) {
                //		print "pluss $userId $acl->id $acl->name<BR>";
                $p->AddAcl($acl->id);
            }
        }
        $p->Add();
    }
    
    redirect($action, "ACCESS", "EDIT_OBJECT&sole=Y&mod=app&isclass=yes&userid={$action->parent->user->id}&appid=$appId&oid=$coid");
}
?>
