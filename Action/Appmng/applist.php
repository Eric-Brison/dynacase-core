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
 * @version $Id:  $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage APPMNG
 */
/**
 */

include_once ("Class.TableLayout.php");
include_once ("Class.QueryGen.php");
include_once ("Class.SubForm.php");
include_once ("Class.Param.php");
// -----------------------------------
function applist(Action &$action)
{
    // -----------------------------------
    // Set the globals elements
    $baseurl = $action->GetParam("CORE_BASEURL");
    $standurl = $action->GetParam("CORE_STANDURL");
    
    $err = $action->Read("USERS_ERROR");
    if ($err != "") {
        $action->lay->Set("ERR_MSG", $err);
        $action->Unregister("USERS_ERROR");
    } else {
        $action->lay->Set("ERR_MSG", "");
    }
    // Set the form element
    $form = new SubForm("edit", 350, 330, $standurl . "app=APPMNG&action=APP_MOD", $standurl . "app=APPMNG&action=APP_EDIT");
    $form->SetParam("id", "-1");
    $form->SetParam("name");
    $form->SetParam("short_name");
    $form->SetParam("description");
    $form->SetParam("available");
    $form->SetParam("displayable");
    $form->SetParam("access_free");
    $form->SetParam("ssl");
    $form->SetParam("machine");
    
    $form->SetKey("id");
    
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/subwindow.js");
    $action->parent->AddJsCode($form->GetMainJs());
    $action->lay->set("MAINFORM", $form->GetMainForm());
    
    if ($action->HasPermission("ADMIN")) {
        $add_icon = new Layout($action->GetLayoutFile("add_icon.xml") , $action);
        $add_icon->set("JSCALL", $form->GetEmptyJsMainCall());
        $action->lay->set("ADD_ICON", $add_icon->gen());
    } else {
        $action->lay->set("ADD_ICON", "");
    }
    // Set the table element
    $query = new QueryGen("", "Application", $action);
    $query->addQuery("(objectclass != 'Y' ) OR ( objectclass isnull)");
    $query->slice = 100;
    $query->order_by = 'name';
    $query->Query();
    // Affect the modif icons
    foreach ($query->table->array as $k => $v) {
        
        $id = $query->table->array[$k]["id"];
        $p = new Param($action->dbaccess, array(
            "VERSION",
            PARAM_APP,
            $id
        ));
        $version = (isset($p->val) ? $p->val : "");
        
        $query->table->array[$k]["update"] = "";
        $query->table->array[$k]["edit"] = "";
        $query->table->array[$k]["delete"] = "";
        $query->table->array[$k]["version"] = $version;
        $query->table->array[$k]["description"] = $action->text($query->table->array[$k]["description"]);
        $query->table->array[$k]["appicon"] = $action->parent->getImageLink($query->table->array[$k]["icon"]);
    }
    
    $query->table->fields = array(
        "id",
        "update",
        "edit",
        "delete",
        "name",
        "appicon",
        "version",
        "description",
        "available",
        "access_free",
        "displayable"
    );
    
    $action->lay->Set("TABLE", $query->table->Set());
    $action->lay->Set("IMGHELP", $action->parent->getImageLink("help.gif"));
    $action->lay->Set("IMGPRINT", $action->parent->getImageLink("print.gif"));
    $action->lay->Set("IMGEDIT", $action->parent->getImageLink("edit.gif"));
    $action->lay->Set("IMGSEARCH", $action->parent->getImageLink("search.gif"));
    $action->lay->Set("APPLIST", _("Application list"));
}
?>
