<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Display parameters
 *
 * @author Anakeen
 * @version $Id: param_list.php,v 1.10 2005/06/16 12:23:07 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage APPMNG
 */
/**
 */

include_once ("Class.TableLayout.php");
include_once ("Class.QueryDb.php");
include_once ("Class.Param.php");
include_once ("Class.SubForm.php");
// -----------------------------------
function param_list(Action &$action)
{
    // -----------------------------------
    // Get Param
    $userid = GetHttpVars("userid");
    $styleid = GetHttpVars("styleid");
    $pview = GetHttpVars("pview"); // set to "all" or "single" if user parameters
    // Set the globals elements
    $baseurl = $action->GetParam("CORE_BASEURL");
    $standurl = $action->GetParam("CORE_STANDURL");
    
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/subwindow.js");
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/AnchorPosition.js");
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/PopupWindow.js");
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/ColorPicker2.js");
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/OptionPicker.js");
    
    $tincparam = array();
    $appinc = array();
    
    switch ($pview) {
        case "allapp":
            $tparam = $action->parent->param->GetApps();
            break;

        case "alluser":
            if ($userid == "") $tparam = array();
            else $tparam = $action->parent->param->GetUser($userid);
            uasort($tparam, "cmpappid");
            break;

        case "singleuser":
            if ($userid == "") $tparam = array();
            else $tparam = $action->parent->param->GetUser($userid, $action->getParam("STYLE"));
            uasort($tparam, "cmpappid");
            break;

        case "allstyle":
            if ($styleid == "") $tparam = array();
            else $tparam = $action->parent->param->GetStyle($styleid);
            break;
        }
        
        $vsection = "appid";
        
        $precApp = 0;
        $tincparam = array();
        $applist = "";
        foreach ($tparam as $k => $v) {
            if (isset($v[$vsection])) {
                if ($v[$vsection] != $precApp) {
                    
                    $action->lay->SetBlockData("PARAM$precApp", $tincparam);
                    $tincparam = array();
                    $precApp = $v[$vsection];
                    
                    $app1 = new Application($action->dbaccess, $precApp);
                    
                    $appinc[$precApp]["appname"] = $app1->name;
                    $appinc[$precApp]["appicon"] = $action->parent->getImageLink($app1->icon);
                    $applist.= ($applist == "" ? "" : ",");
                    $applist.= "'" . $app1->name . "'";
                    $appinc[$precApp]["appdesc"] = $action->text($app1->short_name);
                    $appinc[$precApp]["PARAM"] = "PARAM$precApp";
                }
                $tincparam[$k] = $v;
                // to show difference between global, user and application parameters
                if ($v["type"][0] == PARAM_APP) $tincparam[$k]["classtype"] = "aparam";
                else if ($v["type"][0] == PARAM_USER) $tincparam[$k]["classtype"] = "uparam";
                else if ($v["type"][0] == PARAM_STYLE) $tincparam[$k]["classtype"] = "sparam";
                else $tincparam[$k]["classtype"] = "gparam";
                if ($v["kind"] == "password") {
                    if ($v["val"] != "") $v["val"] = "*****";
                    $tincparam[$k]["val"] = $v["val"];
                }
                $tincparam[$k]["sval"] = str_replace(array(
                    '"'
                ) , array(
                    "&quot;"
                ) , $v["val"]);
                
                $tincparam[$k]["colorstatic"] = ($v["kind"] == "static" || $v["kind"] == "readonly") ? "#666666" : "";
                // force type user if user mode
                if ($userid > 0) $tincparam[$k]["type"] = PARAM_USER . $userid;
                else if ($styleid != "") $tincparam[$k]["type"] = PARAM_STYLE . $styleid;
                
                if ($tincparam[$k]["descr"] == "") $tincparam[$k]["descr"] = $tincparam[$k]["name"];
                else $tincparam[$k]["descr"] = _($tincparam[$k]["descr"]);
                $tincparam[$k]["tooltip"] = $tincparam[$k]["name"] . " : " . $tincparam[$k]["descr"];
            }
        }
        
        $action->lay->SetBlockData("PARAM$precApp", $tincparam);
        if ($pview == "singleuser") { // chg action because of acl USER/ADMIN
            $action->lay->Set("ACTIONDEL", "PARAM_UDELETE");
            $action->lay->Set("ACTIONMOD", "PARAM_UMOD");
        } else {
            $action->lay->Set("ACTIONDEL", "PARAM_DELETE");
            $action->lay->Set("ACTIONMOD", "PARAM_MOD");
        }
        
        uasort($appinc, "cmpappname");
        $action->lay->set("AppList", $applist);
        $action->lay->SetBlockData("APPLI", $appinc);
    }
    function cmpappid($a, $b)
    {
        if ($a["appid"] == $b["appid"]) return 0;
        if ($a["appid"] > $b["appid"]) return 1;
        return -1;
    }
    
    function cmpappname($a, $b)
    {
        if ($a["appname"] == $b["appname"]) return 0;
        if ($a["appname"] > $b["appname"]) return 1;
        return -1;
    }
?>
