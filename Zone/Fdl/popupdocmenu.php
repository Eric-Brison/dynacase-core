<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Specific menu for family
 */
require_once "FDL/popupdoc.php";
/**
 * define popupmenu for a document
 *
 * @param Action &$action current action
 *
 * @return void
 */
function popupdocmenu(Action & $action)
{
    // -----------------------------------
    // define accessibility
    $docid = $action->getArgument("id");
    $abstract = ($action->getArgument("abstract", 'N') == "Y");
    $zone = $action->getArgument("mzone"); // special zone
    $js = ($action->getArgument("js", "true") == "true") ? true : false;
    $css = ($action->getArgument("css", "true") == "true") ? true : false;
    
    $dbaccess = $action->dbaccess;
    $doc = new_Doc($dbaccess, $docid); # _("States")
    if ($zone == "") $specmenu = $doc->specialmenu;
    else $specmenu = $zone;
    if (is_string($specmenu) && preg_match("/(.*):(.*)/", $specmenu, $reg)) {
        $menuapp = $reg[1];
        $menuaction = $reg[2];
    } else {
        $menuapp = "FDL";
        $menuaction = "POPUPDOCDETAIL";
    }
    
    $action->lay->set("id", $doc->id);
    $action->lay->set("menuapp", $menuapp);
    $action->lay->set("menuaction", $menuaction);
    if ($js) {
        $action->parent->AddJsRef($action->GetParam("CORE_PUBURL") . "/FDL/Layout/popupdoc.js");
        $action->parent->AddJsRef($action->GetParam("CORE_PUBURL") . "/FDL/Layout/popupdocmenu.js");
    }
    if ($css) {
        $action->parent->AddCssRef("FDL:POPUP.CSS", true);
    }
}
