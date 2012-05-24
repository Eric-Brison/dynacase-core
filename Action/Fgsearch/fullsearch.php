<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Full Text Search document
 *
 * @author Anakeen 2007
 * @version $Id: fullsearch.php,v 1.10 2008/01/04 17:56:37 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage GED
 */
/**
 */

include_once ("FDL/Class.SearchDoc.php");
include_once ("FDL/Class.DocSearch.php");

include_once ("FDL/freedom_util.php");
/**
 * Fulltext Search document
 * @param Action &$action current action
 * @global keyword Http var : word to search in any values
 * @global famid Http var : restrict to this family identioficator
 * @global start Http var : page number
 * @global dirid Http var : search identificator
 */
function fullsearch(&$action)
{
    
    $dbaccess = $action->GetParam("FREEDOM_DB");
    $famid = GetHttpVars("famid", 0);
    $keyword = GetHttpVars("_se_key", GetHttpVars("keyword")); // keyword to search
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/DHTMLapi.js");
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/AnchorPosition.js");
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/geometry.js");
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/resizeimg.js");
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/subwindow.js");
    $action->parent->AddJsRef($action->GetParam("CORE_PUBURL") . "/FGSEARCH/Layout/fullsearch.js");
    $action->parent->AddJsRef($action->GetParam("CORE_PUBURL") . "/lib/jquery/jquery.js", false);
    $action->parent->AddJsRef($action->GetParam("CORE_PUBURL") . "/FREEDOM/Layout/editdsearch.js");
    $action->parent->AddJsRef($action->GetParam("CORE_STANDURL") . "app=FDL&action=EDITJS");
    $action->parent->AddJsRef($action->GetParam("CORE_PUBURL") . "/FDL/Layout/edittable.js");
    $action->parent->AddJsRef($action->GetParam("CORE_PUBURL") . "/FGSEARCH/Layout/fulleditdsearch.js");
    
    $action->parent->AddCssRef("FGSEARCH:fullsearch.css", true);
    
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/resizeimg.js");
    
    $action->lay->set("key", $keyword);
    if (!is_numeric($famid)) $famid = getFamIdFromName($dbaccess, $famid);
    
    createSearchEngine($action);
    /* $bfam = array(); */
    $tclassdoc = GetClassesDoc($dbaccess, $action->user->id, array(
        1,
        2
    ) , "TABLE");
    
    foreach ($tclassdoc as $k => $cdoc) {
        $selectclass[$k]["idcdoc"] = $cdoc["initid"];
        $selectclass[$k]["classname"] = $cdoc["title"];
        $selectclass[$k]["famselect"] = ($cdoc["initid"] == $famid) ? "selected" : "";
    }
    $action->lay->SetBlockData("SELECTCLASS", $selectclass);
    $action->lay->set("searchtitle", _("General search"));
    $action->lay->set("guideKeyword", _("search dynacase documents"));
    $action->lay->set("initKeyword", ($keyword == "" ? true : false));
}

function createSearchEngine(&$action)
{
    global $_SERVER;
    $tfiles = array(
        "freedom-os.xml",
        "freedom.src",
        "freedom.gif",
        "freedom.xml"
    );
    $script = $_SERVER["SCRIPT_FILENAME"];
    $dirname = dirname($script);
    $base = dirname($_SERVER["SCRIPT_NAME"]);
    $host = $_SERVER["HTTP_HOST"];
    $action->lay->set("HOST", $host);
    $newpath = $host . $base;
    foreach ($tfiles as $k => $v) {
        $out = $dirname . "/img-cache/" . $host . "-" . $v;
        if (!file_exists($out)) {
            $src = "$dirname/moz-searchplugin/$v";
            if (file_exists($src)) {
                $content = file_get_contents($src);
                $destsrc = str_replace(array(
                    "localhost/freedom",
                    "SearchTitle",
                    "orifile"
                ) , array(
                    $newpath,
                    $action->getParam("CORE_CLIENT") ,
                    $host . "-" . $v
                ) , $content);
                file_put_contents($out, $destsrc);
            }
        }
    }
}
?>
