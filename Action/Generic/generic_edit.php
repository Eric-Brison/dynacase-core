<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Display edition interface
 *
 * @author Anakeen
 * @version $Id: generic_edit.php,v 1.75 2009/01/04 18:35:53 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.Doc.php");

include_once ("FDL/family_help.php");
include_once ("Class.QueryDb.php");
include_once ("GENERIC/generic_util.php");
/**
 * Edit a document
 * @param Action &$action current action
 * @global id string Http var : document identifier to see
 * @global zone string Http var : if set, special edit with special zone
 * @global rzone string Http var : if set, to return view with special zone
 * @global rtarget string Http var : if set, to return result in another window (the window will be closed)
 * @global vid string Http var : if set, edit represention describe in view control (can be use only if doc has controlled view)
 * @global mskid string Http var : is set special mask applied for edition
 * @global autoclose string Http var : set to yes to close window after modification
 */
function generic_edit(Action & $action)
{
    // -----------------------------------
    // Get All Parameters
    $docid = trim($action->getArgument("id", 0)); // document to edit
    $classid = $action->getArgument("classid", getDefFam($action)); // use when new doc or change class
    $dirid = $action->getArgument("dirid", 0); // directory to place doc if new doc
    $usefor = $action->getArgument("usefor"); // default values for a document
    $zonebodycard = $action->getArgument("zone"); // define view action
    $rzone = $action->getArgument("rzone"); // special zone when finish edition
    $rvid = $action->getArgument("rvid"); // special zone when finish edition
    $rtarget = $action->getArgument("rtarget", "_self"); // special zone when finish edition return target
    $updateAttrid = $action->getArgument("updateAttrid");
    if ($docid == 0) setHttpVar("classid", $classid);
    $vid = $action->getArgument("vid"); // special controlled view
    $mskid = $action->getArgument("mskid"); // special mask
    $autoclose = $action->getArgument("autoclose"); // to close window after modification
    $recallhelper = $action->getArgument("recallhelper"); // to recall helper input
    $action->lay->eSet("vid", $vid);
    $action->lay->eSet("ezone", $zonebodycard); // use for return in case of constraint
    $action->lay->eSet("rzone", $rzone);
    $action->lay->eSet("rvid", $rvid);
    $action->lay->eSet("rtarget", $rtarget);
    $action->lay->eSet("autoclose", $autoclose);
    $action->lay->eSet("recallhelper", $recallhelper);
    $action->lay->eSet("updateAttrid", $updateAttrid);
    $action->lay->Set("SELFTARGET", ($rtarget == "_self"));
    // Set the globals elements
    $dbaccess = $action->dbaccess;
    
    if (($docid === 0) || ($docid === "") || ($docid === "0")) {
        if ($classid == "") $action->exitError(sprintf(_("Creation aborded : no family specified")));
        if (!is_numeric($classid)) $classid = getFamIdFromName($dbaccess, $classid);
        if ($classid == "") $action->exitError(sprintf(_("Creation aborded : unknow family %s") , GetHttpVars("classid", getDefFam($action))));
        if ($classid > 0) {
            $cdoc = new_Doc($dbaccess, $classid);
            $errc = $cdoc->control('create');
            $erric = $cdoc->control('icreate');
            if ($errc || $erric) {
                redirectAsGuest($action);
            }
            if ($errc != "") $action->exitError(sprintf(_("no privilege to create this kind (%s) of document") , $cdoc->gettitle()));
            if ($erric != "") $action->exitError(sprintf(_("no privilege to create interactivaly this kind (%s) of document") , $cdoc->gettitle()));
            $action->lay->Set("title", mb_convert_case(sprintf(_("creation %s") , $cdoc->getHTMLTitle()) , MB_CASE_TITLE, 'UTF-8'));
        } else {
            $action->lay->Set("title", _("new card"));
        }
        if ($usefor == "D") $action->lay->eSet("title", _("default values"));
        if ($usefor == "Q") $action->lay->eSet("title", _("parameters values"));
        
        $action->lay->eSet("editaction", $action->text("Create"));
        $doc = createDoc($dbaccess, $classid);
        if (!$doc) $action->exitError(sprintf(_("no privilege to create this kind (%d) of document") , $classid));
        if ($usefor == 'D' || $usefor == 'Q') $doc->state = '';
        if ($usefor != "") $doc->doctype = 'T';
    } else {
        $doc = new_Doc($dbaccess, $docid, true); // always latest revision
        fixMultipleAliveDocument($doc);
        $docid = $doc->id;
        setHttpVar("id", $doc->id);
        $err = $doc->lock(true); // autolock
        if ($err != "") {
            redirectAsGuest($action);
            $action->ExitError($err);
        } else {
            $action->AddActionDone("LOCKFILE", $doc->id);
        }
        
        $classid = $doc->fromid;
        if (!$doc->isAlive()) $action->ExitError(_("document not referenced"));
        if (GetHttpVars("viewconstraint") != "Y") {
            //      $doc->refresh(); // set in editcard
            // update access date
            $doc->adate = $doc->getTimeDate();
            $doc->modify(true, array(
                "adate"
            ) , true);
        }
        
        $action->lay->Set("title", $doc->getHTMLtitle());
    }
    
    if ($action->read("navigator") == "EXPLORER") $action->lay->Set("shorticon", getParam("DYNACASE_FAVICO"));
    else $action->lay->Set("shorticon", $doc->getIcon());
    $action->lay->Set("docicon", $doc->getIcon('', 16));
    $param_zone_footer = json_decode($action->getParam("FOOTER_ZONE_EDIT") , true);
    $zone_footer = array();
    foreach ($param_zone_footer as $zone) {
        $zone_footer[] = array(
            "my_zone" => $zone
        );
    }
    $action->lay->SetBlockData("ZONE_FOOTER", $zone_footer);
    
    if ($zonebodycard == "") {
        if ($doc->cvid > 0) {
            /**
             * @var CVDoc $cvdoc
             */
            $cvdoc = new_Doc($dbaccess, $doc->cvid);
            $cvdoc->set($doc);
            if ($vid == "") {
                // search preferred view
                $vid = $doc->getDefaultView(true, "id");
                if ($vid) setHttpVar("vid", $vid);
            }
            
            if ($vid != "") {
                // special controlled view
                $err = $cvdoc->control($vid); // control special view
                if ($err != "") $action->exitError($err);
                $tview = $cvdoc->getView($vid);
                $doc->setMask($tview["CV_MSKID"]);
                if ($zonebodycard == "") $zonebodycard = $tview["CV_ZVIEW"];
            }
        }
    }
    if (($vid == "") && ($mskid != "")) {
        $mdoc = new_Doc($dbaccess, $mskid);
        if ($mdoc->isAlive() && ($mdoc->control('view') == "")) $doc->setMask($mdoc->id);
    }
    
    if ($zonebodycard == "") {
        if ((!$docid) && $doc->defaultcreate != "") $zonebodycard = $doc->defaultcreate;
        else $zonebodycard = $doc->defaultedit;
    }
    
    $action->lay->set("emblem", $doc->getEmblem());
    $action->lay->Set("HEAD", (!preg_match("/[A-Z]+:[^:]+:[T|S|U|V]/", $zonebodycard, $reg)));
    $action->lay->Set("FOOT", (!preg_match("/[A-Z]+:[^:]+:[S|U]/", $zonebodycard, $reg)));
    $action->lay->Set("NOFORM", (preg_match("/[A-Z]+:[^:]+:U/", $zonebodycard, $reg)));
    $action->lay->Set("NOSAVE", (preg_match("/[A-Z]+:[^:]+:V/", $zonebodycard, $reg)));
    if (getHttpVars("forcehead") == "yes") $action->lay->Set("HEAD", true); // for freedom_edit
    $action->lay->Set("iconsrc", $doc->geticon());
    $action->lay->Set("viewstate", "none");
    $action->lay->Set("dhelp", "none");
    if (getFamilyHelpFile($action, $doc->fromid)) {
        $action->lay->Set("dhelp", "");
        $action->lay->Set("helpid", $doc->fromid);
    }
    $action->lay->Set("state", "");
    
    $state = $doc->getState();
    $action->lay->Set("statecolor", $doc->getStateColor("transparent"));
    $action->lay->Set("wid", false);
    if ($doc->fromid > 0) {
        $fdoc = $doc->getFamilyDocument();
        $action->lay->Set("wid", ($fdoc->schar == 'R'));
        $action->lay->eSet("FTITLE", $fdoc->gettitle());
    } else {
        $action->lay->eSet("FTITLE", _("no family"));
    }
    if ($state) { // see only if it is a transitionnal doc
        if ($doc->locked == - 1) $action->lay->Set("state", $action->text($state));
        else {
            
            $action->lay->eSet("state", $action->Text($doc->getStateActivity($doc->getState())));
        }
        $action->lay->Set("viewstate", "inherit");
        $action->lay->Set("wid", ($doc->wid > 0) ? $doc->wid : $doc->state);
    }
    $action->lay->eSet("version", $doc->version);
    
    $action->lay->Set("initid", ($doc->initid != '') ? $doc->initid : 0);
    $action->lay->eSet("id", $doc->id);
    
    $action->lay->set("VALTERN", ($action->GetParam("FDL_VIEWALTERN", "yes") == "yes"));
    // information propagation
    $action->lay->eSet("classid", $classid);
    $action->lay->eSet("dirid", $dirid);
    // Set help URL
    $help = $doc->getHelpPage();
    $action->lay->Set("helpurl", ($help->isAlive()) ? $help->getAttributeHelpUrl() : false);
    $action->lay->Set("helpid", ($help->isAlive()) ? $help->id : false);
}

function cmp_cvorder($a, $b)
{
    if ($a["cv_order"] == $b["cv_order"]) {
        return 0;
    }
    return ($a["cv_order"] < $b["cv_order"]) ? -1 : 1;
}
