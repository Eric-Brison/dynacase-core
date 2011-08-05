<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Specific menu for family
 *
 * @author Anakeen 2000
 * @version $Id: popupdocmenu.php,v 1.4 2007/09/04 09:07:22 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/popupdoc.php");
// -----------------------------------
function popupdocmenu(&$action)
{
    // -----------------------------------
    // define accessibility
    $docid = GetHttpVars("id");
    $abstract = (GetHttpVars("abstract", 'N') == "Y");
    $zone = GetHttpVars("mzone"); // special zone
    $js = (GetHttpVars("js", "true") == "true") ? true : false;
    $css = (GetHttpVars("css", "true") == "true") ? true : false;
    
    $dbaccess = $action->GetParam("FREEDOM_DB");
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
    
    $action->lay->set("menuapp", $menuapp);
    $action->lay->set("menuaction", $menuaction);
    if ($js) {
        $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/AnchorPosition.js");
        $action->parent->AddJsRef($action->GetParam("CORE_PUBURL") . "/FDL/Layout/popupdoc.js");
        $action->parent->AddJsRef($action->GetParam("CORE_PUBURL") . "/FDL/Layout/popupdocmenu.js");
    }
    if ($css) {
        $action->parent->AddCssRef("FDL:POPUP.CSS", true);
    }
}
?>