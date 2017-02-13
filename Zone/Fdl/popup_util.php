<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Utilities functions to generate popup menu
 *
 * @author Anakeen
 * @version $Id: popup_util.php,v 1.15 2005/10/07 14:07:53 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.Doc.php");

function popupInit($name, $items)
{
    global $menuitems;
    global $tmenus;
    global $tsubmenu;
    // ------------------------------------------------------
    // definition of popup menu
    $menuitems[$name] = $items;
    $tsubmenu[$name] = array();
    if (count($menuitems[$name]) == 0) {
        $jsarray = "[]";
    } else {
        $jsarray = "[";
        foreach ($menuitems[$name] as $ki => $imenu) {
            
            $jsarray.= '"' . $imenu . '",';
            global $ {
                $imenu
            };
            $ {
                $imenu
            } = 'v' . $ki;
            $tsubmenu[$name]['v' . $ki] = "";
        }
        // replace last comma by ']'
        $jsarray[mb_strlen($jsarray) - 1] = "]";
    }
    $tmenus[$name]["menuitems"] = $jsarray;
    $tmenus[$name]["name"] = $name;
    $tmenus[$name]["nbmitem"] = count($menuitems[$name]);
    $tmenus[$name]["menulabel"] = '["' . implode('","', $tsubmenu[$name]) . '"]';
    $tmenus[$name]["nbdiv"] = "";
}

function popupAddItem($name, $imenu)
{
    global $menuitems;
    global $tmenus;
    global $tsubmenu;
    // ------------------------------------------------------
    // definition of popup menu
    $menuitems[$name][] = $imenu;
    
    $ki = count($menuitems[$name]) - 1;
    
    global $
    {
        $imenu
    };
    $
    {
        $imenu
    } = 'v' . $ki;
    $tsubmenu[$name]['v' . $ki] = "";
    
    $tmenus[$name]["menuitems"] = '["' . implode('","', $menuitems[$name]) . '"]';
    $tmenus[$name]["nbmitem"] = count($menuitems[$name]);
    $tmenus[$name]["menulabel"] = '["' . implode('","', $tsubmenu[$name]) . '"]';
}
function popupInitItem($name, $k)
{
    global $tmenuaccess;
    global $menuitems;
    
    if (!isset($tmenuaccess[$name][$k]["divid"])) {
        $tmenuaccess[$name][$k]["divid"] = $k;
        
        foreach ($menuitems[$name] as $ki => $v) {
            $tmenuaccess[$name][$k]['v' . $ki] = 2; // invisible
            
        }
    }
}

function popupSubMenu($name, $nameid, $mlabel)
{
    global $tsubmenu;
    global $$nameid;
    $tsubmenu[$name][$$nameid] = $mlabel;
}

function popupGetSubItems($name, $mlabel)
{
    global $menuitems;
    global $tmenus;
    global $tsubmenu;
    
    $ti = array();
    $ki = 0;
    foreach ($tsubmenu[$name] as $k => $v) {
        if ($v == $mlabel) {
            $ti[] = $menuitems[$name][$ki];
        }
        $ki++;
    }
    return $ti;
}
function popupActive($name, $k, $nameid)
{
    global $tmenuaccess;
    global $$nameid;
    popupInitItem($name, $k);
    $tmenuaccess[$name][$k][$$nameid] = POPUP_ACTIVE;
}

function popupInactive($name, $k, $nameid)
{
    global $tmenuaccess;
    global $$nameid;
    
    popupInitItem($name, $k);
    $tmenuaccess[$name][$k][$$nameid] = POPUP_INACTIVE;
}
function popupInvisible($name, $k, $nameid)
{
    global $tmenuaccess;
    global $$nameid;
    
    popupInitItem($name, $k);
    $tmenuaccess[$name][$k][$$nameid] = POPUP_INVISIBLE;
}
// active if Ctrl Key Pushed
function popupCtrlActive($name, $k, $nameid)
{
    global $tmenuaccess;
    global $$nameid;
    
    popupInitItem($name, $k);
    $tmenuaccess[$name][$k][$$nameid] = POPUP_CTRLACTIVE;
}
// inactive if Ctrl Key Pushed
function popupCtrlInactive($name, $k, $nameid)
{
    global $tmenuaccess;
    global $$nameid;
    
    popupInitItem($name, $k);
    $tmenuaccess[$name][$k][$$nameid] = POPUP_CTRLINACTIVE;
}
function popupGetAccessItem($name, $k, $nameid)
{
    global $tmenuaccess;
    global $$nameid;
    return ($tmenuaccess[$name][$k][$$nameid]);
}

function vcompare($a, $b)
{
    $na = intval(substr($a, 1));
    $nb = intval(substr($b, 1));
    
    if ($na == $nb) return 0;
    return ($na < $nb) ? -1 : 1;
}
function popupNoCtrlKey()
{
    global $tmenuaccess;
    global $tsubmenu;
    if (isset($tmenuaccess)) {
        $kv = 0; // index for item
        foreach ($tmenuaccess as $name => $v) foreach ($v as $ki => $vi) foreach ($vi as $kj => $vj) {
            if ($vj == 3) {
                $tmenuaccess[$name][$ki][$kj] = 1;
                if ($tsubmenu[$name][$kj] == "") $tsubmenu[$name][$kj] = "ctrlkey";
            }
        }
    }
}

function popupGetAccess($popname)
{
    global $tmenuaccess;
    global $menuitems;
    
    $ta = $tmenuaccess[$popname][1];
    
    array_shift($ta);
    $ti = $menuitems[$popname];
    $tu = array();
    foreach ($ta as $v) {
        $tu[current($ti) ] = $v;
        next($ti);
    }
    return $tu;
}

function popupSetAccess($popname, $ta)
{
    global $tmenuaccess;
    global $menuitems;
    
    $ti = $menuitems[$popname];
    foreach ($ta as $i => $a) {
        $kt = array_keys($ti, $i);
        if (count($kt) == 1) {
            $k = $kt[0];
            $tmenuaccess[$popname][1]["v$k"] = $a;
        }
    }
}
function popupGen($kdiv = "nothing")
{
    global $tmenuaccess;
    global $menuitems;
    global $tmenus;
    /**
     * @var Action $action
     */
    global $action;
    static $first = 1;
    global $tcmenus; // closeAll menu
    global $tsubmenu;
    
    if ($first) {
        // static part
        $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/DHTMLapi.js");
        $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/geometry.js");
        $action->parent->AddJsRef($action->GetParam("CORE_PUBURL") . "/FDL/Layout/common.js");
        $action->parent->AddJsRef($action->Getparam("CORE_PUBURL") . "/FDL/Layout/popupfunc.js");
        // css pour popup
        //    $cssfile=DEFAULT_PUBDIR ."/FDL/Layout/popup.css";
        //     $csslay = new Layout($cssfile,$action);
        //     $action->parent->AddCssCode($csslay->gen());
        $action->parent->AddCssRef("FDL:POPUP.CSS", true);
        $first = 0;
    }
    $lpopup = new Layout(DEFAULT_PUBDIR . "/FDL/Layout/popup.js", $action);
    if (isset($tmenuaccess)) {
        $kv = 0; // index for item
        $tma = array();
        foreach ($tmenuaccess as $name => $v2) {
            $nbdiv = 0;
            foreach ($v2 as $k => $v) {
                uksort($v, 'vcompare');
                
                $tma[$kv]["vmenuitems"] = "[";
                foreach ($v as $ki => $vi) {
                    if ($ki[0] == 'v') // its a value
                    $tma[$kv]["vmenuitems"].= "" . $vi . ",";
                }
                // replace last comma by ']'
                $tma[$kv]["vmenuitems"][mb_strlen($tma[$kv]["vmenuitems"]) - 1] = "]";
                
                $tma[$kv]["name"] = $name;
                $tma[$kv]["divid"] = $v["divid"];
                $kv++;
                $nbdiv++;
            }
            $tmenus[$name]["nbdiv"] = $nbdiv;
            $tmenus[$name]["menulabel"] = '["' . implode('","', $tsubmenu[$name]) . '"]';
        }
        $lpopup->SetBlockData("MENUACCESS", $tma);
        $lpopup->SetBlockData("MENUS", $tmenus);
        if (isset($tcmenus)) $tcmenus = array_merge($tcmenus, $tmenus);
        else $tcmenus = $tmenus;
        foreach ($tsubmenu as $kl => $vl) foreach ($vl as $sm) if ($sm != "") $tcmenus[$sm]["name"] = $sm;
        $lpopup->SetBlockData("CMENUS", $tcmenus);
    }
    $action->parent->AddJsCode($lpopup->gen());
    
    if ($action->Read("navigator", "") == "EXPLORER") $action->lay->Set("divpos", "absolute");
    else $action->lay->Set("divpos", "fixed");
    
    $tmenus = array(); // re-init (for next time)
    $tmenuaccess = array();
    // $menuitems[$name] = array();
    unset($tmenus);
    unset($tmenusaccess);
    unset($tsubmenu);
    unset($tmenuitems);
}

function popupAddGen($kdiv = "nothing")
{
    global $tmenuaccess;
    global $menuitems;
    global $tmenus;
    /**
     * @var Action $action
     */
    global $action;
    global $tsubmenu;
    
    $lpopup = new Layout(DEFAULT_PUBDIR . "/FDL/Layout/popupadd.js");
    if (isset($tmenuaccess)) {
        reset($tmenuaccess);
        $kv = 0; // index for item
        $tma = array();
        foreach ($tmenuaccess as $name => $v2) {
            $nbdiv = 0;
            foreach ($v2 as $k => $v) {
                uksort($v, 'vcompare');
                
                $tma[$kv]["vmenuitems"] = "[";
                foreach ($v as $ki => $vi) {
                    if ($ki[0] == 'v') // its a value
                    $tma[$kv]["vmenuitems"].= "" . $vi . ",";
                }
                // replace last comma by ']'
                $tma[$kv]["vmenuitems"][mb_strlen($tma[$kv]["vmenuitems"]) - 1] = "]";
                
                $tma[$kv]["name"] = $name;
                $tma[$kv]["divid"] = $v["divid"];
                $kv++;
                $nbdiv++;
            }
            $tmenus[$name]["nbdiv"] = $nbdiv;
            $tmenus[$name]["menulabel"] = '["' . implode('","', $tsubmenu[$name]) . '"]';
        }
        
        $lpopup->SetBlockData("ADDMENUACCESS", $tma);
        $lpopup->SetBlockData("ADDMENUS", $tmenus);
    }
    $action->parent->AddJsCode($lpopup->gen());
    
    $tmenus = array(); // re-init (for next time)
    $tmenuaccess = array();
    // $menuitems[$name] = array();
    unset($tmenus);
    unset($tmenusaccess);
    unset($tsubmenu);
    unset($tmenuitems);
}
