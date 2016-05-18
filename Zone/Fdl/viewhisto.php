<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * View Document History
 *
 * @author Anakeen
 * @version $Id: viewhisto.php,v 1.27 2009/01/04 18:36:37 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.Doc.php");
function viewhisto(Action & $action)
{
    $dbaccess = $action->dbaccess;
    $docid = GetHttpVars("id", 0);
    $viewapp = GetHttpVars("viewapp", "FDL");
    $viewact = GetHttpVars("viewact", "FDL_CARD");
    $target = GetHttpVars("target", "");
    $viewrev = (GetHttpVars("viewrev", "Y") == "Y");
    $comment = GetHttpVars("comment", _("no comment"));
    $notice = (GetHttpVars("notice", "Y") == "Y"); // view notice
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/subwindow.js");
    $action->parent->AddJsRef($action->GetParam("CORE_PUBURL") . "/FDL/Layout/common.js");
    $action->parent->addJsRef("lib/jquery/jquery.js");
    $action->parent->addJsRef("lib/jquery-ui/js/jquery-ui.js");
    $action->parent->addCssRef("css/dcp/jquery-ui.css");
    $action->parent->addJsRef("lib/jquery-dataTables/js/jquery.dataTables.min.js");
    $action->parent->addCssRef("lib/jquery-dataTables/css/jquery.dataTables_themeroller.css");
    $action->parent->addJsRef("FDL/Layout/viewhisto.js");
    
    $localeconfig = getLocaleConfig();
    if ($localeconfig !== false) {
        $formatDate = $localeconfig['dateFormat'] . " %H:%M:%S";
    } else {
        $formatDate = '';
    }
    $doc = new_Doc($dbaccess, $docid, true);
    
    if ($doc->wid) {
        $action->lay->Set("lastState", $doc->getStateActivity(_("Current_state")));
    } else {
        $action->lay->Set("lastState", $doc->getHtmlTitle());
    }
    $action->lay->Set("lastRevision", $doc->revision);
    $action->lay->eSet("lastOwner", Account::getDisplayName($doc->owner));
    
    $action->lay->Set("lastColor", $doc->getStateColor('transparent'));
    $action->lay->Set("docid", $doc->id);
    $action->lay->Set("lastDate", stringDateToLocaleDate(strftime("%Y-%m-%d %T", $doc->revdate) , $formatDate));
    $action->lay->Set("pastRevision", ($doc->revision > 0));
    $tc = $doc->getHisto();
    $tlc = array();
    foreach ($tc as $vc) {
        if ((!$notice) && ($vc["level"] < HISTO_INFO)) {
            $hasnotice = true;
            continue;
        }
        $stime = $vc["date"];
        /*	if (ereg("([0-9]{1,2})/([0-9]{1,2})/([0-9]{1,4}) ([0-2]{0,1}[0-9]):([0-5]{0,1}[0-9])",
          $reg[1], $regt)) {
          $stime=strftime ("%a %d %b %Y %H:%M",mktime($regt[4],$regt[5],$regt[6],$regt[2],$regt[1],$regt[3]));
          } else $stime=$reg[1];
        */
        $tlc[] = array(
            "cdate" => stringDateToLocaleDate($stime, $formatDate) ,
            "cauthor" => htmlspecialchars($vc["uname"]),
            "clevel" => $vc["level"],
            "ccomment" => nl2br(htmlentities($vc["comment"], ENT_COMPAT, "UTF-8"))
        );
    }
    $action->lay->SetBlockData("LASTCOMMENT", $tlc);
    
    $action->lay->Set("title", $doc->getHTMLTitle());
    $action->lay->eSet("target", $target);
    $action->lay->eSet("VIEWAPP", $viewapp);
    $action->lay->eSet("VIEWACT", $viewact);
    $action->lay->eSet("VIEWREV", $viewrev);
    $action->lay->eSet("notice", $notice);
    $hastate = false;
    
    $s = new SearchDoc($action->dbaccess);
    $s->addFilter("initid = %d", $doc->initid);
    $s->setOrder("revision desc");
    $s->latest = false;
    $s->setObjectReturn();
    $dl = $s->search()->getDocumentList();
    
    $hasnotice = false;
    $trdoc = array();
    $tversion = array();
    $iversion = 0;
    $hasVersion = false;
    /**
     * @var Doc $rdoc
     */
    foreach ($dl as $k => $rdoc) {
        if ($rdoc->locked != - 1) continue;
        if ($rdoc->control('view')) continue;
        $trdoc[$k]["owner"] = htmlspecialchars(Account::getDisplayName($rdoc->owner));
        if ($k == 0) $trdoc[$k]["owner"] = _("Modification date");
        
        $trdoc[$k]["revision"] = $rdoc->revision;
        $trdoc[$k]["version"] = htmlspecialchars($rdoc->version);
        $trdoc[$k]["target"] = ($target == "") ? "doc_" . $rdoc->id : $target;
        
        $state = $rdoc->getState();
        $color = $rdoc->getStateColor();
        $sact = $rdoc->getStateActivity(_("Current_state"));
        $trdoc[$k]["state"] = ($state == "") ? $rdoc->getHTMLTitle() : _($state);
        $hastate = $hastate | ($state != "");
        $trdoc[$k]["color"] = ($color == "") ? "transparent" : $color;
        
        $trdoc[$k]["date"] = stringDateToLocaleDate(strftime("%Y-%m-%d %T", $rdoc->revdate) , $formatDate);
        // special table for versions
        if (!in_array($rdoc->version, array_keys($tversion))) {
            $tversion[$rdoc->version] = "vtr" . $iversion++;
            $trdoc[$k]["cversion"] = true;
        } else {
            $trdoc[$k]["cversion"] = false;
        }
        $trdoc[$k]["vername"] = $tversion[$rdoc->version];
        if ($rdoc->version) $hasVersion = true;
        $trdoc[$k]["COMMENT"] = "COMMENT$k";
        
        $tc = $rdoc->getHisto();
        $tlc = array();
        $kc = 0; // index comment
        foreach ($tc as $vc) {
            if ((!$notice) && ($vc["level"] < HISTO_INFO)) {
                $hasnotice = true;
                continue;
            }
            $stime = $vc["date"];
            /*	if (ereg("([0-9]{1,2})/([0-9]{1,2})/([0-9]{1,4}) ([0-2]{0,1}[0-9]):([0-5]{0,1}[0-9])",
            $reg[1], $regt)) {
            $stime=strftime ("%a %d %b %Y %H:%M",mktime($regt[4],$regt[5],$regt[6],$regt[2],$regt[1],$regt[3]));
            } else $stime=$reg[1];
            */
            $tlc[] = array(
                "cdate" => stringDateToLocaleDate($stime, $formatDate) ,
                "cauthor" => htmlspecialchars($vc["uname"]),
                "clevel" => $vc["level"],
                "ccomment" => nl2br(htmlentities($vc["comment"], ENT_COMPAT, "UTF-8"))
            );
        }
        $action->lay->SetBlockData("COMMENT$k", $tlc);
        
        $trdoc[$k]["id"] = $rdoc->id;
        $trdoc[$k]["divid"] = $k;
    }
    if (!$hasnotice) $action->lay->Set("notice", true);
    // not display detail display
    $action->lay->Set("STATE", $hastate);
    $action->lay->Set("viewdiff", ($s->count() > 1));
    $action->lay->Set("nodetail", ($iversion > 1));
    
    $action->lay->Set("hasversion", $hasVersion);
    $action->lay->eSet("latestVersion", $doc->version);
    $action->lay->SetBlockData("TABLEBODY", $trdoc);
}
