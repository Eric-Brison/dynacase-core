<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Edition to affect document
 *
 * @author Anakeen 2011
 */

include_once ("FDL/viewdocjs.php");
/**
 * All edit js scripts in one single file
 * @param Action &$action current action
 */
function alleditjs(Action & $action)
{
    $jurl = "WHAT/Layout";
    
    $ckeditorPath = "lib/ckeditor/4";
    if ($action->getParam("ISIE6") || $action->getParam("ISIE7")) {
        
        $ckeditorPath = "lib/ckeditor/3";
    }

    $action->parent->addJsRef("$ckeditorPath/ckeditor.js");

    $extraCode = "";//sprintf("CKEDITOR_BASEPATH = '%s/';", $ckeditorPath);
    $statics = array(
       // "$ckeditorPath/ckeditor.js",
        "$jurl/subwindow.js",
        "$jurl/geometry.js",
        "$jurl/AnchorPosition.js",
        "$jurl/PopupWindow.js",
        "$jurl/DHTMLapi.js",
        "jscalendar/Layout/calendar.js",
        "jscalendar/Layout/calendar-setup.js",
        "FDL/Layout/common.js",
        "FDL/Layout/iframe.js",
        "FDL/Layout/autocompletion.js",
        "FDC/Layout/inserthtml.js",
        "FDL/Layout/popupdoc.js",
        "FDL/Layout/popupdocmenu.js",
        "$jurl/resizeimg.js",
        "lib/jscolor/jscolor.js",
        "lib/json2/json2.js"
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
        "FDL/Layout/editload.js",
        "FDL/Layout/enum_choice.js",
        "FDL/Layout/viewdoc.js",
        "FDL/Layout/edithtmltext.js"
    );
    
    viewdocjs($action);
    $action->lay->template = "";
    
    RessourcePacker::pack_js($action, $statics, $dynamics, $extraCode);
}
?>
