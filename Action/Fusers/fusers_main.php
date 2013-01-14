<?php
/*
 * Iuser list
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

include_once ("FDL/freedom_util.php");
include_once ("FDL/Lib.Dir.php");
/**
 * View list of document for a same family
 * @param Action &$action current action
 * @global chgAttr Http var :
 * @global chgId Http var :
 * @global chgValue Http var :
 * @global usedefaultview Http var : (Y|N) set Y if detail doc must be displayed with default view
 * @global etarget Http var : window target when edit doc
 * @global target Http var : window target when view doc
 * @global dirid Http var : folder/search id to restric searches
 * @global cols Http var : attributes id for column like : us_fname|us_lname
 * @global viewone Http var : (Y|N) set Y if want display detail doc if only one found
 * @global createsubfam Http var : (Y|N) set N if no want view possibility to create subfamily
 */
function fusers_main(Action & $action)
{
    global $_POST;
    
    $rqi_form = array();
    foreach ($_POST as $k => $v) {
        if (substr($k, 0, 4) == "rqi_") $rqi_form[substr($k, 4) ] = $v;
    }
    
    $dbaccess = $action->getParam("FREEDOM_DB");
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/subwindow.js");
    $action->parent->AddJsRef($action->GetParam("CORE_PUBURL") . "/FDC/Layout/setparamu.js");
    
    $pstart = GetHttpVars("sp", 0);
    $action->lay->set("choosecolumn", ($action->Haspermission("USERCARD_MANAGER", "USERCARD") == 1 ? true : false));
    $chattr = GetHttpVars("chgAttr", "");
    $chid = GetHttpVars("chgId", "");
    $chval = GetHttpVars("chgValue", "");
    $usedefaultview = (GetHttpVars("usedefaultview", "N") == "Y");
    $viewone = (GetHttpVars("viewone", "N") == "Y");
    $createsubfam = (GetHttpVars("createsubfam", "N") == "Y");
    $etarget = GetHttpVars("etarget");
    $target = GetHttpVars("target", "bookinfo");
    $dirid = GetHttpVars("dirid"); // restrict search
    $cols = GetHttpVars("cols"); // specific cols
    if ($chattr != "" && $chid != "") {
        $mdoc = new_Doc($dbaccess, $chid);
        $mdoc->setValue($chattr, $chval);
        $err = $mdoc->Modify();
        if ($err == "") AddWarningMsg($mdoc->title . " modifié (" . $mdoc->getAttribute($chattr)->getLabel() . " : " . $chval . ")");
    }
    $action->lay->set("viewpref", ($cols == ""));
    // Init page lines
    $lpage = $action->getParam("FUSERS_MAINLINE", 25);
    $action->lay->set("linep", $lpage);
    $choicel = array(
        10,
        25,
        50
    );
    foreach ($choicel as $k => $v) {
        $tl[] = array(
            "count" => $v,
            "init" => ($lpage == $v ? "selected" : "")
        );
    }
    $action->lay->setBlockData("BLine", $tl);
    // propagate HTTP vars parameters
    $action->lay->set("sp", $pstart);
    $action->lay->set("lp", $lpage);
    $action->lay->set("target", $target);
    $action->lay->set("dirid", $dirid);
    $action->lay->set("etarget", $etarget);
    $action->lay->set("createsubfam", GetHttpVars("createsubfam"));
    $action->lay->set("usedefaultview", GetHttpVars("usedefaultview"));
    $action->lay->set("viewone", GetHttpVars("viewone"));
    $action->lay->set("cols", GetHttpVars("cols"));
    $action->lay->set("", GetHttpVars("cols"));
    
    $sfullsearch = (GetHttpVars("sfullsearch", "") == "on" ? true : false);
    $action->lay->set("fullsearch", $sfullsearch);
    
    $sfam = GetHttpVars("dfam", $action->getParam("USERCARD_FIRSTFAM"));
    $action->lay->set("dfam", $sfam);
    $dnfam = new_Doc($dbaccess, $sfam);
    $action->lay->set("famid", $dnfam->id);
    $action->lay->set("famsearch", mb_convert_case(mb_strtolower($dnfam->title) , MB_CASE_TITLE));
    $dfam = createDoc($dbaccess, $sfam, false);
    $fattr = $dfam->GetAttributes();
    // Get user configuration
    $ucols = array();
    if ($cols) {
        $tccols = explode("|", $cols);
        foreach ($tccols as $k => $v) $ucols[$v] = 1;
        $action->lay->set("choosecolumn", false); // don't see choose column
        
    } else {
        $pc = $action->getParam("FUSERS_MAINCOLS", "");
        if ($pc != "") {
            $tccols = explode("|", $pc);
            foreach ($tccols as $k => $v) {
                if ($v == "") continue;
                $x = explode("%", $v);
                if ($sfam == $x[0]) $ucols[$x[1]] = 1;
            }
        }
        if (count($ucols) == 0) {
            // default abstract
            $la = $dnfam->getAbstractAttributes();
            foreach ($la as $k => $v) {
                if (($v->mvisibility != 'H') && ($v->mvisibility != 'I')) $ucols[$v->id] = 1;
            }
        }
    }
    // add sub families for creation
    $child = array();
    if (($dnfam->control("create") == "") && ($dnfam->control("icreate") == "")) {
        $child[] = array(
            "title" => $dnfam->title,
            "id" => $dnfam->id
        );
    } else $child = array();
    
    if ($createsubfam) {
        $child+= $dnfam->GetChildFam($dnfam->id, true);
        $action->lay->set("viewsubfam", count($child) > 1);
        $action->lay->setBlockData("NEW", $child);
        $fc = current($child);
        $action->lay->set("famid", $fc["id"]);
        $action->lay->set("famsearch", mb_convert_case(mb_strtolower($fc["title"]) , MB_CASE_TITLE));
    } else {
        $action->lay->set("viewsubfam", false);
    }
    
    $action->lay->set("cancreate", count($child) > 0);
    $orderby = "title";
    
    $cols = 0;
    $filter = array();
    $td = array();
    $sf = "";
    $clabel = mb_convert_case(mb_strtolower($dnfam->title) , MB_CASE_TITLE);
    if (isset($rqi_form["__ititle"]) && $rqi_form["__ititle"] != "" && $rqi_form["__ititle"] != $clabel) {
        if ($sfullsearch) $filter[] = "( title ~* '" . $rqi_form["__ititle"] . "' ) ";
        else $filter[] = "( title ~* '^" . $rqi_form["__ititle"] . "' ) ";
        $sf = $rqi_form["__ititle"];
    }
    $td[] = array(
        "ATTimage" => false,
        "ATTnormal" => true,
        "id" => "__ititle",
        "label" => ($sf == "" ? $clabel : "$sf") ,
        "filter" => ($sf == "" ? false : true) ,
        "firstCol" => false
    );
    $cols++;
    
    $vattr = array();
    foreach ($fattr as $k => $v) {
        if ($v->type != "menu" && $v->type != "frame") {
            if (isset($ucols[$v->id]) && $ucols[$v->id] == 1) {
                $sf = "";
                $clabel = mb_convert_case(mb_strtolower($v->getLabel()) , MB_CASE_TITLE);
                $vattr[] = $v;
                $attimage = $attnormal = false;
                switch ($v->type) {
                    case "image":
                        $attimage = true;
                        break;

                    default:
                        $attnormal = true;
                }
                if (isset($rqi_form[$v->id]) && $rqi_form[$v->id] != "" && $rqi_form[$v->id] != $clabel) {
                    $filter[] = "( " . $v->id . " ~* '" . $rqi_form[$v->id] . "' ) ";
                    $sf = $rqi_form[$v->id];
                }
                $td[] = array(
                    "ATTimage" => $attimage,
                    "ATTnormal" => $attnormal,
                    "id" => $v->id,
                    "label" => ($sf == "" ? $clabel : "$sf") ,
                    "filter" => ($sf == "" ? false : true) ,
                    "firstCol" => false
                );
                $cols++;
            }
        }
        $action->lay->SetBlockData("COLS", $td);
    }
    
    $psearch = $pstart * $lpage;
    $fsearch = $psearch + $lpage + 1;
    $cl = $rq = internalGetDocCollection($dbaccess, $dirid, $psearch, $fsearch, $filter, $action->user->id, "TABLE", $sfam, false, "title");
    
    $dline = array();
    $il = 0;
    
    $action->lay->set("idone", ($viewone && (count($cl) == 1)) ? $cl[0]["id"] : false);
    $pzone = '';
    foreach ($cl as $k => $v) {
        if ($il >= $lpage) continue;
        $dcol = array();
        $ddoc = getDocObject($dbaccess, $v);
        $attchange = ($ddoc->Control("edit") == "" ? true : false);
        $dcol[] = array(
            "ATTchange" => false,
            "ATTname" => "",
            "content" => mb_convert_case(mb_strtolower($v["title"]) , MB_CASE_TITLE) ,
            "ATTimage" => false,
            "ATTnormal" => true
        );
        foreach ($vattr as $ka => $va) {
            $attimage = $attnormal = false;
            switch ($va->type) {
                case "image":
                    $attimage = true;
                    break;

                default:
                    $attnormal = true;
            }
            $dcol[] = array(
                "ATTchange" => false,
                "content" => $ddoc->GetHtmlAttrValue($va->id, "faddbook_blanck", false) ,
                "cid" => $v["id"],
                "ATTimage" => $attimage,
                "ATTnormal" => $attnormal,
                "ATTname" => $va->id
            );
        }
        $action->lay->setBlockData("C$il", $dcol);
        $dline[$il]["cid"] = $v["id"];
        $dline[$il]["fabzone"] = $pzone;
        $dline[$il]["canChange"] = $attchange;
        $dline[$il]["fabzone"] = $pzone;
        $dline[$il]["etarget"] = ($etarget) ? $etarget : "edit" . $v["id"];
        $dline[$il]["title"] = xml_entity_encode(mb_convert_case(mb_strtolower($v["title"]) , MB_CASE_TITLE));
        $dline[$il]["Line"] = $il;
        $dline[$il]["icop"] = $dnfam->GetIcon($v["icon"]);
        $il++;
    }
    $pzone = ((!$usedefaultview) && isset($ddoc->faddbook_card)) ? $ddoc->faddbook_card : "";
    $action->lay->set("fabzone", $pzone);
    
    $action->lay->setBlockData("DLines", $dline);
    $action->lay->set("colspan", ($cols + 2));
    
    $action->lay->set("NextPage", false);
    $action->lay->set("PrevPage", false);
    if (count($cl) > $lpage) {
        $action->lay->set("NextPage", true);
        $action->lay->set("pnext", ($pstart + 1));
    }
    if ($pstart > 0) {
        $action->lay->set("PrevPage", true);
        $action->lay->set("sp", ($pstart - 1));
        $action->lay->set("pprev", ($pstart - 1));
    }
    $action->lay->set("dirtitle", "");
    if ($dirid > 0) {
        $fdoc = new_doc($dbaccess, $dirid);
        $action->lay->set("dirtitle", $fdoc->title);
    }
}
?>
