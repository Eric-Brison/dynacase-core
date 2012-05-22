<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Edition to affect document
 *
 * @author Anakeen 2011
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

include_once ("FDL/viewdocjs.php");
/**
 * All edit js scripts in one single file
 * @param Action &$action current action
 */
function alleditjs(Action & $action)
{
    $jurl = "WHAT/Layout";
    $statics = array(
        "FDL/Layout/ckeditorStaticEnvVar.js",
        "ckeditor/ckeditor.js",
        "$jurl/subwindow.js",
        "$jurl/geometry.js",
        "$jurl/AnchorPosition.js",
        "$jurl/PopupWindow.js",
        "$jurl/DHTMLapi.js",
        "jscalendar/Layout/calendar.js",
        "jscalendar/Layout/calendar-setup.js",
        "FDL/Layout/common.js",
        "FDL/Layout/viewicard.js",
        "FDL/Layout/iframe.js",
        "FDL/Layout/autocompletion.js",
        "FDC/Layout/inserthtml.js",
        "FDL/Layout/popupdoc.js",
        "FDL/Layout/popupdocmenu.js",
        "$jurl/resizeimg.js",
        "lib/jscolor/jscolor.js",
        "lib/data/json2.js"
    );
    if ($action->Read("navigator") == "EXPLORER") {
        //$statics[]="$jurl/iehover.js";
        
    }
    $lang = substr($action->GetParam("CORE_LANG") , 0, 2);
    if (preg_match('#^[a-z0-9_\.-]+$#i', $lang) && file_exists("jscalendar/Layout/calendar-" . $lang . ".js")) {
        $statics[] = "jscalendar/Layout/calendar-" . $lang . ".js";
    } else {
        $statics[] = "jscalendar/Layout/calendar-fr.js";
    }
    
    $dynamics = array(
        "FDL/Layout/editcommon.js",
        "FDL/Layout/editidoc.js",
        "FDL/Layout/enum_choice.js",
        "FDL/Layout/viewdoc.js",
        "FDL/Layout/edithtmltext.js"
    );
    
    viewdocjs($action);
    $action->lay->template = "";
    
    RessourcePacker::pack_js($action, $statics, $dynamics);
}
?>