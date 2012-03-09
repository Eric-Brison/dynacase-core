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
 * @version $Id: viewbarmenu.php,v 1.10 2008/10/09 08:00:37 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
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
    $dbaccess = $action->GetParam("FREEDOM_DB");
    $doc = new_Doc($dbaccess, $docid);
    if ($docid == "") $action->exitError(_("No identificator"));
    if ($doc->doctype == 'C') $popup = getpopupfamdetail($action, $docid);
    else {
        if ($doc->specialmenu) {
            if (preg_match("/(.*):(.*)/", $doc->specialmenu, $reg)) {
                $action->getmenulink = true;
                $dir = $reg[1];
                $function = strtolower($reg[2]);
                $file = $function . ".php";
                if (include_once ("$dir/$file")) {
                    $function($action);
                    $popup = $action->menulink;
                } else {
                    AddwarningMsg(sprintf(_("Incorrect specification of special menu : %s") , $doc->specialmenu));
                }
            } else {
                AddwarningMsg(sprintf(_("Incorrect specification of special menu : %s") , $doc->specialmenu));
            }
        }
    }
    if (!$popup) $popup = getpopupdocdetail($action, $docid);
    foreach ($popup as $k => $v) {
        if ($v["visibility"] != POPUP_ACTIVE) unset($popup[$k]);
        else if (($v["url"] == "") && ($v["jsfunction"] == "")) unset($popup[$k]);
        else {
            $popup[$k]["menu"] = ($v["submenu"] != "");
            $popup[$k]["self"] = (($v["target"] == "_self") && ($v["jsfunction"] == ""));
            if ($popup[$k]["menu"]) {
                $idxmenu = $v["submenu"];
                if (!isset($mpopup[$idxmenu])) {
                    $mpopup[$idxmenu] = true;
                    $popup[$k] = array(
                        "idlink" => $idxmenu,
                        "descr" => ucfirst((_($v["submenu"]))) ,
                        "visibility" => false,
                        "confirm" => false,
                        "jsfunction" => false,
                        "title" => _("Click to view menu") ,
                        "barmenu" => false,
                        "m" => "",
                        "url" => false,
                        "target" => false,
                        "mwidth" => false,
                        "mheight" => false,
                        "smid" => false,
                        "menu" => true,
                        "tconfirm" => false,
                        "issubmenu" => false
                    );
                } else {
                    unset($popup[$k]);
                }
            } else {
                $popup[$k]["idlink"] = $k;
                if (!isset($v["jsfunction"])) $popup[$k]["jsfunction"] = '';
                if ($v["mwidth"] == "") $popup[$k]["mwidth"] = $action->getParam("FDL_HD2SIZE");
                if ($v["mheight"] == "") $popup[$k]["mheight"] = $action->getParam("FDL_VD2SIZE");
                if ($v["target"] == "") $popup[$k]["target"] = "$k$docid";
                $popup[$k]["descr"] = ucfirst($v["descr"]);
                $popup[$k]["title"] = ucfirst($v["title"]);
                $popup[$k]["m"] = ($v["barmenu"] == "true") ? "m" : "";
                $popup[$k]["ISJS"] = ($v["jsfunction"] != "");
                $popup[$k]["confirm"] = ($v["confirm"] == "true");
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
    $action->lay->setBlockData("LINKS", $popup);
    $action->lay->set("id", $docid);
    
    $action->lay->Set("canmail", (($doc->usefor != "P") && ($doc->control('send') == "")));
}
