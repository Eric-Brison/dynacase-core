<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Display actions paramters
 *
 * @author Anakeen
 * @version $Id: actionlist.php,v 1.5 2003/08/18 15:46:41 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage APPMNG
 */
/**
 */

include_once ("Class.TableLayout.php");
include_once ("Class.QueryDb.php");
include_once ("Class.QueryGen.php");
include_once ("Class.Action.php");
include_once ("Class.SubForm.php");
// -----------------------------------
function actionlist(&$action)
{
    // -----------------------------------
    // Set the globals elements
    $baseurl = $action->GetParam("CORE_BASEURL");
    $standurl = $action->GetParam("CORE_STANDURL");
    
    $action->lay->set("ACTION_CHG", "ACTION_APPL_CHG");
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/subwindow.js");
    
    $err = $action->Read("err_add_parameter");
    if ($err != "") {
        $action->lay->Set("ERR_MSG", $err);
        $action->Unregister("err_add_parameter");
    } else {
        $action->lay->Set("ERR_MSG", "");
    }
    // select the first user if not set
    $appl_id = $action->Read("action_appl_id");
    $action->log->debug("appl_id : $appl_id");
    if ($appl_id == "") $appl_id = 0;
    // affect the select form elements
    $query = new QueryDb("", "Application");
    $query->AddQuery("(objectclass != 'Y' ) OR ( objectclass isnull)");
    $query->order_by = "name";
    $applist = $query->Query();
    unset($query);
    $tab = array();
    $appl_sel = "";
    $i = 0;
    reset($applist);
    while (list($k, $v) = each($applist)) {
        
        if ($appl_id == 0) {
            $appl_id = $v->id;
            $action->Register("action_appl_id", $appl_id);
        }
        $tab[$i]["text"] = $v->name;
        $tab[$i]["id"] = $v->id;
        if ($appl_id == $v->id) {
            $appl_sel = $v;
            $tab[$i]["selected"] = "selected";
        } else {
            $tab[$i]["selected"] = "";
        }
        $i++;
    }
    
    $action->lay->SetBlockData("SELAPPLI", $tab);
    $action->parent->AddJsRef("change_acl.js");
    // Set the table element
    $query = new QueryGen("", "Action", $action);
    
    $query->AddQuery("id_application=$appl_id");
    $query->slice = pow(2, 31);
    $query->order_by = "name";
    
    $query->table->headsortfields = array(
        "name" => "name"
    );
    $query->table->headcontent = array(
        "name" => $action->text("name")
    );
    $query->Query();
    
    $query->table->fields = array(
        "id",
        "name",
        "openaccess",
        "short_name",
        "script",
        "layout",
        "available",
        "acl",
        "root",
        "toc",
        "toc_order"
    );
    
    $action->lay->Set("TABLE", $query->table->Set());
}
?>
