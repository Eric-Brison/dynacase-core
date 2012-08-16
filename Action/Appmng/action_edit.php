<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Edit parameters for actions
 *
 * @author Anakeen
 * @version $Id: action_edit.php,v 1.4 2005/07/08 15:29:51 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage APPMNG
 */
/**
 */

include_once ("Class.SubForm.php");
include_once ("Class.Action.php");
// -----------------------------------
function action_edit(Action &$action)
{
    // -----------------------------------
    // Get all the params
    $id = $action->getArgument("id");
    $appl_id = $action->Read("action_appl_id");
    $ActionCour=null;
    if ($id == "") {
        $action->lay->Set("name", "");
        $action->lay->Set("short_name", "");
        $action->lay->Set("long_name", "");
        $action->lay->Set("acl", "");
        $action->lay->Set("root", "");
        $action->lay->Set("toc", "");
        $action->lay->Set("id", "");
        $action->lay->Set("TITRE", $action->text("titlecreateaction"));
        $action->lay->Set("BUTTONTYPE", $action->text("butcreate"));

        $action->lay->set("openaccess", "");
    } else {
        $ActionCour = new Action($action->GetParam("CORE_DB") , $id);
        $action->lay->Set("id", $id);
        $action->lay->Set("name", $ActionCour->name);
        $action->lay->Set("short_name", $ActionCour->short_name);
        $action->lay->Set("long_name", $ActionCour->long_name);
        $action->lay->Set("acl", $ActionCour->acl);
        $action->lay->Set("root", $ActionCour->root);
        $action->lay->Set("toc", $ActionCour->toc);
        $action->lay->Set("TITRE", $action->text("titlemodifyaction"));
        $action->lay->Set("BUTTONTYPE", $action->text("butmodify"));
        $action->lay->set("openaccess", $ActionCour->openaccess);
    }
    
    $tab = array();
    if ($ActionCour->available == 'Y') {
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
    
    $action->parent->AddJsRef("APPMNG/Layout/action_control.js");
}
?>
