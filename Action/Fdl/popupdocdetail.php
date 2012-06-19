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
 * @version $Id: popupdocdetail.php,v 1.45 2009/01/08 17:48:40 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/popupdoc.php");
include_once ("FDL/Class.SearchDoc.php");
function popupdocdetail(Action & $action)
{
    $docid = GetHttpVars("id");
    if ($docid == "") $action->exitError(_("No identificator"));
    $popup = getpopupdocdetail($action, $docid);
    popupdoc($action, $popup);
}

function getpopupdocdetail(Action & $action, $docid)
{
    // define accessibility
    $zone = GetHttpVars("zone"); // special zone
    $dbaccess = $action->GetParam("FREEDOM_DB");
    $doc = new_Doc($dbaccess, $docid);
    if ($doc->isAffected()) $docid = $doc->id;
    //  if ($doc->doctype=="C") return; // not for familly
    $tsubmenu = array();
    // -------------------- Menu menu ------------------
    $surl = $action->getParam("CORE_STANDURL");
    
    $tlink = array(
        "headers" => array(
            "descr" => _("Properties") ,
            "url" => "$surl&app=FDL&action=IMPCARD&zone=FDL:VIEWPROPERTIES:T&id=$docid",
            "confirm" => "false",
            "control" => "false",
            "tconfirm" => "",
            "target" => "prop$docid",
            "visibility" => POPUP_CTRLACTIVE,
            "submenu" => "",
            "barmenu" => "false"
        ) ,
        "latest" => array(
            "descr" => _("View latest") ,
            "url" => "$surl&app=FDL&action=FDL_CARD&latest=Y&id=$docid",
            "confirm" => "false",
            "control" => "false",
            "tconfirm" => "",
            "target" => "_self",
            "visibility" => POPUP_INVISIBLE,
            "submenu" => "",
            "barmenu" => "false"
        ) ,
        "editdoc" => array(
            "descr" => _("Modify") ,
            "url" => "$surl&app=GENERIC&action=GENERIC_EDIT&rzone=$zone&id=$docid",
            "confirm" => "false",
            "control" => "false",
            "tconfirm" => "",
            "target" => "_self",
            "visibility" => POPUP_ACTIVE,
            "submenu" => "",
            "barmenu" => "false"
        )
    );
    
    addCvPopup($tlink, $doc);
    addStatesPopup($tlink, $doc);
    $tlink = array_merge($tlink, array(
        "delete" => array(
            "descr" => _("Delete") ,
            "url" => "$surl&app=GENERIC&action=GENERIC_DEL&id=$docid",
            "confirm" => "true",
            "control" => "false",
            "tconfirm" => sprintf(_("Sure delete %s ?") , str_replace("'", "&rsquo;", $doc->title)) ,
            "target" => "_self",
            "visibility" => POPUP_INACTIVE,
            "submenu" => "",
            "barmenu" => "false"
        ) ,
        "restore" => array(
            "descr" => _("restore") ,
            "url" => "$surl&app=FDL&action=RESTOREDOC&id=$docid",
            "tconfirm" => "",
            "confirm" => "false",
            "target" => "_self",
            "visibility" => POPUP_INVISIBLE,
            "submenu" => "",
            "barmenu" => "false"
        ) ,
        "editstate" => array(
            "descr" => _("Change step") ,
            "url" => "$surl&app=FREEDOM&action=FREEDOM_EDITSTATE&id=$docid",
            "confirm" => "false",
            "control" => "false",
            "tconfirm" => "",
            "target" => "_self",
            "visibility" => POPUP_INVISIBLE,
            "submenu" => "",
            "barmenu" => "false"
        ) ,
        "lockdoc" => array(
            "descr" => _("Lock") ,
            "url" => "$surl&app=FDL&action=LOCKFILE&id=$docid",
            "confirm" => "false",
            "control" => "false",
            "tconfirm" => "",
            "target" => "_self",
            "visibility" => POPUP_INACTIVE,
            "submenu" => N_("security") ,
            "barmenu" => "false"
        ) ,
        "unlockdoc" => array(
            "descr" => _("Unlock") ,
            "url" => "$surl&app=FDL&action=UNLOCKFILE&id=$docid",
            "confirm" => "false",
            "control" => "false",
            "tconfirm" => "",
            "target" => "_self",
            "visibility" => POPUP_INACTIVE,
            "submenu" => "security",
            "barmenu" => "false"
        ) ,
        "revise" => array(
            "descr" => _("Revise") ,
            "url" => "$surl&app=FREEDOM&action=REVCOMMENT&id=$docid",
            "confirm" => "false",
            "control" => "false",
            "tconfirm" => "",
            "target" => "",
            "visibility" => POPUP_INACTIVE,
            "submenu" => "",
            "barmenu" => "false"
        ) ,
        "editprof" => array(
            "descr" => _("Change profile") ,
            "url" => "$surl&app=FREEDOM&action=EDITPROF&id=$docid",
            "confirm" => "false",
            "control" => "false",
            "tconfirm" => "",
            "target" => "",
            "visibility" => POPUP_INACTIVE,
            "submenu" => "security",
            "barmenu" => "false"
        ) ,
        "privateprof" => array(
            "descr" => _("Set private") ,
            "url" => "$surl&app=FREEDOM&action=MODPROF&docid=$docid&profid=private",
            "confirm" => "false",
            "control" => "false",
            "tconfirm" => "",
            "target" => "_self",
            "visibility" => POPUP_INVISIBLE,
            "submenu" => "security",
            "barmenu" => "false"
        ) ,
        "specprof" => array(
            "descr" => _("Set autonome profil") ,
            "url" => "$surl&app=FREEDOM&action=MODPROF&docid=$docid&profid=$docid",
            "confirm" => "false",
            "control" => "false",
            "tconfirm" => "",
            "target" => "_self",
            "visibility" => POPUP_INVISIBLE,
            "submenu" => "security",
            "barmenu" => "false"
        ) ,
        "publicprof" => array(
            "descr" => _("Set public") ,
            "url" => "$surl&app=FREEDOM&action=MODPROF&docid=$docid&profid=0",
            "confirm" => "false",
            "control" => "false",
            "tconfirm" => "",
            "target" => "_self",
            "visibility" => POPUP_INVISIBLE,
            "submenu" => "security",
            "barmenu" => "false"
        ) ,
        "histo" => array(
            "descr" => _("History") ,
            "url" => "$surl&app=FREEDOM&action=HISTO&id=$docid",
            "confirm" => "false",
            "control" => "false",
            "tconfirm" => "",
            "target" => "histo" . $doc->initid,
            "visibility" => POPUP_ACTIVE,
            "submenu" => "",
            "barmenu" => "false"
        ) ,
        "reaffect" => array(
            "descr" => _("Reaffect") ,
            "url" => "",
            "jsfunction" => "popdoc(null,'$surl&app=FDL&action=EDITAFFECT&id=$docid')",
            "confirm" => "false",
            "control" => "false",
            "tconfirm" => "",
            "target" => "",
            "visibility" => POPUP_INVISIBLE,
            "submenu" => "",
            "barmenu" => "false"
        ) ,
        "duplicate" => array(
            "descr" => _("Duplicate") ,
            "url" => "$surl&app=GENERIC&action=GENERIC_DUPLICATE&id=$docid",
            "confirm" => "true",
            "control" => "false",
            "tconfirm" => _("Sure duplicate ?") ,
            "target" => "_self",
            "visibility" => POPUP_CTRLACTIVE,
            "submenu" => "",
            "barmenu" => "false"
        ) ,
        "access" => array(
            "descr" => _("goaccess") ,
            "url" => "$surl&app=FREEDOM&action=FREEDOM_GACCESS&id=" . $doc->id,
            "confirm" => "false",
            "control" => "false",
            "tconfirm" => "",
            "target" => "",
            "mwidth" => 800,
            "mheight" => 300,
            "visibility" => POPUP_ACTIVE,
            "submenu" => "security",
            "barmenu" => "false"
        ) ,
        "tobasket" => array(
            "descr" => _("Add to basket") ,
            "url" => "$surl&app=FREEDOM&action=ADDDIRFILE&docid=$docid&dirid=" . $action->getParam("FREEDOM_IDBASKET") ,
            "confirm" => "false",
            "control" => "false",
            "tconfirm" => "",
            "target" => "",
            "visibility" => POPUP_CTRLACTIVE,
            "submenu" => "",
            "barmenu" => "false"
        ) ,
        "chgicon" => array(
            "descr" => _("Change icon") ,
            "url" => "$surl&app=FDL&action=EDITICON&id=$docid",
            "confirm" => "false",
            "control" => "false",
            "tconfirm" => "",
            "target" => "_self",
            "visibility" => POPUP_INVISIBLE,
            "submenu" => "",
            "barmenu" => "false"
        ) ,
        "addpostit" => array(
            "descr" => _("Add postit") ,
            "jsfunction" => "postit('$surl&app=GENERIC&action=GENERIC_EDIT&classid=27&pit_title=&pit_idadoc=$docid',50,50,300,200)",
            "confirm" => "false",
            "control" => "false",
            "tconfirm" => "",
            "target" => "",
            "visibility" => POPUP_ACTIVE,
            "submenu" => "",
            "barmenu" => "false"
        ) ,
        "viewask" => array(
            "descr" => _("View my ask") ,
            "jsfunction" => "viewwask('$surl&app=FDL&action=VIEWWASK&docid=$docid',50,50,300,200)",
            "confirm" => "false",
            "control" => "false",
            "tconfirm" => "",
            "target" => "",
            "visibility" => POPUP_INVISIBLE,
            "submenu" => "",
            "barmenu" => "false"
        ) ,
        "viewanswers" => array(
            "descr" => _("View answers") ,
            "url" => "$surl&app=FDL&action=IMPCARD&zone=FDL:VIEWANSWERS&id=$docid",
            "confirm" => "false",
            "control" => "false",
            "tconfirm" => "",
            "target" => "wask" . $doc->id,
            "visibility" => POPUP_INVISIBLE,
            "submenu" => "",
            "barmenu" => "false"
        ) ,
        "createforum" => array(
            "descr" => _("create forum") ,
            "url" => "$surl&app=FDL&action=FDL_FORUMCREATE&docid=$docid",
            "confirm" => "false",
            "control" => "false",
            "tconfirm" => "",
            "target" => "_self",
            "visibility" => POPUP_INVISIBLE,
            "submenu" => "",
            "barmenu" => "false"
        ) ,
        "openforum" => array(
            "descr" => _("open forum") ,
            "url" => "$surl&app=FDL&action=FDL_FORUMOPEN&docid=$docid",
            "confirm" => "false",
            "control" => "false",
            "tconfirm" => "",
            "target" => "_self",
            "visibility" => POPUP_INVISIBLE,
            "submenu" => "",
            "barmenu" => "false"
        ) ,
        "closeforum" => array(
            "descr" => _("close forum") ,
            "url" => "$surl&app=FDL&action=FDL_FORUMCLOSE&docid=$docid",
            "confirm" => "false",
            "control" => "false",
            "tconfirm" => "",
            "target" => "_self",
            "visibility" => POPUP_INVISIBLE,
            "submenu" => "",
            "barmenu" => "false"
        ) ,
        "toxml" => array(
            "descr" => _("View XML") ,
            "url" => "$surl&app=FDL&action=VIEWXML&id=$docid",
            "confirm" => "false",
            "control" => "false",
            "tconfirm" => "",
            "target" => "",
            "visibility" => POPUP_CTRLACTIVE,
            "submenu" => "",
            "barmenu" => "false"
        ) ,
        "relations" => array(
            "descr" => _("Document relations") ,
            "url" => "$surl&app=FREEDOM&action=RNAVIGATE&id=$docid",
            "confirm" => "false",
            "control" => "false",
            "tconfirm" => "",
            "target" => "",
            "visibility" => POPUP_CTRLACTIVE,
            "submenu" => "",
            "barmenu" => "false"
        ) ,
        "path" => array(
            "descr" => _("Access path list") ,
            "url" => "$surl&app=FREEDOM&action=FREEDOM_IFLD&id=$docid",
            "confirm" => "false",
            "control" => "false",
            "tconfirm" => "",
            "target" => "",
            "visibility" => POPUP_CTRLACTIVE,
            "submenu" => "",
            "barmenu" => "false"
        ) ,
        "reference" => array(
            "descr" => _("Search linked documents") ,
            "url" => "$surl&app=GENERIC&action=GENERIC_ISEARCH&id=$docid",
            "confirm" => "false",
            "control" => "false",
            "tconfirm" => "",
            "target" => "",
            "visibility" => POPUP_CTRLACTIVE,
            "submenu" => "",
            "barmenu" => "false"
        ) ,
        "tag" => array(
            "descr" => _("Add a tag") ,
            "url" => "$surl&app=FDL&action=TAG_MANAGEMENT&type=view",
            "confirm" => "false",
            "control" => "false",
            "tconfirm" => "",
            "target" => "_blank",
            "visibility" => ($doc->getFamDoc()->tagable == "public" ? POPUP_ACTIVE : POPUP_INVISIBLE) ,
            "submenu" => "",
            "barmenu" => "false"
        )
    ));
    
    changeMenuVisibility($action, $tlink, $doc);
    
    addFamilyPopup($tlink, $doc);
    addArchivePopup($tlink, $doc);
    addDocOfflinePopup($tlink, $doc, "_self", _("Offline menu"));
    
    return $tlink;
}
/**
 * Add control view menu
 */
function addArchivePopup(&$tlink, Doc & $doc, $target = "_self")
{
    if ($doc->fromname == "ARCHIVING") return; // no archive archive
    if ($doc->archiveid > 0) return;
    $s = new SearchDoc($doc->dbaccess, "ARCHIVING");
    $s->setObjectReturn();
    $s->addFilter("arc_status = 'O'");
    $s->search();
    
    if ($s->count() > 0) {
        while ($archive = $s->nextDoc()) {
            if ($archive->control("modify") == "") {
                $tlink["arch" . $archive->id] = array(
                    "descr" => sprintf(_("Insert in %s") , $archive->getTitle()) ,
                    "url" => "?app=FREEDOM&action=ADDDIRFILE&docid=" . $doc->initid . "&dirid=" . $archive->initid,
                    "confirm" => "false",
                    "control" => "false",
                    "tconfirm" => "",
                    "target" => "",
                    "visibility" => POPUP_ACTIVE,
                    "submenu" => _("Archive menu") ,
                    "barmenu" => "false"
                );
                // app=FREEDOM&action=FREEDOM_INSERTFLD&dirid=[dirid]&id=[FREEDOM_IDBASKET]
                if (($doc->defDoctype == "S") || ($doc->defDoctype == "D")) {
                    $tlink["farch" . $archive->id] = array(
                        "descr" => sprintf(_("Insert the content in %s") , $archive->getTitle()) ,
                        "url" => "?app=FREEDOM&action=FREEDOM_INSERTFLD&dirid=" . $doc->initid . "&id=" . $archive->initid,
                        "confirm" => "true",
                        "control" => "false",
                        "tconfirm" => sprintf("Sure insert the content of %s n archive ?", $doc->getTitle()) ,
                        "target" => "",
                        "visibility" => POPUP_ACTIVE,
                        "submenu" => _("Archive menu") ,
                        "barmenu" => "false"
                    );
                }
            }
        }
    }
}
/**
 * Additionnal menu for if document has an associated view controller
 * @param array $tlink
 * @param \Doc $doc
 * @param string $target
 */
function addCvPopup(&$tlink, Doc & $doc, $target = "_self")
{
    
    $rvid = getHttpVars("vid"); // for the return
    if ($doc->cvid > 0) {
        
        $surl = getParam("CORE_STANDURL");
        $cud = ($doc->CanEdit() == "");
        $docid = $doc->id;
        /**
         * @var CVDoc $cvdoc
         */
        $cvdoc = new_Doc($doc->dbaccess, $doc->cvid);
        $cvdoc->set($doc);
        $ti = $cvdoc->getTValue("CV_IDVIEW");
        $tl = $cvdoc->getTValue("CV_LVIEW");
        $tz = $cvdoc->getTValue("CV_ZVIEW");
        $tk = $cvdoc->getTValue("CV_KVIEW");
        $tm = $cvdoc->getTValue("CV_MSKID");
        $td = $cvdoc->getTValue("CV_DISPLAYED");
        $tmenu = $cvdoc->getTValue("CV_MENU");
        
        $tv = array(); // consult array views
        $te = array(); // edit array views
        $count = array();
        if (count($tk) > 0) {
            foreach ($tk as $k => $v) {
                if ($td[$k] != "no") {
                    if ($tz[$k] != "") {
                        if ($ti[$k] == "") $cvk = "CV$k";
                        else $cvk = $ti[$k];
                        if ($v == "VEDIT") {
                            if ($cud) {
                                if ($cvdoc->control($cvk) == "") {
                                    $tv[$cvk] = array(
                                        "typeview" => N_("specialedit") , # N_("specialedit %s")
                                        "idview" => $cvk,
                                        "menu" => $tmenu[$k],
                                        "zoneview" => $tz[$k],
                                        "txtview" => $tl[$k]
                                    );
                                }
                            }
                        } else {
                            if ($cvdoc->control($cvk) == "") {
                                $tv[$cvk] = array(
                                    "typeview" => N_("specialview") , # N_("specialview %s")
                                    "idview" => $cvk,
                                    "menu" => $tmenu[$k],
                                    "zoneview" => $tz[$k],
                                    "txtview" => $tl[$k]
                                );
                            }
                        }
                    }
                }
            }
        }
        
        foreach ($tv as $v) {
            $count[$v["typeview"]]++;
        }
        
        foreach ($tv as $v) {
            $engine = $cvdoc->getZoneTransform($v["zoneview"]);
            $url = ($v["typeview"] == 'specialview') ? "$surl&app=FDL&action=FDL_CARD&vid=" . $v["idview"] . "&id=$docid" : "$surl&app=GENERIC&action=GENERIC_EDIT&rvid=$rvid&vid=" . $v["idview"] . "&id=$docid";
            if ($engine) {
                $js = "popdoc(null,'$url')";
                $url = "";
            } else {
                $js = "";
            }
            if ($v["menu"] != "") {
                if ($v["menu"] == "-") $submenu = "";
                else $submenu = $v["menu"];
                $mtitle = $v["txtview"];
            } else {
                $submenu = ($count[$v["typeview"]] > 1) ? $v["typeview"] : "";
                $mtitle = ($count[$v["typeview"]] > 1) ? $v["txtview"] : sprintf(_($v["typeview"] . " %s") , $v["txtview"]);
            }
            $tlink[$v["idview"]] = array(
                "descr" => $mtitle,
                "url" => $url,
                "jsfunction" => $js,
                "confirm" => "false",
                "control" => "false",
                "tconfirm" => "",
                "target" => $target,
                "visibility" => POPUP_ACTIVE,
                "submenu" => $submenu,
                "barmenu" => "false"
            );
        }
    }
}
/**
 * Additionnal menu when workflow is detected
 * @param array $tlink
 * @param \Doc $doc
 */
function addStatesPopup(&$tlink, Doc & $doc)
{
    
    if ($doc->wid > 0) {
        /**
         * @var WDoc $wdoc
         */
        $wdoc = new_Doc($doc->dbaccess, $doc->wid);
        $wdoc->Set($doc);
        $fstate = $wdoc->GetFollowingStates();
        
        $surl = getParam("CORE_STANDURL");
        $docid = $doc->id;
        
        foreach ($fstate as $v) {
            $tr = $wdoc->getTransition($doc->state, $v);
            $jsf = "";
            
            if ((!$tr["nr"]) || (is_array($tr["ask"]) && (count($tr["ask"]) > 0))) {
                $jsf = sprintf("popdoc(null,'$surl&app=FDL&action=EDITCHANGESTATE&id=$docid&nstate=$v','%s',0,40,400,250)", (str_replace("'", "&rsquo;", sprintf(_("Steps")))));
            } else {
                $jsf = sprintf("subwindow(100,100,'_self','$surl&app=FREEDOM&action=MODSTATE&newstate=$v&id=$docid');");
            }
            $visibility = POPUP_ACTIVE;
            $tooltip = $wdoc->getActivity($v, ucfirst(_($v)));
            //$icon = (!$tr) ? "Images/noaccess.png" : ((is_array($tr["ask"])) ? "Images/miniask.png" : "");
            $icon = (!$tr) ? "Images/noaccess.png" : "";
            if ($tr && $tr["m0"]) {
                // verify m0
                $err = call_user_func(array(
                    $wdoc,
                    $tr["m0"],
                ) , $tr["e2"], $wdoc->doc->state);
                if ($err) {
                    $visibility = POPUP_INACTIVE;
                    $tooltip = $err;
                    $icon = "Images/nowaccess.png";
                }
            }
            $tlink[$v] = array(
                "title" => $tooltip,
                "descr" => $tr['id'] ? _($tr['id']) : $wdoc->getActivity($v, mb_ucfirst(_($v))) ,
                "jsfunction" => $jsf,
                "confirm" => "false",
                "control" => "false",
                "color" => $wdoc->getColor($v) ,
                "tconfirm" => "",
                "icon" => $icon,
                "target" => "_self",
                "visibility" => $visibility,
                "submenu" => "chgstates", #_("chgstates")
                "barmenu" => "false"
            );
        }
    }
}
/**
 * additional menu for family documents
 * @param array $tlink
 * @param Doc $doc
 */
function addFamilyPopup(&$tlink, Doc & $doc)
{
    $lmenu = $doc->GetMenuAttributes(true);
    foreach ($lmenu as $k => $v) {
        $confirm = false;
        $control = false;
        if (($v->getOption("onlyglobal") == "yes") && ($doc->doctype != "C")) continue;
        if (($v->getOption("global") != "yes") && ($doc->doctype == "C")) continue;
        if ($v->link[0] == '?') {
            $v->link = substr($v->link, 1);
            $confirm = true;
        }
        if ($v->getOption("lconfirm") == "yes") $confirm = true;
        if ($v->link[0] == 'C') {
            $v->link = substr($v->link, 1);
            $control = true;
        }
        if ($v->getOption("lcontrol") == "yes") $control = true;
        if (preg_match('/\[(.*)\](.*)/', $v->link, $reg)) {
            $v->link = $reg[2];
            $tlink[$k]["target"] = $reg[1];
        } else {
            $tlink[$k]["target"] = $v->id . "_" . $doc->id;
        }
        if ($v->getOption("ltarget") != "") {
            $tlink[$k]["target"] = $v->getOption("ltarget");
        } else if ($v->getOption("mtarget") != "") $tlink[$k]["target"] = $v->getOption("mtarget");
        $tlink[$k]["idlink"] = $v->id;
        $tlink[$k]["descr"] = $v->getLabel();
        $tlink[$k]["title"] = $v->getOption("ltitle");
        $tlink[$k]["url"] = addslashes($doc->urlWhatEncode($v->link));
        $tlink[$k]["confirm"] = $confirm ? "true" : "false";
        $tlink[$k]["control"] = $control;
        $tlink[$k]["mwidth"] = $v->getOption("mwidth");
        $tlink[$k]["mheight"] = $v->getOption("mheight");
        $tlink[$k]["tconfirm"] = $v->getOption("tconfirm", sprintf(_("Sure %s ?") , addslashes($v->getLabel())));
        if ($v->visibility == "H") $tlink[$k]["visibility"] = POPUP_INVISIBLE;
        else $tlink[$k]["visibility"] = ($control) ? POPUP_CTRLACTIVE : POPUP_ACTIVE;
        $tlink[$k]["submenu"] = $v->getOption("submenu");
        $tlink[$k]["barmenu"] = ($v->getOption("barmenu") == "yes") ? "true" : "false";
        if ($v->precond != "") {
            $tlink[$k]["visibility"] = $doc->ApplyMethod($v->precond, POPUP_ACTIVE);
            if ($tlink[$k]["visibility"] === false) $tlink[$k]["visibility"] = POPUP_INVISIBLE;
            elseif ($tlink[$k]["visibility"] === true) $tlink[$k]["visibility"] = POPUP_ACTIVE;
        }
    }
    // -------------------- Menu action ------------------
    $lactions = $doc->GetActionAttributes();
    foreach ($lactions as $k => $v) {
        
        $confirm = false;
        $control = false;
        $v->link = $v->getLink($doc->id);
        if ($v->getOption("lconfirm") == "yes") $confirm = true;
        if ($v->getOption("lcontrol") == "yes") $control = true;
        
        if (preg_match('/\[(.*)\](.*)/', $v->link, $reg)) {
            $v->link = $reg[2];
            $tlink[$k]["target"] = $reg[1];
        } else {
            $tlink[$k]["target"] = $v->id . "_" . $doc->id;
        }
        if ($v->getOption("ltarget") != "") {
            $tlink[$k]["target"] = $v->getOption("ltarget");
        } else if ($v->getOption("mtarget") != "") $tlink[$k]["target"] = $v->getOption("mtarget");
        $tlink[$k]["barmenu"] = ($v->getOption("barmenu") == "yes") ? "true" : "false";
        $tlink[$k]["idlink"] = $v->id;
        $tlink[$k]["descr"] = $v->getLabel();
        $tlink[$k]["url"] = addslashes($doc->urlWhatEncode($v->link));
        $tlink[$k]["confirm"] = $confirm ? "true" : "false";
        $tlink[$k]["control"] = $control;
        $tlink[$k]["mwidth"] = $v->getOption("mwidth");
        $tlink[$k]["mheight"] = $v->getOption("mheight");
        $tlink[$k]["tconfirm"] = sprintf(_("Sure %s ?") , addslashes($v->getLabel()));
        if ($v->visibility == "H") $tlink[$k]["visibility"] = POPUP_INVISIBLE;
        else $tlink[$k]["visibility"] = ($control) ? POPUP_CTRLACTIVE : POPUP_ACTIVE;
        $tlink[$k]["submenu"] = $v->getOption("submenu");
        if ($v->precond != "") $tlink[$k]["visibility"] = $doc->ApplyMethod($v->precond, POPUP_ACTIVE);
    }
}
/**
 * additionnal menus when offline is installed
 * @param array $tlink
 * @param Doc $doc
 * @param string $target
 * @param string $menu
 */
function addDocOfflinePopup(&$tlink, Doc & $doc, $target = "_self", $menu = 'offline')
{
    if (file_exists("OFFLINE/off_popupdocfolder.php")) {
        include_once ("OFFLINE/off_popupdocfolder.php");
        addOfflinePopup($tlink, $doc, $target, $menu);
    }
}
/**
 * Add control view menu
 * @param \Action $action
 * @param array $tlink
 * @param \Doc $doc
 */
function changeMenuVisibility(Action & $action, &$tlink, Doc & $doc)
{
    $cuf = ($doc->CanUnLockFile() == "");
    $cud = ($doc->CanEdit() == "");
    $tlink["toxml"]["visibility"] = POPUP_INVISIBLE;
    //  $tlink["reference"]["visibility"]=POPUP_CTRLACTIVE;
    if (getParam("FREEDOM_IDBASKET") == 0) $tlink["tobasket"]["visibility"] = POPUP_INVISIBLE;
    
    if ($doc->locked == $doc->userid) $tlink["lockdoc"]["visibility"] = POPUP_INVISIBLE;
    else if (($doc->locked != $doc->userid) && $cud) $tlink["lockdoc"]["visibility"] = POPUP_CTRLACTIVE;
    else $tlink["lockdoc"]["visibility"] = POPUP_INVISIBLE;
    
    if ($doc->isLocked()) {
        if ($cuf) $tlink["unlockdoc"]["visibility"] = POPUP_ACTIVE;
        else $tlink["unlockdoc"]["visibility"] = POPUP_INACTIVE;
    } else $tlink["unlockdoc"]["visibility"] = POPUP_INVISIBLE;
    
    if (!$doc->isRevisable()) $tlink["revise"]["visibility"] = POPUP_INVISIBLE;
    else if ((($doc->lmodify == 'Y') || ($doc->revision == 0)) && ($cud)) $tlink["revise"]["visibility"] = POPUP_CTRLACTIVE;
    else $tlink["revise"]["visibility"] = POPUP_CTRLINACTIVE;
    
    if ($doc->IsControlled() && ($doc->profid > 0) && ($doc->Control("viewacl") == "")) {
        $tlink["access"]["visibility"] = POPUP_CTRLACTIVE;
    } else {
        $tlink["access"]["visibility"] = POPUP_INVISIBLE;
    }
    
    if (($doc->Control("modifyacl") == "") && (!$doc->isLocked(true))) {
        $tlink["editprof"]["visibility"] = POPUP_CTRLACTIVE;
        
        if (($doc->doctype != 'P') && ($doc->doctype != 'W') && ($doc->fromname != 'WASK')) {
            if ($doc->profid != 0) $tlink["publicprof"]["visibility"] = POPUP_CTRLACTIVE;
            if ($doc->profid == 0) $tlink["privateprof"]["visibility"] = POPUP_CTRLACTIVE;
        } elseif (($doc->doctype == 'P') || ($doc->doctype == 'W') || ($doc->fromname == 'WASK')) {
            if (($doc->profid == 0) || ($doc->profid != $doc->id) || ($doc->profid != $doc->initid)) $tlink["specprof"]["visibility"] = POPUP_CTRLACTIVE;
            if ($doc->profid != 0) $tlink["publicprof"]["visibility"] = POPUP_CTRLACTIVE;
            if (($doc->profid == 0) || ($doc->profid == $doc->id) || ($doc->profid == $doc->initid)) $tlink["editprof"]["visibility"] = POPUP_INVISIBLE;
        }
    } else {
        $tlink["editprof"]["visibility"] = POPUP_CTRLINACTIVE;
    }
    
    $fdoc = $doc->getFamDoc();
    if ($fdoc->Control("icreate") != "") $tlink["duplicate"]["visibility"] = POPUP_INVISIBLE;
    
    if ($doc->PreDocDelete() == "") {
        $tlink["delete"]["visibility"] = POPUP_ACTIVE;
    } else {
        $tlink["delete"]["visibility"] = POPUP_INACTIVE;
    }
    
    if ($cud) {
        $tlink["editdoc"]["visibility"] = POPUP_ACTIVE;
        $tlink["chgicon"]["visibility"] = POPUP_CTRLACTIVE;
        if ($doc->allocated > 0) $tlink["reaffect"]["visibility"] = POPUP_ACTIVE;
    } else {
        $tlink["editdoc"]["visibility"] = POPUP_INACTIVE;
    }
    
    if ($doc->locked == - 1) { // fixed document
        if ($doc->doctype != 'Z') {
            $tmpdoc = new_Doc($doc->dbaccess, $doc->initid, true);
            if ($tmpdoc->Control("view") == "") {
                if (!$tmpdoc->preRevive()) $tlink["latest"]["visibility"] = POPUP_ACTIVE;
            }
        } elseif (!$doc->preRevive()) $tlink["restore"]["visibility"] = POPUP_ACTIVE;
        $tlink["editdoc"]["visibility"] = POPUP_INVISIBLE;
        $tlink["delete"]["visibility"] = POPUP_INVISIBLE;
        $tlink["editprof"]["visibility"] = POPUP_INVISIBLE;
        $tlink["revise"]["visibility"] = POPUP_INVISIBLE;
        $tlink["lockdoc"]["visibility"] = POPUP_INVISIBLE;
        $tlink["publicprof"]["visibility"] = POPUP_INVISIBLE;
        $tlink["privateprof"]["visibility"] = POPUP_INVISIBLE;
    }
    /*
    if ($doc->locked != -1) {
    if ($doc->wid > 0) {
      $wdoc=new_Doc($doc->dbaccess, $doc->wid);
      if ($wdoc->isAlive()) {
    $wdoc->Set($doc);
    if (count($wdoc->GetFollowingStates()) > 0)  $tlink["editstate"]["visibility"]=POPUP_ACTIVE;
    else $tlink["editstate"]["visibility"]=POPUP_INACTIVE;
      }
    }
    }*/
    
    if (($doc->wid > 0) || ($doc->revision > 0)) $tlink["histo"]["visibility"] = POPUP_ACTIVE;
    $waskes = $doc->getWasks(false);
    if (count($waskes) > 0) {
        if ($doc->control("wask") == "") $tlink["viewanswers"]["visibility"] = POPUP_ACTIVE;
        $waskes = $doc->getWasks(true);
        if (count($waskes) > 0) $tlink["viewask"]["visibility"] = POPUP_ACTIVE;
    } else {
        // find the wask in fixed revision
        if (($doc->control("wask") == "") && ($doc->wid > 0)) {
            $latestwaskid = $doc->getLatestIdWithAsk(); // change variable
            if ($latestwaskid) {
                $tlink["viewanswers"]["visibility"] = POPUP_ACTIVE;
                $tlink["viewanswers"]["url"].= "&id=$latestwaskid";
            }
        }
    }
    
    if ($doc->doctype == "F") $tlink["chgicon"]["visibility"] = POPUP_INVISIBLE;
    
    if (($doc->postitid > 0) || ($doc->locked == - 1)) $tlink["addpostit"]["visibility"] = POPUP_INVISIBLE;
    else if ($doc->fromid == 27) $tlink["addpostit"]["visibility"] = POPUP_INVISIBLE; // for post it family
    else {
        $fnote = new_doc($doc->dbaccess, 27);
        if ($fnote->control("icreate") != "") $tlink["addpostit"]["visibility"] = POPUP_INVISIBLE;
        else $tlink["addpostit"]["visibility"] = POPUP_ACTIVE;
    }
    if (!$action->parent->Haspermission("FREEDOM", "FREEDOM")) {
        // actions not available
        $tlink["editstate"]["visibility"] = POPUP_INVISIBLE;
        $tlink["revise"]["visibility"] = POPUP_INVISIBLE;
        $tlink["editprof"]["visibility"] = POPUP_INVISIBLE;
        $tlink["access"]["visibility"] = POPUP_INVISIBLE;
        $tlink["tobasket"]["visibility"] = POPUP_INVISIBLE;
    }
    if (!$action->parent->Haspermission("FREEDOM_READ", "FREEDOM")) {
        $tlink["histo"]["visibility"] = POPUP_INVISIBLE;
    }
    // Forum
    $tlink["createforum"]["visibility"] = POPUP_INVISIBLE;
    $tlink["openforum"]["visibility"] = POPUP_INVISIBLE;
    $tlink["closeforum"]["visibility"] = POPUP_INVISIBLE;
    $ff = $fdoc->forumid;
    if ($ff != "" && $doc->Control("edit") == "") {
        $vf = intval($doc->forumid);
        if ($vf == 0) $tlink["createforum"]["visibility"] = POPUP_ACTIVE;
        $tlink["closeforum"]["visibility"] = ($vf > 0 ? POPUP_ACTIVE : POPUP_INVISIBLE);
        $tlink["openforum"]["visibility"] = ($vf < 0 ? POPUP_ACTIVE : POPUP_INVISIBLE);
    }
}
?>
