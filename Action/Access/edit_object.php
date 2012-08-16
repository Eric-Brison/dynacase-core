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
 * @version $Id: edit_object.php,v 1.5 2003/08/18 15:46:41 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage ACCESS
 */
/**
 */
// ---------------------------------------------------------------
// $Id: edit_object.php,v 1.5 2003/08/18 15:46:41 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Access/edit_object.php,v $
// ---------------------------------------------------------------
// ---------------------------------------------------------------
include_once ("Class.User.php");
include_once ("Class.ControlObject.php");
include_once ("Class.ObjectPermission.php");
// -----------------------------------
function edit_object(&$action)
{
    // -----------------------------------
    
    $coid = GetHttpVars("oid", 0);
    $appid = GetHttpVars("appid", 0);
    $viewuser = (GetHttpVars("viewuser", "no") == "yes");
    // contruct user id list
    // write title : oid description
    $oid = new ControlObject($action->dbaccess, array(
        $coid,
        $appid
    ));
    $action->lay->Set("title", $oid->description);
    $action->lay->Set("appid", $appid);
    $action->lay->Set("oid", $coid);
    // compute the head of table : acl definition
    $acl = new Acl($action->dbaccess);
    $appacls = $acl->getAclApplication($oid->id_class);
    $tacldef = array();
    while (list($k, $v) = each($appacls)) {
        $tacldef[$k]["description"] = _($v->description);
        $tacldef[$k]["name"] = $v->name;
    }
    $action->lay->SetBlockData("DEFACL", $tacldef);
    // define ACL for  each user
    $ouser = new Account();
    if ($viewuser) $tiduser = $ouser->GetUserList();
    else $tiduser = $ouser->GetGroupList();
    $userids = array();
    while (list($k, $v) = each($tiduser)) {
        if ($v->id == 1) continue; // except admin : don't need privilege
        $userids[$k]["userid"] = $v->id;
        $userids[$k]["descuser"] = $v->firstname . " " . $v->lastname;
        $userids[$k]["SELECTACL"] = "selectacl_$k";
        // compute acl for userId
        $uperm = new ObjectPermission($action->dbaccess, array(
            $v->id,
            $coid,
            $appid
        ));
        
        $tacl = array();
        reset($appacls);
        while (list($ka, $acl) = each($appacls)) {
            $tacl[$ka]["aclid"] = $acl->id;
            if (in_array($acl->id, $uperm->privileges)) {
                $tacl[$ka]["selected"] = "checked";
            } else $tacl[$ka]["selected"] = "";
        }
        $action->lay->SetBlockData($userids[$k]["SELECTACL"], $tacl);
    }
    
    $action->lay->SetBlockData("USERS", $userids);
}
?>
