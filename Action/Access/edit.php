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
 * @version $Id: edit.php,v 1.12 2007/02/14 13:22:58 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage ACCESS
 */
/**
 */
// ---------------------------------------------------------------
// $Id: edit.php,v 1.12 2007/02/14 13:22:58 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Access/edit.php,v $
// ---------------------------------------------------------------
// ---------------------------------------------------------------
include_once ("Class.SubForm.php");
include_once ("Class.User.php");
include_once ("Class.ControlObject.php");
include_once ("Class.ObjectPermission.php");
// -----------------------------------
function edit(Action & $action)
{
    // -----------------------------------
    $accountType = getHttpVars("accountType");
    $isclass = (GetHttpVars("isclass") == "yes");
    $coid = intval(GetHttpVars("oid"));
    // the modification can come from action user_access or appl_access
    if (GetHttpVars("mod") == "user") {
        $appId = GetHttpVars("id");
        $filteruser = getHttpVars("userfilter");
        if ($accountType == "G") {
            $action->lay->Set("returnact", "GROUP_ACCESS&userfilter=$filteruser");
            $userId = $action->Read("access_group_id");
        } elseif ($accountType == "R") {
            $action->lay->Set("returnact", "ROLE_ACCESS&userfilter=$filteruser");
            $userId = $action->Read("access_role_id");
        } else {
            $action->lay->Set("returnact", "USER_ACCESS&userfilter=$filteruser"); // for return previous page
            $userId = $action->Read("access_user_id");
        }
    } else {
        $userId = GetHttpVars("id");
        if ($isclass) {
            $appId = $action->Read("access_class_id");
            $action->lay->Set("returnact", "OBJECT_ACCESS&oid=$coid"); // for return previous page
            
        } else {
            $appId = $action->Read("access_appl_id");
            $action->lay->Set("returnact", "APPL_ACCESS"); // for return previous page
            
        }
    }
    $action->lay->Set("modifyact", "MODIFY");
    $action->lay->Set("target", "fbody");
    
    if (($isclass) || ($coid > 0)) {
        // oid list for object class only
        $action->lay->SetBlockData("OBJECTCLASS", array(
            array(
                "zou"
            )
        ));
    }
    // write title : user name
    $user = new Account($action->GetParam("CORE_DB") , $userId);
    switch ($user->accounttype) {
        case "U":
            $action->lay->set("accountLabel", _("User"));
            break;

        case "G":
            $action->lay->set("accountLabel", _("Group"));
            break;

        case "R":
            $action->lay->set("accountLabel", _("Role"));
            break;

        default:
            $action->lay->set("accountLabel", "");
    }
    $action->lay->Set("title", $user->firstname . " " . $user->lastname);
    edit_main($action, $userId, $appId, $coid);
}
// -----------------------------------
function edit_oid(&$action)
{
    // -----------------------------------
    $userId = intval(GetHttpVars("userid")); // can be affected by session var
    $coid = intval(GetHttpVars("oid"));
    $appId = GetHttpVars("appid");
    
    $action->lay->Set("modifyact", "MODIFY");
    $action->lay->Set("returnact", "OBJECT_ACCESS&oid=$coid&userid=$userId&appid=$appId"); //
    $action->lay->Set("target", "body");
    
    if ($userId == 0) $userId = $action->Read("access_user_id");
    if ($coid == 0) $coid = $action->Read("access_object_id");
    // user list for object modification
    $action->lay->SetBlockData("USERS", array(
        array(
            "zou"
        )
    ));
    // write title : oid description
    $oid = new ControlObject("", array(
        $coid,
        $appId
    ));
    // register for next time : same parameters
    $action->Register("access_object_id", $coid);
    $action->Register("access_class_id", $oid->id_class);
    $action->Register("access_user_id", $userId);
    
    $action->lay->Set("title", $action->text("object") . " : " . $oid->description);
    edit_main($action, $userId, $oid->id_class, $coid);
}
// -----------------------------------
function edit_main(Action & $action, $userId, $appId, $coid)
{
    // ------------------------
    //  print "$userId -  $appId - $coid";
    // Get all the params
    if (!$appId) $action->exitError(_("Cannot edit access. No application parameter."));
    if (!$userId) $action->exitError(_("Cannot edit access. No user parameter."));
    $isclass = (GetHttpVars("isclass") == "yes");
    //-------------------
    // contruct object id list
    if (($isclass) || ($coid > 0)) {
        
        $octrl = new ControlObject();
        $toid = $octrl->GetOids($appId);
        $oids = array();
        while (list($k, $v) = each($toid)) {
            
            if ($v->id_obj == $coid) $oids[$k]["selectedoid"] = "selected";
            else $oids[$k]["selectedoid"] = "";
            $oids[$k]["oid"] = $v->id_obj;
            $oids[$k]["descoid"] = $v->description;
        }
        
        $action->lay->SetBlockData("OID", $oids);
        // contruct user id list
        $ouser = new Account();
        $tiduser = $ouser->GetUserAndGroupList();
        $userids = array();
        while (list($k, $v) = each($tiduser)) {
            if ($v->id == 1) continue; // except admin : don't need privilege
            if ($v->id == $userId) $userids[$k]["selecteduser"] = "selected";
            else $userids[$k]["selecteduser"] = "";
            $userids[$k]["userid"] = $v->id;
            $userids[$k]["descuser"] = $v->firstname . " " . $v->lastname;
        }
        
        $action->lay->SetBlockData("USER", $userids);
        
        $action->lay->Set("nbinput", 5);
    } else {
        $action->lay->Set("nbinput", 4);
    }
    
    if (($isclass) && (!($coid > 0))) $coid = $oids[0]["oid"]; // get first if no selected
    $action->lay->Set("userid", $userId);
    $action->lay->Set("oid", $coid);
    $action->lay->Set("appid", $appId);
    $action->lay->Set("dboperm", "");
    //-------------------
    // compute permission
    $app = new Application($action->dbaccess, $appId);
    $action->lay->Set("appname", $action->text($app->short_name));
    
    if ($coid > 0) {
        // control view acl permission first
        $p = new ObjectPermission("", array(
            $action->parent->user->id,
            $coid,
            $appId
        ));
        if (preg_match("/dbname=(.*)/", $p->dbaccess, $reg)) {
            $action->lay->Set("dboperm", $reg[1]);
        }
        
        if (($err = $p->ControlOid($appId, "viewacl")) != "") {
            $action->ExitError($err);
        }
        // compute acl for userId
        $uperm = new ObjectPermission("", array(
            $userId,
            $coid,
            $appId
        ));
        $uperm->GetGroupPrivileges();
    } else {
        $uperm = new Permission($action->dbaccess, array(
            $userId,
            $appId
        ));
    }
    $acl = new Acl($action->dbaccess);
    
    $appacls = $acl->getAclApplication($appId);
    
    $tableacl = array();
    while (list($k, $v) = each($appacls)) {
        
        $tableacl[$k]["aclname"] = $v->name;
        $tableacl[$k]["acldesc"] = " (" . _($v->description) . ")";
        $tableacl[$k]["aclid"] = $v->id;
        if ($uperm->HasPrivilege($v->id)) {
            $tableacl[$k]["selected"] = "checked";
        } else {
            $tableacl[$k]["selected"] = "";
        }
        $tableacl[$k]["iacl"] = "$k"; // index for table in xml
        if (in_array($v->id, $uperm->GetUnPrivileges())) {
            $tableacl[$k]["selectedun"] = "checked";
        } else {
            $tableacl[$k]["selectedun"] = "";
        }
        if (in_array($v->id, $uperm->GetUpPrivileges())) {
            $tableacl[$k]["selectedup"] = "checked";
        } else {
            $tableacl[$k]["selectedup"] = "";
        }
        if (in_array($v->id, $uperm->GetGPrivileges())) {
            $tableacl[$k]["selectedg"] = "checked";
        } else {
            $tableacl[$k]["selectedg"] = "";
        }
    }
    
    $action->lay->SetBlockData("SELECTACL", $tableacl);
}
?>
