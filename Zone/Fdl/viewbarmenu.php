<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Specific menu for family
 *
 * @author Anakeen
 * @version $Id: viewbarmenu.php,v 1.10 2008/10/09 08:00:37 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/popupdocdetail.php");
include_once ("FDL/popupfamdetail.php");

function viewbarmenu(Action & $action)
{
    $docid = GetHttpVars("id");
    $dbaccess = $action->dbaccess;
    $doc = new_Doc($dbaccess, $docid);
    if ($docid == "") $action->exitError(_("No identificator"));
    $popup = '';
    if ($doc->doctype == 'C') $popup = getpopupfamdetail($action, $docid);
    else {
        if ($doc->specialmenu) {
            if (preg_match("/(.*):(.*)/", $doc->specialmenu, $reg)) {
                $action->parent->setVolatileParam("getmenulink", 1);
                
                $dir = $reg[1];
                $function = strtolower($reg[2]);
                $file = $function . ".php";
                if (include_once ("$dir/$file")) {
                    $function($action);
                    $popup = $action->GetParam("menulink");;
                } else {
                    AddwarningMsg(sprintf(_("Incorrect specification of special menu : %s") , $doc->specialmenu));
                }
                $action->parent->setVolatileParam("getmenulink", null);
            } else {
                AddwarningMsg(sprintf(_("Incorrect specification of special menu : %s") , $doc->specialmenu));
            }
        }
    }
    if (!$popup) $popup = getpopupdocdetail($action, $docid);
    $other = false;
    $menuIndex = array(
        "visibility",
        "jsfunction",
        "isjs",
        "submenu",
        "mwidth",
        "mheight",
        "title",
        "url",
        "target",
        "confirm",
        "tconfirm"
    );
    foreach ($popup as $k => $v) {
        foreach ($menuIndex as $aItemMenu) if (!isset($v[$aItemMenu])) $popup[$k][$aItemMenu] = $v[$aItemMenu] = ''; // set undefined options
        $vis = $v["visibility"];
        if ($vis == POPUP_CTRLACTIVE || $vis == POPUP_CTRLINACTIVE) $other = true;
        if (($vis != POPUP_ACTIVE && (!$v["submenu"])) || ($vis == POPUP_INVISIBLE || $vis == POPUP_CTRLACTIVE || $vis == POPUP_CTRLINACTIVE)) unset($popup[$k]);
        else if (($v["url"] == "") && ($v["jsfunction"] == "")) unset($popup[$k]);
        else {
            $popup[$k]["menu"] = ($v["submenu"] != "");
            $popup[$k]["self"] = (($v["target"] == "_self") && ($v["jsfunction"] == "")) ? 'true' : 'false';
            if ($popup[$k]["menu"]) {
                $idxmenu = $v["submenu"];
                if (!isset($mpopup[$idxmenu])) {
                    $mpopup[$idxmenu] = true;
                    $popup[$k] = array(
                        "idlink" => $idxmenu,
                        "descr" => _($v["submenu"]) ,
                        "visibility" => false,
                        "confirm" => "false",
                        "jsfunction" => "false",
                        "isjs" => "false",
                        "title" => _("Click to view menu") ,
                        "barmenu" => false,
                        "m" => "",
                        "url" => "",
                        "target" => "",
                        "mwidth" => "",
                        "mheight" => "",
                        "smid" => "",
                        "menu" => true,
                        "tconfirm" => "",
                        "issubmenu" => false
                    );
                } else {
                    unset($popup[$k]);
                }
            } else {
                $popup[$k]["idlink"] = $k;
                if ($v["mwidth"] == "") $popup[$k]["mwidth"] = $action->getParam("FDL_HD2SIZE");
                if ($v["mheight"] == "") $popup[$k]["mheight"] = $action->getParam("FDL_VD2SIZE");
                if ($v["target"] == "") $popup[$k]["target"] = "$k$docid";
                $popup[$k]["descr"] = $v["descr"];
                $popup[$k]["title"] = ucfirst($v["title"]);
                $popup[$k]["m"] = ($v["barmenu"] == "true") ? "m" : "";
                $popup[$k]["isjs"] = ($v["jsfunction"] != "") ? 'true' : 'false';
                $popup[$k]["confirm"] = ($v["confirm"] == "true") ? 'true' : 'false';
                $popup[$k]["tconfirm"] = str_replace(array(
                    '"',
                    "'"
                ) , array(
                    '-',
                    "&rsquo;"
                ) , $v["tconfirm"]);
            }
        }
    }
    foreach ($popup as & $elmt) {
        foreach ($elmt as $k => & $value) {
            if (is_string($value)) {
                $value = str_replace("[", "&#091;", htmlspecialchars($value, ENT_QUOTES));
            }
        }
        unset($value);
    }
    unset($elmt);
    $action->lay->set("other", $other);
    $action->lay->setBlockData("LINKS", $popup);
    $action->lay->set("id", $doc->id);
}
