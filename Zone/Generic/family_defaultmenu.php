<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generate bar menu
 *
 * @author Anakeen
 * @version $Id: barmenu.php,v 1.54 2008/08/14 09:59:14 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

require_once "GENERIC/generic_util.php";
require_once "FDL/Lib.Attr.php";
// -----------------------------------
function family_defaultmenu(Action & $action)
{
    // -----------------------------------
    global $dbaccess; // use in getChildCatg function
    $dirid = $action->getArgument("dirid", getDefFld($action)); // folder where search
    $catg = $action->getArgument("catg", 1); // catg where search
    $pds = $action->getArgument("pds"); // special extra parameters used by parametrable searches
    $dbaccess = $action->dbaccess;
    
    $onefamOrigin = $action->getArgument("onefam"); // onefam origin
    $famid = getDefFam($action);
    $defaultMenu = array();
    $fdoc = new_Doc($dbaccess, $famid);
    
    if ($catg > 1) {
        $fld = new_Doc($dbaccess, $catg);
    } else {
        $fld = new_Doc($dbaccess, $dirid);
    }
    //change famid if it is a simplesearch
    $sfdoc = $fdoc; // search family
    if ($fld->isAlive()) {
        $sfamid = $fld->getRawValue("se_famid");
        if ($sfamid && $sfamid != $fdoc->id) {
            $sfdoc = new_Doc($dbaccess, $sfamid);
            if (!$sfdoc->isAlive()) {
                $sfdoc = $fdoc; // restore if dead
                
            }
        }
    }
    $defaultMenuCreate = array();
    if (($fdoc->control("create") == "") && ($fdoc->control("icreate") == "")) {
        $child[$famid] = array(
            "title" => $fdoc->getTitle() ,
            "id" => $famid,
            "name" => $fdoc->name
        );
    } else {
        $child = array();
    }
    
    $onlyonefam = (getInherit($action, $famid) == "N");
    if (!$onlyonefam) {
        $child+= $fdoc->GetChildFam($fdoc->id, true);
    }
    
    if ($action->HasPermission("GENERIC")) {
        foreach ($child as $vid) {
            $defaultMenuCreate[] = array(
                "label" => DocFam::getLangTitle($vid) ,
                "target" => "finfo",
                "url" => sprintf("?app=GENERIC&amp;action=GENERIC_EDIT&amp;classid=%s", $vid["id"])
            );
        }
    }
    if (count($defaultMenuCreate)) {
        $defaultMenu["create"] = array(
            "label" => _("Creation") ,
            "title" => _("Document creation") ,
            "items" => $defaultMenuCreate
        );
    }
    //--------------------- enum menu -----------------------
    $lattr = $sfdoc->getNormalAttributes();
    /**
     * @var NormalAttribute $a
     */
    foreach ($lattr as $a) {
        if ((($a->type == "enum") || ($a->type == "enumlist")) && (($a->phpfile != "-") && ($a->getOption("bmenu") != "no"))) {
            $tmkind = array();
            $enum = $a->getenum();
            $enumItems = array();
            foreach ($enum as $kk => $ki) {
                $klabel = $a->getenumLabel($kk);
                $tmpArray = explode('/', $klabel, substr_count($kk, '.') + 0);
                $klabel = array_pop($tmpArray);
                $enumItems[] = array(
                    "label" => $klabel,
                    "data-level" => (substr_count($kk, '.') - substr_count($kk, '\.')) ,
                    "href" => sprintf("?app=GENERIC&amp;action=GENERIC_SEARCH_KIND&amp;onefam=%s&amp;famid=%s&amp;kid=%s&amp;aid=%s&amp;catg=%s%s", $onefamOrigin, $famid, urlencode($kk) , $a->id, $catg, $pds)
                );
                $tmkind[] = $a->id . $kk;
            }
            $defaultMenu[$a->id] = array(
                "title" => sprintf(_("Filter on %s") , $a->getLabel()) ,
                "label" => $a->getLabel() ,
                "items" => $enumItems
            );
        }
    }
    //--------------------- tools menu -----------------------
    $toolsItemMenu = array();
    if (empty($onefamOrigin) && $action->HasPermission("GENERIC")) {
        $d = new_doc($dbaccess, 16);
        if ($d->control("create") == "" && $d->control("icreate") == "") {
            $toolsItemMenu['newsearch'] = array(
                "label" => _("New search") ,
                "target" => "finfo",
                "url" => sprintf('?app=GENERIC&amp;action=GENERIC_EDIT&amp;se_memo=yes&amp;classid=16&amp;onlysubfam=%s&amp;sfamid=%s', $famid, $famid)
            );
        }
        
        $d = new_doc($dbaccess, 25);
        if ($d->control("create") == "" && $d->control("icreate") == "") {
            $toolsItemMenu['newreport'] = array(
                "label" => _("New report") ,
                "target" => "finfo",
                "url" => sprintf('?app=GENERIC&amp;action=GENERIC_EDIT&amp;se_memo=yes&amp;classid=25&amp;onlysubfam=%s&amp;sfamid=%s', $famid, $famid)
            );
        }
    }
    
    if (empty($onefamOrigin) && ($dirid < 1000000000) && ($catg > 1)) {
        $toolsItemMenu['memosearch'] = array(
            "label" => _("memosearch") ,
            "target" => "fhidden",
            "url" => sprintf('?app=GENERIC&amp;action=GENERIC_MEMOSEARCH&amp;famid=%s&amp;
            psearchid=%s', $famid, $catg)
        );
    }
    
    if (empty($onefamOrigin)) {
        $toolsItemMenu['viewsearch'] = array(
            "label" => _("View my searches") ,
            "target" => "_overlay",
            "url" => sprintf('?app=GENERIC&amp;action=GENERIC_SEARCH&amp;catg=0&amp;onefam=%s&amp;mysearches=yes&amp;famid=%s', $onefamOrigin, $famid)
        );
    } else {
        $toolsItemMenu['viewsearch'] = array(
            "label" => _("Handle my searches") ,
            "target" => "_overlay",
            "url" => sprintf('?app=%s&amp;action=ONEFAM_MANAGE_SEARCH&amp;famId=%s', $onefamOrigin, $famid)
        );
    }
    
    if (empty($onefamOrigin) && $action->HasPermission("GED", "FREEDOM")) {
        $toolsItemMenu['folders'] = array(
            "label" => _("folders") ,
            "target" => "freedom$famid",
            "url" => sprintf('?app=FREEDOM&amp;action=FREEDOM_FRAME&amp;dirid=%s&amp;famid=%s', getDefFld($action) , $famid)
        );
    }
    
    $toolsItemMenu['prefs'] = array(
        "label" => _("Preferences") ,
        "target" => "_self",
        "url" => sprintf('?app=GENERIC&amp;action=GENERIC_PREFS&amp;dirid=%s&amp;famid=%s&amp;onefam=%s', getDefFld($action) , $famid, $onefamOrigin)
    );
    
    $lmenu = $fdoc->GetMenuAttributes();
    $firstGlobalMenu = true;
    foreach ($lmenu as $k => $v) {
        if ($v->getOption("global") == "yes") {
            $confirm = ($v->getOption("lconfirm") == "yes");
            
            $vis = MENU_ACTIVE;
            if ($v->precond != "") $vis = $fdoc->ApplyMethod($v->precond, MENU_ACTIVE);
            if ($vis == MENU_ACTIVE) {
                
                $textConfirm = '';
                if ($firstGlobalMenu) {
                    $toolsItemMenu['family'] = array(
                        "label" => sprintf("%s", $fdoc->getHTMLTitle()) ,
                        "class" => "ui-widget-header"
                    );
                    $firstGlobalMenu = false;
                }
                
                if ($confirm) {
                    $textConfirm = $v->getOption("tconfirm");
                    if (!$textConfirm) $textConfirm = sprintf(_("Sure %s ?") , addslashes($v->getLabel()));
                }
                $toolsItemMenu[$v->id] = array(
                    "label" => $v->getLabel() ,
                    "target" => $v->getOption("mtarget", $v->getoption('ltarget', $v->id)) ,
                    "confirm" => $textConfirm,
                    "url" => $fdoc->urlWhatEncode($v->link)
                );
            }
        }
    }
    //----------------------------
    // sort menu
    $tsort = array();
    $tsort["-"] = array(
        "said" => "",
        "satitle" => _("no sort") ,
        "aorder" => ""
    );
    /**
     * @var DocFam $sfdoc
     */
    $props = $sfdoc->getSortProperties();
    foreach ($props as $propName => $config) {
        if ($config['sort'] != 'asc' && $config['sort'] != 'desc') {
            continue;
        }
        switch ($propName) {
            case 'state':
                if ($sfdoc->wid > 0) {
                    $tsort["state"] = array(
                        "said" => "state",
                        "satitle" => _("state") ,
                        "aorder" => getAttributeOrder($action, $propName, $config['sort'])
                    );
                }
                break;

            case 'title':
                $tsort["title"] = array(
                    "said" => "title",
                    "satitle" => _("doctitle") ,
                    "aorder" => getAttributeOrder($action, $propName, $config['sort'])
                );
                break;

            case 'initid':
                $tsort["initid"] = array(
                    "said" => "initid",
                    "satitle" => _("createdate") ,
                    "aorder" => getAttributeOrder($action, $propName, $config['sort'])
                );
                break;

            default:
                $label = Doc::$infofields[$propName]['label'];
                if ($label != '') {
                    $label = _($label);
                }
                $tsort[$propName] = array(
                    "said" => $propName,
                    "satitle" => $label,
                    "aorder" => getAttributeOrder($action, $propName, $config['sort'])
                );
        }
    }
    
    foreach ($tsort as $k => $v) {
        $tmsort[$v["said"]] = "sortdoc" . $v["said"];
    }
    $lattr = $sfdoc->GetSortAttributes();
    foreach ($lattr as $k => $a) {
        $pType = parseType($a->type);
        
        if ($pType['type'] == 'docid') {
            $doctitleAttr = $a->getOption('doctitle');
            if ($doctitleAttr != '') {
                /**
                 * @var NormalAttribute $sortAttribute
                 */
                $sortAttribute = false;
                if ($doctitleAttr == 'auto') {
                    $sortAttribute = $sfdoc->getAttribute(sprintf("%s_title", $a->id));
                } else {
                    $sortAttribute = $sfdoc->getAttribute($doctitleAttr);
                }
                if ($sortAttribute === false) {
                    $action->log->error(sprintf("Could not find doctitle attribute '%s' for attribute '%s'", $doctitleAttr, $a->id));
                    continue;
                }
                $tsort[$sortAttribute->id] = array(
                    "said" => $sortAttribute->id,
                    "satitle" => $a->getLabel() ,
                    "aorder" => getAttributeOrder($action, $sortAttribute->id, $sortAttribute->getOption('sortable'))
                );
                $tmsort[$sortAttribute->id] = "sortdoc" . $sortAttribute->id;
                continue;
            }
        }
        
        $tsort[$a->id] = array(
            "said" => $a->id,
            "satitle" => $a->getLabel() ,
            "aorder" => getAttributeOrder($action, $a->id, $a->getOption('sortable'))
        );
        $tmsort[$a->id] = "sortdoc" . $a->id;
    }
    // select the current sort
    $csort = $action->getArgument("sqlorder");
    if ($csort == "") $csort = getDefUSort($action, "--");
    
    if (($csort == '') || ($csort == '--')) {
        $csort = '-';
        $cselect = "&bull;";
    } else if ($csort[0] == '-') {
        $csort = substr($csort, 1);
        $cselect = "&uarr;";
    } else {
        $cselect = "&darr;";
    }
    
    $sortTitle = _("Sort");
    $sortItemMenu = array();
    foreach ($tsort as $k => $v) {
        $dsort = ($csort == $k) ? $cselect : "&nbsp;"; // use puce
        if ($csort == $k) $sortTitle = $v["satitle"] . $cselect;
        $sortItemMenu[] = array(
            "label" => $dsort . '&nbsp;' . $v["satitle"],
            "href" => sprintf("javascript:sendSort('%s','%s','%s','%s','%s')", $onefamOrigin, $dirid, $catg, $famid, $v["aorder"])
        );
    }
    $defaultMenu["sort"] = array(
        "title" => _("Sort") ,
        "label" => $sortTitle,
        "items" => $sortItemMenu
    );
    $defaultMenu["tools"] = array(
        "label" => _("Tools") ,
        "items" => $toolsItemMenu
    );
    return $defaultMenu;
}
function getAttributeOrder(Action & $action, $attrName, $orderBy)
{
    $usort = getDefUSort($action, "__UNDEFINED");
    if ($usort != "__UNDEFINED") {
        /*
         * Invert the sort sign and extract attr name
        */
        if ((!empty($usort)) && $usort[0] == "-") {
            $invertedSortSign = "";
            $sortAttr = substr($usort, 1);
        } else {
            $invertedSortSign = "-";
            $sortAttr = $usort;
        }
        /*
         * If the sort is on the same attr, then
         * we set sort on the same attr but with
         * the inverted sign
        */
        if ($sortAttr == $attrName) {
            return $invertedSortSign . $attrName;
        }
    }
    /*
     * By default, we set the sort sign from the
     * 'sortable' option or the 'sort' property parameter
    */
    if ($orderBy === 'desc') {
        return "-" . $attrName;
    }
    return $attrName;
}
