<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Generate bar menu
 *
 * @author Anakeen 2000
 * @version $Id: barmenu.php,v 1.54 2008/08/14 09:59:14 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("GENERIC/generic_util.php");
include_once ("FDL/Lib.Attr.php");
// -----------------------------------
function family_defaultmenu(Action & $action)
{
    // -----------------------------------
    global $dbaccess; // use in getChildCatg function
    $dirid = $action->getArgument("dirid", getDefFld($action)); // folder where search
    $catg = $action->getArgument("catg", 1); // catg where search
    $pds = $action->getArgument("pds"); // special extra parameters used by parametrable searches
    $dbaccess = $action->GetParam("FREEDOM_DB");
    
    $onefamOrigin = $action->getArgument("onefam"); // onefam origin
    $famid = getDefFam($action);
    $defaultMenu = array();
    $fdoc = new_Doc($dbaccess, $famid);
    
    if ($catg > 1) $fld = new_Doc($dbaccess, $catg);
    else $fld = new_Doc($dbaccess, $dirid);
    //change famid if it is a simplesearch
    $sfamid = $famid;
    $sfdoc = $fdoc; // search family
    if ($fld->isAlive()) {
        $sfamid = $fld->getValue("se_famid");
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
            "id" => $famid
        );
    } else $child = array();
    
    $onlyonefam = (getInherit($action, $famid) == "N");
    if (!$onlyonefam) $child+= $fdoc->GetChildFam($fdoc->id, true);
    
    $tnewmenu = array();
    if ($action->HasPermission("GENERIC")) {
        foreach ($child as $k => $vid) {
            
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
    foreach ($lattr as $k => $a) {
        if ((($a->type == "enum") || ($a->type == "enumlist")) && (($a->phpfile != "-") && ($a->getOption("bmenu") != "no"))) {
            
            $tmkind = array();
            $enum = $a->getenum();
            $enumItems = array();
            foreach ($enum as $kk => $ki) {
                $klabel = $a->getenumLabel($ki);
                
                $klabel = array_pop(explode('/', $klabel, substr_count($kk, '.') + 0));
                
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
    $searchItemMenu = array();
    $toolsItemMenu['newsearch'] = array(
        "label" => _("New search") ,
        "target" => "finfo",
        "url" => sprintf('?app=GENERIC&amp;action=GENERIC_EDIT&amp;se_memo=yes&amp;classid=16&amp;onlysubfam=%s&amp;sfamid=%s', $famid, $famid)
    );
    
    $toolsItemMenu['newreport'] = array(
        "label" => _("New report") ,
        "target" => "finfo",
        "url" => sprintf('?app=GENERIC&amp;action=GENERIC_EDIT&amp;se_memo=yes&amp;classid=25&amp;onlysubfam=%s&amp;sfamid=%s', $famid, $famid)
    );
    if ($action->HasPermission("GENERIC_MASTER")) {
        $toolsItemMenu['imvcard'] = array(
            "label" => _("Import file") ,
            "target" => "finfo",
            "url" => sprintf('?app=GENERIC&amp;action=GENERIC_EDITIMPORT&amp;famid=%s', $famid)
        );
    }
    
    if (($dirid < 1000000000) && ($catg > 1)) {
        
        $toolsItemMenu['memosearch'] = array(
            "label" => _("memosearch") ,
            "target" => "fhidden",
            "url" => sprintf('?app=GENERIC&amp;action=GENERIC_MEMOSEARCH&amp;famid=%s&amp;
            psearchid=%s', $famid, $catg)
        );
    }
    
    $toolsItemMenu['viewsearch'] = array(
        "label" => _("View my searches") ,
        "target" => "_self",
        "url" => sprintf('?app=GENERIC&amp;action=GENERIC_SEARCH&amp;catg=0&amp;onefam=%s&amp;mysearches=yes&amp;famid=%s', $onefamOrigin, $famid)
    );
    /*
    $toolsItemMenu['searches'] = array(
                                      "label" => _("Searches") ,
                                      "items"=>$searchItemMenu);
    */
    if ($action->HasPermission("GED", "FREEDOM")) {
        $toolsItemMenu['folders'] = array(
            "label" => _("folders") ,
            "target" => "freedom$famid",
            "url" => sprintf('?app=FREEDOM&amp;action=FREEDOM_FRAME&amp;dirid=%s&amp;famid=%s', getDefFld($action) , $famid)
        );
    }
    
    $toolsItemMenu['prefs'] = array(
        "label" => _("Preferences") ,
        "target" => "_self",
        "url" => sprintf('?app=GENERIC&amp;action=GENERIC_PREFS&amp;dirid=%s&amp;famid=%s', getDefFld($action) , $famid)
    );
    
    if ($action->HasPermission("GENERIC_MASTER")) {
        $toolsItemMenu['kindedit'] = array(
            "label" => _("Edit enum attributes") ,
            "target" => "finfo",
            "url" => sprintf('?app=GENERIC&action=GENERIC_EDITFAMCATG&famid=%s', $famid)
        );
    }
    
    $globalItemMenu = array();
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
    
    $action->lay->Set("topid", getDefFld($action));
    $action->lay->Set("dirid", $dirid);
    $action->lay->Set("catg", $catg);
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
    
    while (list($k, $v) = each($tsort)) {
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
    
    $action->lay->set("ukey", getDefUKey($action));
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
            "href" => sprintf('?app=GENERIC&amp;action=GENERIC_USORT&amp;onefam=%s&amp;catg=%s&amp;famid=%s&amp;aorder=%s', $onefamOrigin, $dirid, $famid, $v["aorder"])
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
        if ($usort[0] == "-") {
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
