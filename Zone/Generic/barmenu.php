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
// -----------------------------------
function barmenu(&$action)
{
    // -----------------------------------
    global $dbaccess; // use in getChildCatg function
    
    $dirid = GetHttpVars("dirid", getDefFld($action)); // folder where search
    $catg = GetHttpVars("catg", 1); // catg where search
    if ($action->Read("navigator", "") == "EXPLORER") {
        // special for position style
        $action->lay->set("positionstyle", "");
        $action->lay->set("fhelp", "_blank");
    } else {
        $action->lay->set("positionstyle", "fixed");
        $action->lay->set("fhelp", "fhidden");
    }
    
    $dbaccess = $action->GetParam("FREEDOM_DB");
    
    $famid = getDefFam($action);
    
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
    
    $action->lay->set("pds", $fld->urlWhatEncodeSpec("")); // parameters for searches
    if (($fdoc->control("create") == "") && ($fdoc->control("icreate") == "")) {
        $child[$famid] = array(
            "title" => $fdoc->getTitle() ,
            "id" => $famid
        );
    } else $child = array();
    
    $onlyonefam = (getInherit($action, $famid) == "N");
    if (!$onlyonefam) $child+= $fdoc->GetChildFam($fdoc->id, true);
    
    $tchild = array();
    $tnewmenu = array();
    foreach ($child as $k => $vid) {
        $tchild[] = array(
            "stitle" => DocFam::getLangTitle($vid) ,
            "subfam" => $vid["id"]
        );
        $tnewmenu[] = "newdoc" . $k;
    }
    $action->lay->SetBlockData("NEWFAM", $tchild);
    
    $action->lay->Set("dcreate", (count($tchild) > 0) ? "" : "none");
    $action->lay->Set("cancreate", (count($tchild) > 0));
    
    $action->lay->Set("ftitle", $fdoc->gettitle());
    
    $action->lay->Set("famid", $famid);
    $action->lay->Set("splitmode", getSplitMode($action, $famid));
    
    include_once ("FDL/popup_util.php");
    include_once ("FDL/Lib.Attr.php");
    //--------------------- kind menu -----------------------
    $lattr = $sfdoc->getNormalAttributes();
    
    $tkind = array();
    foreach ($lattr as $k => $a) {
        if ((($a->type == "enum") || ($a->type == "enumlist")) && (($a->phpfile != "-") && ($a->getOption("bmenu") != "no"))) {
            
            $tkind[] = array(
                "kindname" => $a->getLabel() ,
                "kindid" => $a->id,
                "vkind" => "kind" . $a->id
            );
            $tvkind = array();
            $tmkind = array();
            $enum = $a->getenum();
            foreach ($enum as $kk => $ki) {
                $klabel = $a->getenumLabel($ki);
                
                $klabel = array_pop(explode('/', $klabel, substr_count($kk, '.') + 0));
                $tvkind[] = array(
                    "ktitle" => $klabel,
                    "level" => (substr_count($kk, '.') - substr_count($kk, '\.')) * 20,
                    "kid" => str_replace('\.', '.', $kk) ,
                    "urlkid" => urlencode($kk)
                );
                $tmkind[] = $a->id . $kk;
            }
            $action->lay->SetBlockData("kind" . $a->id, $tvkind);
            
            popupInit($a->id . "menu", $tmkind);
            foreach ($tmkind as $km => $vid) {
                popupActive($a->id . "menu", 1, $vid);
            }
        }
    }
    
    $action->lay->SetBlockData("KIND", $tkind);
    $action->lay->SetBlockData("MKIND", $tkind);
    
    $action->lay->Set("nbcol", 4 + count($tkind));
    //--------------------- construction of  menu -----------------------
    popupInit("newmenu", $tnewmenu);
    
    popupInit("helpmenu", array(
        'help',
        'imvcard',
        'folders',
        'newdsearch',
        'newreport',
        'viewdsearch',
        'memosearch',
        'isplit',
        'cview',
        'aview',
        'kindedit',
        'prefs'
    ));
    
    $lmenu = $fdoc->GetMenuAttributes();
    foreach ($lmenu as $k => $v) {
        if ($v->getOption("global") == "yes") {
            $confirm = ($v->getOption("lconfirm") == "yes");
            $tmenu[$k] = array(
                "mid" => $v->id,
                "mtitle" => $v->getLabel() ,
                "confirm" => ($confirm) ? "true" : "false",
                "tconfirm" => ($confirm) ? sprintf(_("Sure %s ?") , addslashes($v->getLabel())) : "",
                "murl" => addslashes($fdoc->urlWhatEncode($v->link))
            );
            
            popupAddItem('helpmenu', $v->id);
            $vis = MENU_ACTIVE;
            if ($v->precond != "") $vis = $fdoc->ApplyMethod($v->precond, MENU_ACTIVE);
            if ($vis == MENU_ACTIVE) popupActive("helpmenu", 1, $v->id);
        }
    }
    
    $action->lay->setBlockData("FAMMENU", $tmenu);
    
    if ($action->HasPermission("GENERIC_MASTER")) popupActive("helpmenu", 1, 'kindedit');
    else popupInvisible("helpmenu", 1, 'kindedit');
    
    if ($action->HasPermission("GENERIC")) {
        
        while (list($k, $vid) = each($tnewmenu)) {
            popupActive("newmenu", 1, $vid);
        }
    } else {
        
        while (list($k, $vid) = each($tnewmenu)) {
            popupInactive("newmenu", 1, $vid);
        }
    }
    
    if (($dirid < 1000000000) && ($catg > 1)) popupActive("helpmenu", 1, 'memosearch');
    else popupInvisible("helpmenu", 1, 'memosearch');
    
    if ($action->HasPermission("GENERIC_MASTER")) {
        popupActive("helpmenu", 1, 'imvcard');
    } else {
        popupInvisible("helpmenu", 1, 'imvcard');
    }
    
    popupInvisible("helpmenu", 1, 'isplit');
    popupInvisible("helpmenu", 1, 'cview');
    popupInvisible("helpmenu", 1, 'aview');
    popupActive("helpmenu", 1, 'prefs');
    popupActive("helpmenu", 1, 'newdsearch');
    popupActive("helpmenu", 1, 'newreport');
    popupActive("helpmenu", 1, 'viewdsearch');
    
    popupInactive("helpmenu", 1, 'help'); // for the moment need to rewrite documentation
    popupInvisible("helpmenu", 1, 'folders');
    if ($idappfree = $action->parent->Exists("FREEDOM")) {
        
        $permission = new Permission($action->dbaccess, array(
            $action->user->id,
            $idappfree
        ));
        
        if (($action->user->id == 1) || ($permission->isAffected() && (count($permission->privileges) > 0))) {
            popupActive("helpmenu", 1, 'folders');
        }
    }
    
    $action->lay->Set("topid", getDefFld($action));
    $action->lay->Set("dirid", $dirid);
    $action->lay->Set("catg", $catg);
    //----------------------------
    // sort menu
    $tsort = array(
        "-" => array(
            "said" => "",
            "satitle" => _("no sort")
        ) ,
        "title" => array(
            "said" => "title",
            "satitle" => _("doctitle")
        ) ,
        "initid" => array(
            "said" => "initid",
            "satitle" => _("createdate")
        ) ,
        "revdate" => array(
            "said" => "revdate",
            "satitle" => _("revdate")
        )
    );
    if ($sfdoc->wid > 0) {
        $tsort["state"] = array(
            "said" => "state",
            "satitle" => _("state")
        );
    }
    $tmsort[] = "sortdesc";
    while (list($k, $v) = each($tsort)) {
        $tmsort[$v["said"]] = "sortdoc" . $v["said"];
    }
    $lattr = $sfdoc->GetSortAttributes();
    foreach ($lattr as $k => $a) {
        $pType = parseType($a->type);
        
        if ($pType['type'] == 'docid') {
            $doctitleAttr = $a->getOption('doctitle');
            if ($doctitleAttr != '') {
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
                    "satitle" => sprintf("%s (title)", $a->getLabel())
                );
                $tmsort[$sortAttribute->id] = "sortdoc" . $sortAttribute->id;
                continue;
            }
        }
        
        $tsort[$a->id] = array(
            "said" => $a->id,
            "satitle" => $a->getLabel()
        );
        $tmsort[$a->id] = "sortdoc" . $a->id;
    }
    
    $action->lay->set("ukey", getDefUKey($action));
    // select the current sort
    $csort = GetHttpVars("sqlorder");
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
    
    $action->lay->set("sortby", _("Sort"));
    foreach ($tsort as $k => $v) {
        $tsort[$k]["dsort"] = ($csort == $k) ? $cselect : "&nbsp;"; // use puce
        if ($csort == $k) $action->lay->set("sortby", $v["satitle"] . $cselect);
    }
    popupInit("sortmenu", $tmsort);
    reset($tmsort);
    while (list($k, $v) = each($tmsort)) {
        popupActive("sortmenu", 1, $v);
    }
    $action->lay->SetBlockData("USORT", $tsort);
    
    popupGen(1);
}
?>
