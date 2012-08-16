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
 * @version $Id: appl_access.php,v 1.7 2007/02/16 14:11:14 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage ACCESS
 */
/**
 */
// ---------------------------------------------------------------
// $Id: appl_access.php,v 1.7 2007/02/16 14:11:14 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Access/appl_access.php,v $
// ---------------------------------------------------------------
include_once ("Class.QueryDb.php");
include_once ("Class.SubForm.php");
include_once ("Class.QueryGen.php");
include_once ("FDL/Class.Doc.php");
include_once ("FDL/editutil.php");
// -----------------------------------
function appl_access(Action & $action, $oid = 0)
{
    // -----------------------------------
    $baseurl = $action->GetParam("CORE_BASEURL");
    $standurl = $action->GetParam("CORE_STANDURL");
    
    $action->lay->set("usefilter", false);
    $action->lay->set("maxreach", false);
    $action->lay->set("URG", false);
    editmode($action);
    // affect the select form elements
    $varreg = "access_appl_id";
    $paramedit = "&isclass=no";
    
    $action->lay->set("ACTION_CHG", "ACCESS_APPL_CHG$paramedit");
    $action->lay->set("ACTION_MOD", "APPL_ACCESS_MOD$paramedit");
    // select the first user if not set
    $appl_id = trim($action->Read($varreg));
    
    if ($appl_id == "") {
        $appl_id = 0;
        simpleQuery($action->dbaccess, "select id from application where name='ACCESS'", $appl_id, true, true);
    }
    $appl_sel = new Application($action->dbaccess, $appl_id);
    // Set the edit form element
    $form = new SubForm("edit", 500, 330, "not used", $standurl . "app=ACCESS&action=EDIT&mod=app$paramedit");
    $form->SetParam("id", "-1");
    $form->SetKey("id");
    
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/subwindow.js");
    $action->parent->AddJsCode($form->GetMainJs());
    $jsscript = $form->GetLinkJsMainCall();
    
    $action->lay->set("changeLabel", _("Select Application Access"));
    $action->lay->set("hasuser", true);
    // display application / object class
    $tab = array();
    if (!$appl_sel->isAffected()) $action->exitError(sprintf("application %s not found", $appl_id));
    $action->lay->set("displayName", $appl_sel->name);
    $i = 0;
    
    $action->parent->AddJsRef("change_acl.js");
    // Init a querygen object to select users
    $query = new QueryGen($action->dbaccess, "Account", $action);
    //
    // Give some global elements for the table layout
    $query->table->fields = array(
        "id",
        "name",
        "selname",
        "description",
        "lastname",
        "firstname",
        "edit",
        "imgaccess"
    );
    $query->table->headsortfields = array(
        "shortname" => "login",
        "desc" => "lastname"
    );
    
    $query->table->headcontent = array(
        "shortname" => _("userlogin") ,
        "desc" => _("username") ,
        "permission" => _("permissions")
    );
    $query->placeHolder = _("Account filter");
    // 1) Get all users except admin
    $query->AddQuery("id != 1");
    $query->order_by = 'accounttype, login';
    $query->slice = 200;
    $query->Query();
    // 2) Get all acl for all users
    reset($query->table->array);
    unset($tab);
    
    $dr = new_doc($action->dbaccess, "ROLE");
    $du = new_doc($action->dbaccess, "IUSER");
    $dg = new_doc($action->dbaccess, "IGROUP");
    $drIcon = $dr->getIcon('', 18);
    $duIcon = $du->getIcon('', 18);
    $dgIcon = $dg->getIcon('', 18);
    
    foreach ($query->table->array as $k => $v) {
        if (!isset($v["login"])) continue;
        
        $uperm = new Permission($action->dbaccess, array(
            $v["id"],
            $appl_sel->id
        ));
        
        $name = $v["login"];
        
        $tab = array();
        $aclids = $uperm->privileges;
        if (!$aclids) { // no privilege
            $aclids = array(
                0
            );
        }
        
        foreach ($aclids as $k2 => $v2) {
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
        
        $query->table->array[$k]["name"] = $v["login"];
        $query->table->array[$k]["selname"] = $v["id"];
        $query->table->array[$k]["id"] = $v["id"];
        if (!isset($v["firstname"])) $v["firstname"] = "";
        if (!isset($v["lastname"])) $v["lastname"] = "";
        $query->table->array[$k]["description"] = $v["firstname"] . " " . $v["lastname"];
        $query->table->array[$k]["edit"] = str_replace("[id]", $v["id"], $jsscript);
        switch ($v["accounttype"]) {
            case 'U':
                $query->table->array[$k]["imgaccess"] = $duIcon;
                break;

            case 'G':
                $query->table->array[$k]["imgaccess"] = $dgIcon;
                break;

            case 'R':
                $query->table->array[$k]["imgaccess"] = $drIcon;
                break;

            default:
                $query->table->array[$k]["imgaccess"] = "Images/access.gif";
        }
    }
    
    $query->table->Set();
}
?>
