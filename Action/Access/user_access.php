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
 * @version $Id: user_access.php,v 1.11 2007/02/16 08:32:08 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage ACCESS
 */
/**
 */
// ---------------------------------------------------------------
// $Id: user_access.php,v 1.11 2007/02/16 08:32:08 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Access/user_access.php,v $
// ---------------------------------------------------------------
include_once ("Class.QueryDb.php");
include_once ("Class.QueryGen.php");
include_once ("Class.SubForm.php");
include_once ("Class.TableLayout.php");
include_once ("FDL/editutil.php");
// -----------------------------------
function user_access(Action & $action, $accountType = "U")
{
    // -----------------------------------
    $baseurl = $action->GetParam("CORE_BASEURL");
    $standurl = $action->GetParam("CORE_STANDURL");
    $filteruser = getHttpVars("userfilter");
    
    $user_id = getHttpVars("uid");
    $action->lay->set("userfilter", $filteruser);
    // Set the edit form element
    $paramedit = "&accountType=$accountType";
    
    $form = new SubForm("edit", 500, 330, "app=ACCESS&action=MODIFY$paramedit", $standurl . "app=ACCESS&action=EDIT&mod=user&userfilter=$filteruser$paramedit");
    
    $form->SetKey("id");
    
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/subwindow.js");
    $action->parent->AddJsCode($form->GetMainJs());
    $jsscript = $form->GetLinkJsMainCall();
    // Set
    $action->lay->set("ACTION_CHG", "ACCESS_USER_CHG$paramedit");
    $action->lay->set("ACTION_MOD", "USER_ACCESS_MOD$paramedit");
    $action->lay->set("fhelp", ($action->Read("navigator", "") == "EXPLORER") ? "_blank" : "fhidden");
    
    $action->lay->set("shortname", _($action->text("appname")));
    $action->lay->set("desc", _($action->text("appdesc")));
    $action->lay->set("permission", $action->text("permissions"));
    
    $action->lay->set("QUERY_FORM", "");
    $action->lay->set("FULLTEXTFORM", "");
    
    $action->lay->set("maxreach", false);
    $action->lay->set("usefilter", false);
    $action->lay->set("accountType", $accountType);
    $action->lay->set("URG", true);
    editmode($action);
    // affect the select form elements
    $u = new Account();
    if ($accountType == "G") {
        
        $varreg = "access_group_id";
        $action->lay->set("imgaccess", $action->parent->getImageLink("access2.gif", true, 20));
        $action->lay->set("changeLabel", _("Select Group Access"));
    } elseif ($accountType == "R") {
        
        $varreg = "access_role_id";
        $action->lay->set("imgaccess", $action->parent->getImageLink("access2.gif", true, 20));
        $action->lay->set("changeLabel", _("Select Role Access"));
    } else {
        $varreg = "access_user_id";
        $action->lay->set("imgaccess", $action->parent->getImageLink("access.gif", true, 20));
        $action->lay->set("changeLabel", _("Select User Access"));
    }
    // select the first user if not set
    $user_id = trim($user_id);
    if ($user_id == "") $user_id = trim($action->Read($varreg));
    else $action->register($varreg, $user_id);
    $action->log->debug("user_id : $user_id");
    if ($user_id == "") {
        simpleQuery($action->dbaccess, sprintf("select id from users where accounttype='%s' order by id limit 1", pg_escape_string($accountType)) , $user_id, true, true);
    }
    
    $u->select($user_id);
    $action->lay->set("displayName", $u->getDisplayName($user_id));
    
    $tab = array();
    
    $action->lay->set("hasuser", $u->id ? true : false);
    
    $action->parent->AddJsRef("change_acl.js");
    
    $action->register($varreg, $u->id);
    // 1) Get all application
    $query = new QueryGen($action->dbaccess, "Application", $action);
    $query->AddQuery("access_free = 'N'");
    $query->AddQuery("(objectclass != 'Y' ) OR ( objectclass isnull)");
    $query->table->headsortfields = array(
        "shortname" => "name",
        "desc" => "description"
    );
    
    $query->table->headcontent = array(
        "shortname" => $action->text("appname") ,
        "desc" => $action->text("appdesc") ,
        "permission" => $action->text("permissions")
    );
    
    $query->table->fields = array(
        "id",
        "name",
        "selname",
        "description",
        "edit"
    );
    $query->order_by = "name";
    $query->slice = 100;
    $query->placeHolder = _("Application filter");
    
    $query->Query();
    // 2) Get all acl for all application
    reset($query->table->array);
    
    while (list($k, $v) = each($query->table->array)) {
        
        if (!isset($v["id"])) continue;
        // test if application is controled
        $acl = new Acl($action->dbaccess);
        if (!$acl->getAclApplication($v["id"])) continue;
        // get user permissions
        $uperm = new Permission($action->dbaccess, array(
            $u->id,
            $v["id"]
        ));
        
        $name = $v["name"];
        
        $tab = array();
        $aclids = $uperm->privileges;
        if (!$aclids) { // no privilege
            $aclids = array(
                0
            );
        }
        
        while (list($k2, $v2) = each($aclids)) {
            $tab[$k2]["aclid"] = $v2;
            
            if ($v2 == 0) {
                $tab[$k2]["aclname"] = $action->text("none");
            } else {
                $acl = new Acl($action->dbaccess, $v2);
                $tab[$k2]["aclname"] = $acl->name;
            }
        }
        $action->lay->SetBlockData($v["id"], $tab);
        
        unset($tab);
        unset($acls);
        $query->table->array[$k]["name"] = $v["name"];
        $query->table->array[$k]["selname"] = $v["name"];
        $query->table->array[$k]["description"] = _($v["description"]);
        $query->table->array[$k]["id"] = $v["id"];
        
        $query->table->array[$k]["edit"] = str_replace("[id]", $v["id"], $jsscript);
    }
    
    $query->table->Set();
}
?>
