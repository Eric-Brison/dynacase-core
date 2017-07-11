<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * View document zone
 */

include_once ("FDL/Class.Doc.php");
include_once ("FDL/Class.DocAttr.php");

include_once ("Class.TableLayout.php");
include_once ("Class.QueryDb.php");
include_once ("Class.QueryGen.php");
include_once ("FDL/freedom_util.php");
include_once ("FDL/family_help.php");
include_once ("VAULT/Class.VaultFile.php");
// -----------------------------------
function viewcard(Action & $action)
{
    // -----------------------------------
    // GetAllParameters
    $docid = GetHttpVars("id");
    $abstract = (GetHttpVars("abstract", 'N') == "Y"); // view doc abstract attributes
    $props = (GetHttpVars("props", 'N') == "Y"); // view doc properties
    $zonebodycard = GetHttpVars("zone"); // define view action
    $vid = GetHttpVars("vid"); // special controlled view
    $ulink = (GetHttpVars("ulink", '2')); // add url link
    $target = GetHttpVars("target"); // may be mail
    $reload = ($action->read("reload$docid", "N") == "Y"); // need reload
    if (($target != "mail") && ($target != "te")) $action->lay->set("MAILVIEW", false);
    else $action->lay->set("MAILVIEW", true);
    $action->lay->set("fhelp", ($action->Read("navigator", "") == "EXPLORER") ? "_blank" : "fhidden");
    $action->lay->set('verifyfiles', false);
    $action->lay->set('POSTIT', (GetHttpVars("postit", 'Y') == "Y"));
    
    if ($ulink == "N") $ulink = false;
    else if ($ulink == "Y") $ulink = 1;
    $action->lay->set("ulink", $ulink);
    // Set the globals elements
    $action->parent->AddJsRef(sprintf("?app=FDL&amp;action=ALLVIEWJS&amp;wv=%s", $action->getParam('WVERSION')));
    $action->parent->addJsRef("lib/jquery/jquery.js");
    $action->parent->addJsRef("lib/jquery-ui/js/jquery-ui.js");
    $action->parent->addCssRef("css/dcp/jquery-ui.css");
    /*
    $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/WHAT/Layout/AnchorPosition.js");
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/resizeimg.js");
    $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FDL/Layout/common.js");
    $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FDC/Layout/setparamu.js");
    $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FDC/Layout/inserthtml.js");
    */
    $baseurl = $action->GetParam("CORE_BASEURL");
    $standurl = $action->GetParam("CORE_STANDURL");
    $dbaccess = $action->dbaccess;
    /*
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/DHTMLapi.js");
    $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FDL/Layout/iframe.js");
    */
    $action->parent->addJsRef("FDL/Layout/viewcard.js");
    if ($reload) {
        $action->parent->AddJsRef($action->GetParam("CORE_PUBURL") . "/FDL/Layout/reload.js");
        $action->unregister("reload$docid");
    } else {
        $action->lay->eset("refreshfld", GetHttpVars("refreshfld"));
    }
    $action->lay->eset("ITSELF", (isset($_SERVER["REQUEST_URI"]) ? $_SERVER["REQUEST_URI"] : '#'));
    $doc = new_Doc($dbaccess, $docid);
    if (!$doc->isAffected()) $action->exitError(sprintf(_("cannot see unknow reference %s") , $docid));
    
    $err = $doc->control("view");
    if ($err != "") {
        redirectAsGuest($action);
        $action->exitError($err);
    }
    if ($doc->isConfidential()) {
        redirect($action, "FDL", "FDL_CONFIDENTIAL&id=" . $doc->id);
    }
    $useRss = ($doc->getRawValue("gui_isrss") == "yes");
    $action->lay->set("RSS", $useRss);
    if ($useRss) $action->lay->eset("rsslink", $doc->getRssLink());
    
    $param_zone_footer = json_decode($action->getParam("FOOTER_ZONE_VIEW") , true);
    $zone_footer = array();
    foreach ($param_zone_footer as $zone) {
        $zone_footer[] = array(
            "my_zone" => $zone
        );
    }
    $action->lay->SetBlockData("ZONE_FOOTER", $zone_footer);
    /*
     *  doc->cvid | vid != '' || mask to apply
     * -----------+-----------++-----------------
     *      0     |     0     || setMask(0)
     *      0     |     1     || (!) Not possible as a vid is the id of a view in doc->cvid
     *      1     |     0     || setMask(Doc::USEMASKCVVIEW)
     *      1     |     1     || setMask(cvid->vid->mskid) OR setMask(0)
    */
    
    if ($doc->wid > 0) {
        $err = $doc->setMask(0);
        if ($err) addWarningMsg($err);
    }
    if ($doc->cvid > 0) {
        // special controlled view
        
        /**
         * @var $cvdoc \Dcp\Family\CVDOC
         */
        $cvdoc = new_Doc($dbaccess, $doc->cvid);
        $cvdoc = clone $cvdoc;
        $cvdoc->Set($doc);
        if ($vid != "") {
            /*
             * Apply mask from requested view
            */
            $err = $cvdoc->control($vid); // control special view
            if ($err != "") $action->exitError($err);
            $tview = $cvdoc->getView($vid);
            $err = $doc->setMask($tview["CV_MSKID"]);
            if ($err) addWarningMsg($err);
            if ($zonebodycard == "") {
                $zonebodycard = $tview["CV_ZVIEW"];
            }
        } else {
            /*
             * Apply mask from default VIEW view
            */
            $doc->setMask(Doc::USEMASKCVVIEW);
            /* Propagate default view id */
            $vid = $doc->getDefaultView(false, "id");
            if ($vid) {
                setHttpVar("vid", $vid);
                if ($zonebodycard == "") {
                    $tview = $cvdoc->getView($vid);
                    $zonebodycard = $tview["CV_ZVIEW"];
                }
            }
        }
    }
    // set emblem
    $action->lay->set("emblem", $doc->getEmblem());
    $domains = $doc->getDomainIds();
    if (empty($domains)) {
        $action->lay->set("inDomain", false);
    } else {
        $action->lay->set("inDomain", true);
    }
    
    if ($doc->doctype === 'Z') {
        if ($doc->lmodify === "D") {
            $err = _("This document has been deleted");
        } else {
            $err = _("This revision has been deleted");
        }
    } else {
        // disabled control just to refresh
        $doc->disableEditControl();
        $err = $doc->refresh();
        $err.= $doc->preConsultation();
        $doc->enableEditControl();
        if ($doc->hasWaitingFiles()) {
            /*
            $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FDL/Layout/verifycomputedfiles.js");
            */
            $action->lay->set('verifyfiles', true);
        }
    }
    // set view zone
    if ($zonebodycard == "") {
        $zonebodycard = $doc->defaultview;
    }
    if ($zonebodycard == "") {
        $zonebodycard = "FDL:VIEWBODYCARD";
    }

    if ($action->getParam("ISIE", null) === null) {
        initExplorerParam($action->parent, false);
    }

    // with doc head ?
    $zo = $doc->getZoneOption($zonebodycard);
    if (GetHttpVars("dochead") == "") $dochead = (!preg_match("/[T|U|V]/", $zo, $reg));
    else $dochead = (GetHttpVars("dochead", 'Y') == "Y");
    $action->lay->set("viewbarmenu", ($zo == "V"));
    
    $action->lay->set("LGTEXTERROR", strlen($err));
    $action->lay->set("TEXTERROR", cleanhtmljs(nl2br($err)));
    $action->lay->Set("ZONEBODYCARD", $doc->viewDoc($zonebodycard, $target, $ulink, $abstract));
    /*
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/geometry.js");
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
    $action->parent->AddJsRef($action->GetParam("CORE_STANDURL")."app=FDL&action=VIEWDOCJS&sesscache=".$action->session->id);
    */
    //------------------------------
    // display document attributes
    $action->lay->Set("reference", $doc->initid . (($doc->name == "") ? "" : " ({$doc->name})"));
    
    $action->lay->Set("revision", $doc->revision);
    $action->lay->Set("V_TITLE", $doc->getDocAnchor($doc->id, $target));
    
    $action->lay->Set("lockedid", 0);
    $action->lay->Set("comment", '');
    
    if ($doc->confidential > 0) $action->lay->eSet("locked", _("confidential"));
    else if ($doc->locked == - 1) $action->lay->eSet("locked", _("fixed"));
    else if ($doc->archiveid) $action->lay->eSet("locked", _("archived"));
    else if ($doc->control("edit") != "") $action->lay->eSet("locked", _("read only"));
    else if ($doc->locked == 0) {
        $action->lay->eSet("locked", _("not locked"));
    } else {
        $user = new Account("", abs($doc->locked));
        $action->lay->eSet("locked", $user->firstname . " " . $user->lastname);
        $action->lay->Set("lockedid", $user->fid);
    }
    
    $action->lay->Set("dhelp", "none");
    if ($doc->fromid > 0) {
        $cdoc = $doc->getFamilyDocument();
        $action->lay->eSet("classtitle", $cdoc->getTitle());
        if (getFamilyHelpFile($action, $doc->fromid)) {
            $action->lay->Set("dhelp", "");
            $action->lay->Set("helpid", $doc->fromid);
        }
    } else {
        $action->lay->Set("classtitle", _("no family"));
    }
    $action->lay->Set("postitid", ($doc->postitid > 0) ? $doc->postitid : false);
    $action->lay->Set("waskid", 0);
    $action->lay->Set("latestwaskid", 0);
    
    if ($doc->locked != - 1) {
        $latestidwask = $doc->getLatestIdWithAsk();
        if ($latestidwask) {
            $rdoc = new_doc($doc->dbaccess, $latestidwask);
            
            if (!$rdoc->askIsCompleted()) $action->lay->Set("latestwaskid", $latestidwask);
        }
    } else {
        if (!$doc->askIsCompleted()) {
            $action->lay->Set("waskid", "1");
        }
    }
    
    if (($target == "mail") && ($doc->icon != "")) $action->lay->Set("iconsrc", "cid:icon");
    else $action->lay->Set("iconsrc", $doc->geticon());
    
    if ($doc->fromid > 0) $action->lay->Set("cid", $doc->fromid);
    else $action->lay->Set("cid", $doc->id);
    
    $action->lay->Set("viewstate", "none");
    $action->lay->Set("state", "");
    
    $state = $doc->getState();
    $action->lay->Set("statecolor", $doc->getStateColor("transparent"));
    if ($state) { // see only if it is a transitionnal doc
        if ($doc->locked == - 1) $action->lay->eSet("state", $action->text($state));
        else {
            
            $action->lay->eSet("state", $action->Text($doc->getStateActivity($doc->getState())));
        }
        $action->lay->Set("viewstate", "inherit");
        $action->lay->Set("wid", ($doc->wid > 0) ? $doc->wid : $doc->state);
    }
    $action->lay->Set("version", $doc->version);
    
    $action->lay->Set("title", $doc->getHTMLTitle());
    $action->lay->Set("id", $doc->id);
    
    if ($abstract) {
        // only 3 properties for abstract mode
        $listattr = $doc->GetAbstractAttributes();
    } else {
        $listattr = $doc->GetNormalAttributes();
    }
    // see or don't see head
    $action->lay->Set("HEAD", $dochead);
    $action->lay->Set("ACTIONS", (getHttpVars("viewbarmenu") == 1));
    
    $famMail = new_doc($dbaccess, 'MAIL');
    $canCreateMail = $famMail->control('create') == "";
    $action->lay->Set("amail", (($doc->usefor != "P") && $canCreateMail && ($doc->control('send') == "")) ? "inline" : "none");
    // update access date
    $doc->adate = $doc->getTimeDate(0, true);
    $doc->modify(true, array(
        "adate"
    ) , true);
    if ($doc->delUTag($action->user->id, "TOVIEW") == "") {
        $err = $doc->addUTag($action->user->id, "VIEWED");
    }
    // Set help URL
    $help = $doc->getHelpPage();
    $action->lay->Set("helpurl", ($help->isAlive()) ? $help->getAttributeHelpUrl() : false);
    $action->lay->Set("helpid", ($help->isAlive()) ? $help->id : false);
}
