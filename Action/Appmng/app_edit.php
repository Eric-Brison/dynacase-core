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
 * @version $Id: app_edit.php,v 1.6 2005/07/08 15:29:51 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage APPMNG
 */
/**
 */

include_once ("Class.SubForm.php");
include_once ("Class.Application.php");
// -----------------------------------
function app_edit(Action &$action)
{
    // -----------------------------------
    // Get all the params
    $id = $action->getArgument("id");
    $AppCour=null;
    if ($id == "") {
        $action->lay->Set("name", "");
        $action->lay->Set("short_name", "");
        $action->lay->Set("description", "");
        $action->lay->Set("passwd", "");
        $action->lay->Set("id", "");
        $action->lay->Set("TITRE", $action->text("titlecreate"));
        $action->lay->Set("BUTTONTYPE", $action->text("butcreate"));

    } else {
        $AppCour = new Application($action->GetParam("CORE_DB") , $id);
        $action->lay->Set("id", $id);
        $action->lay->Set("name", $AppCour->name);
        $action->lay->Set("short_name", $AppCour->short_name);
        $action->lay->Set("description", $AppCour->description);
        $action->lay->Set("passwd", "");
        $action->lay->Set("TITRE", $action->text("titlemodify"));
        $action->lay->Set("BUTTONTYPE", $action->text("butmodify"));
    }
    
    $action->lay->Set("access_free", $AppCour->access_free);
    
    $tab = array();
    if ($AppCour->ssl == 'Y') {
        $tab[0]["selected"] = "selected";
        $tab[1]["selected"] = "";
    } else {
        $tab[0]["selected"] = "";
        $tab[1]["selected"] = "selected";
    }
    $tab[0]["ssl"] = "Y";
    $tab[1]["ssl"] = "N";
    
    $action->lay->SetBlockData("SELECTSSL", $tab);
    
    unset($tab);
    $tab = array();
    if ($AppCour->available == 'Y') {
        $tab[0]["selected"] = "selected";
        $tab[1]["selected"] = "";
    } else {
        $tab[0]["selected"] = "";
        $tab[1]["selected"] = "selected";
    }
    $tab[0]["available"] = "Y";
    $tab[1]["available"] = "N";
    
    $action->lay->SetBlockData("SELECTAVAILABLE", $tab);
    
    unset($tab);
    $tab = array();
    if ($AppCour->displayable == 'Y') {
        $tab[0]["selected"] = "selected";
        $tab[1]["selected"] = "";
    } else {
        $tab[0]["selected"] = "";
        $tab[1]["selected"] = "selected";
    }
    $tab[0]["displayable"] = "Y";
    $tab[1]["displayable"] = "N";
    
    $action->lay->SetBlockData("SELECTDISPLAYABLE", $tab);
    
    $form = new SubForm("edit");
    $form->SetParam("name");
    $form->SetParam("short_name");
    $form->SetParam("description");
    $form->SetParam("available", "", "sel");
    $form->SetParam("displayable", "", "sel");
    $form->SetParam("access_free", "", "sel");
    $form->SetParam("id");
    $action->parent->AddJsCode($form->GetSubJs());
    $control = $action->GetLayoutFile("app_control.js");
    $lay = new Layout($control);
    $action->parent->AddJsCode($lay->gen());
}
?>
