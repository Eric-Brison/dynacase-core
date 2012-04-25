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
 * @version $Id: popupdoc.php,v 1.23 2008/10/09 08:00:55 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.Doc.php");
// -----------------------------------
function popupdoc(Action & $action, $tlink, $tsubmenu = array())
{
    if ($action->getmenulink) { // to be use in viewbarmenu function
        $action->menulink = $tlink;
        return;
    }
    
    header('Content-type: text/xml; charset=utf-8');
    $onlyctrl = (GetHttpVars("onlyctrl") == "yes"); // view only ctrl
    $onlysub = GetHttpVars("submenu"); // view only sub menu
    $action->lay = new Layout(getLayoutFile("FDL", "popupdoc.xml") , $action);
    
    if ($onlysub && (!seems_utf8($onlysub))) $onlysub = utf8_encode($onlysub);
    
    $mb = microtime();
    
    $action->lay->set("CODE", "OK");
    $action->lay->set("warning", "");
    // define accessibility
    $action->lay->Set("SEP", false);
    $noctrlkey = ($action->getParam("FDL_CTRLKEY", "yes") == "no");
    
    $useicon = false;
    $rlink = array();
    $rlinkbottom = array();
    foreach ($tlink as $k => $v) {
        if ($onlyctrl) {
            if (($v["visibility"] != POPUP_CTRLACTIVE) && ($v["visibility"] != POPUP_CTRLINACTIVE)) $v["visibility"] = POPUP_INVISIBLE;
            if ($v["visibility"] == POPUP_CTRLACTIVE) $v["visibility"] = POPUP_ACTIVE;
            else if ($v["visibility"] == POPUP_CTRLINACTIVE) $v["visibility"] = POPUP_INACTIVE;
        }
        
        if ($onlysub) {
            if ($v["submenu"] != $onlysub) $v["visibility"] = POPUP_INVISIBLE;
            else $v["submenu"] = "";
        }
        if ($v["visibility"] == POPUP_INACTIVE) {
            if ($v["title"]) {
                $v["url"] = '';
                $v["jsfunction"] = sprintf("alert('%s')", str_replace("'", "&rsquo;", $v["title"]));
            } else {
                $v["url"] = '#';
                $v["jsfunction"] = '';
            }
            $v["confirm"] = 'false';
        }
        if ($v["visibility"] != POPUP_INVISIBLE) {
            if ($v["submenu"] == "") {
                if ($v["icon"]) $useicon = true;
                $v["ICONS"] = "mainicon";
            } else {
                $smid = base64_encode($v["submenu"]);
                if (!isset($tsubmenu[$smid])) $action->lay->set("icon" . $smid, false);
                $v["ICONS"] = "icon" . $smid;
                if ($v["icon"]) $action->lay->set("icon" . $smid, true);
            }
            
            if ((!isset($v["icon"])) || ($v["icon"] == "")) {
                $v["icon"] = "Images/none.gif";
            }
            
            $v["issubmenu"] = false;
            $v["descr"] = ucfirst(($v["descr"]));
            $v["title"] = ucfirst(($v["title"]));
            $v["tconfirm"] = str_replace(array(
                "\n",
                "\r",
                '"'
            ) , array(
                "\\n",
                '',
                '-'
            ) , ($v["tconfirm"]));
            if (!isset($v["visibility"])) $v["visibility"] = "";
            if (!isset($v["confirm"])) $v["confirm"] = "";
            if (!isset($v["color"])) $v["color"] = false;
            if (!isset($v["title"])) $v["title"] = false;
            
            if (!isset($v["jsfunction"])) $v["jsfunction"] = "";
            if (!isset($v["barmenu"])) $v["barmenu"] = "";
            if (!isset($v["url"])) $v["url"] = "";
            else $v["url"] = ($v["url"]);
            if (!isset($v["separator"])) $v["separator"] = false;
            if ((!isset($v["idlink"])) || ($v["idlink"] == "")) $v["idlink"] = $k;
            if ((!isset($v["target"])) || ($v["target"] == "")) $v["target"] = $k;
            if ((!isset($v["mwidth"])) || ($v["mwidth"] == "")) $v["mwidth"] = $action->getParam("FDL_HD2SIZE", 300);
            if ((!isset($v["mheight"])) || ($v["mheight"] == "")) $v["mheight"] = $action->getParam("FDL_VD2SIZE", 400);
            if ((isset($v["url"])) && ($v["url"] != "")) $v["URL"] = true;
            else $v["URL"] = false;
            
            if ($noctrlkey) {
                if ($v["visibility"] == POPUP_CTRLACTIVE) {
                    $v["submenu"] = N_("menuctrlkey");
                    $v["visibility"] = POPUP_ACTIVE;
                }
            }
            
            if ((isset($v["jsfunction"])) && ($v["jsfunction"] != "")) $v["JSFT"] = true;
            else $v["JSFT"] = false;
            $v["smid"] = "";
            if ((isset($v["submenu"])) && ($v["submenu"] != "")) {
                $smid = base64_encode($v["submenu"]);
                $v["smid"] = $smid;
                
                if (!isset($tsubmenu[$smid])) {
                    if ((!isset($v["icon"])) || ($v["icon"] == "")) {
                        $icon = "Images/none.gif";
                    } else {
                        $icon = $v["icon"];
                    }
                    $tsubmenu[$smid] = array(
                        "idlink" => $smid,
                        "descr" => ucfirst((_($v["submenu"]))) ,
                        "icon" => $v["icon"],
                        "visibility" => false,
                        "ICONS" => "mainicon",
                        "confirm" => false,
                        "color" => false,
                        "title" => false,
                        "jsfunction" => false,
                        "barmenu" => false,
                        "url" => false,
                        "target" => false,
                        "mwidth" => false,
                        "mheight" => false,
                        "smid" => false,
                        "tconfirm" => false,
                        "issubmenu" => false
                    );
                }
                
                if (!isset($tsubmenu[$smid]["displayed"])) {
                    $tsubmenu[$smid]["displayed"] = true;
                    $tsubmenu[$smid]["issubmenu"] = true;
                    $tsubmenu[$smid]["URL"] = false;
                    $tsubmenu[$smid]["JSFT"] = false;
                    $tsubmenu[$smid]["separator"] = false;
                    if ($noctrlkey && ($v["submenu"] == "menuctrlkey")) {
                        $rlinkbottom[] = $tsubmenu[$smid];
                    } else {
                        $rlink[] = $tsubmenu[$smid];
                    }
                }
            }
            
            $rlink[] = $v;
        }
    }
    
    if ($noctrlkey) {
        // ctrlkey submenu at bottom
        $rlink = array_merge($rlink, $rlinkbottom);
    }
    
    $action->lay->Set("mainicon", $useicon);
    $action->lay->SetBlockData("ADDLINK", $rlink);
    $action->lay->SetBlockData("SUBMENU", $tsubmenu);
    $action->lay->SetBlockData("SUBDIVMENU", $tsubmenu);
    $action->lay->Set("count", count($tlink));
    $action->lay->Set("SEP", (count($tsubmenu) > 0)); // to see separator
    $action->lay->set("delay", microtime_diff(microtime() , $mb));
}
?>