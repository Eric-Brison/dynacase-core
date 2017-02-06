<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * View set of documents of same family
 *
 * @author Anakeen
 * @version $Id: generic_list.php,v 1.43 2008/10/30 09:23:46 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/viewfolder.php");
include_once ("GENERIC/generic_util.php");
/**
 * View list of document from folder (or searches)
 * @param Action &$action current action
 * @global string $dirid Http var : folder identifier to see
 * @global string $catg Http var :
 * @global string $page Http var : page number
 * @global string $tabs Http var : tab number 1 for ABC, 2 for DEF, if onglet=Y..
 * @global string $onglet Http var : [Y|N] Y if want see alphabetics tabs
 * @global string $famid Http var : main family identifier
 * @global string $sqlorder Http var : order by attribute
 * @global string $gview Http var : [abstract|column] view mode
 */
function generic_list(&$action)
{
    // Set the globals elements
    // Get all the params
    $dirid = getHttpVars("dirid"); // directory to see
    $catgid = getHttpVars("catg", $dirid); // category
    $startpage = getHttpVars("page", "0"); // page to see
    $tab = getHttpVars("tab", "0"); // tab to see 1 for ABC, 2 for DEF, ...
    $onglet = getHttpVars("onglet"); // if you want onglet
    $famid = getHttpVars("famid"); // family restriction
    $clearkey = (getHttpVars("clearkey", "N") == "Y"); // delete last user key search
    $sqlorder = getHttpVars("sqlorder"); // family restriction
    $onefamOrigin = getHttpVars("onefam"); // onefam orgigin
    $target = "finfo$famid";
    setHttpVar("target", $target);
    if (!($famid > 0)) $famid = getDefFam($action);
    
    $searchMode = $action->getParam("GENE_SEARCHMODE", "words");
    if ($onefamOrigin) {
        $onefamSearchMode = ApplicationParameterManager::getParameterValue($onefamOrigin, "ONEFAM_SEARCHMODE");
        if ($onefamSearchMode) {
            $searchMode = $onefamSearchMode;
        }
    }
    $paginationType = "basic";
    $column = generic_viewmode($action, $famid); // choose the good view mode
    $dbaccess = $action->dbaccess;
    $packGeneric = "";
    $action->parent->addJsRef($action->getParam("CORE_JSURL") . "/DHTMLapi.js", false, $packGeneric);
    $action->parent->addJsRef($action->getParam("CORE_JSURL") . "/geometry.js", false, $packGeneric);
    $action->parent->addJsRef($action->getParam("CORE_JSURL") . "/AnchorPosition.js", false, $packGeneric);
    $action->parent->addJsRef($action->getParam("CORE_PUBURL") . "/FDL/Layout/common.js", false, $packGeneric);
    $action->parent->addJsRef($action->getParam("CORE_PUBURL") . "/FDL/Layout/popupfunc.js", false, $packGeneric);
    $action->parent->addCssRef("css/dcp/main.css");
    $action->parent->addCssRef("GENERIC:generic_list.css", true);
    
    $applicationPaginationConfig = json_decode(ApplicationParameterManager::getParameterValue($onefamOrigin, "ONEFAM_FAMCONFIG") , true);
    if (!empty($applicationPaginationConfig)) {
        $famName = getNameFromId($dbaccess, $famid);
        if (isset($applicationPaginationConfig[$famName]) && isset($applicationPaginationConfig[$famName]["paginationType"]) && !empty($applicationPaginationConfig[$famName]["paginationType"])) {
            $paginationType = $applicationPaginationConfig[$famName]["paginationType"];
        } else if (isset($applicationPaginationConfig["*"]) && isset($applicationPaginationConfig["*"]["paginationType"]) && !empty($applicationPaginationConfig["*"]["paginationType"])) {
            $paginationType = $applicationPaginationConfig["*"]["paginationType"];
        }
    }
    //change famid if it is a simplesearch
    $sfamid = $famid;
    if ($dirid) {
        $dir = new_doc($dbaccess, $dirid);
        if ($dir->isAlive() && ($dir->defDoctype == 'S')) {
            $sfamid = $dir->getRawValue("se_famid");
        }
    }
    if ($onglet) {
        $wonglet = ($onglet != 'N');
    } else {
        $wonglet = (getTabLetter($action, $famid) == 'Y');
    }
    
    $dir = new_Doc($dbaccess, $dirid);
    $catg = new_Doc($dbaccess, $catgid);
    $action->lay->set("folderid", "0");
    $pds = "";
    if ($catg->doctype == 'D') $action->lay->set("folderid", $catg->id);
    $action->lay->set("pds", "");
    if ($catgid) {
        $catg = new_Doc($dbaccess, $catgid);
        $catgid = $catg->id;
        $pds = $catg->urlWhatEncodeSpec("");
        $action->lay->set("pds", $pds);
        
        $action->lay->set("fldtitle", $dir->getHTMLTitle());
    } else {
        if ($dirid == 0) {
            $action->lay->set("fldtitle", _("precise search"));
            $action->lay->set("pds", "");
        } else {
            $action->lay->set("fldtitle", $dir->getHTMLTitle());
        }
    }
    
    $action->lay->eSet("ONEFAMORIGIN", $onefamOrigin);
    $action->lay->eSet("famtarget", $target);
    $action->lay->eSet("dirid", $dir->id);
    $action->lay->eSet("tab", $tab);
    $action->lay->eSet("catg", $catgid);
    $action->lay->eSet("famid", $famid);
    $slice = $action->getParam("CARD_SLICE_LIST", 5);
    //  $action->lay->set("next",$start+$slice);
    //$action->lay->set("prev",$start-$slice);
    $action->lay->set("nexticon", "");
    $action->lay->set("previcon", "");
    if ($searchMode === "words") {
        $action->lay->set("SearchPlaceHolder", ___("Search words", "generic"));
    } else {
        $action->lay->set("SearchPlaceHolder", ___("Search characters", "generic"));
    }
    
    if ($sqlorder == "") {
        /* This should be in sync with the default value $def from getDefUSort() */
        $sqlorder = getDefUSort($action, "-revdate", $sfamid);
        setHttpVar("sqlorder", $sqlorder);
    }
    
    if ($famid > 0) {
        if ($sqlorder != "") {
            $ndoc = createDoc($dbaccess, $sfamid, false);
            if ($sqlorder[0] == "-") {
                $sqlorder = substr($sqlorder, 1);
            }
            if (!in_array($sqlorder, $ndoc->fields)) {
                setHttpVar("sqlorder", "");
            }
        }
    }
    if ($clearkey) {
        $action->setParamU("GENE_LATESTTXTSEARCH", setUkey($action, $famid, $keyword = ''));
    }
    $only = false;
    if ($dirid) {
        if ($dir->fromid == 38) {
            $famid = 0; // special researches
            setHttpVar("sqlorder", "--"); // no sort possible
            
        }
        $only = (getInherit($action, $famid) == "N");
        viewfolder($action, true, false, $column, $slice, array() , ($only) ? -(abs($famid)) : abs($famid) , $paginationType);
        // can see next
        $action->lay->set("nexticon", $action->getIcon("next.png", N_("next") , 16));
    }
    getFamilySearches($action, $dbaccess, $famid, $only);
    
    if ($startpage > 0) {
        // can see prev
        $action->lay->set("previcon", $action->getIcon("prev.png", N_("prev") , 16));
    }
    
    if ($dirid && $wonglet) {
        // hightlight the selected part (ABC, DEF, ...)
        $onglet = array(
            array(
                "onglabel" => "A B C",
                "ongdir" => "1"
            ) ,
            array(
                "onglabel" => "D E F",
                "ongdir" => "2"
            ) ,
            array(
                "onglabel" => "G H I",
                "ongdir" => "3"
            ) ,
            array(
                "onglabel" => "J K L",
                "ongdir" => "4"
            ) ,
            array(
                "onglabel" => "M N O",
                "ongdir" => "5"
            ) ,
            array(
                "onglabel" => "P Q R S",
                "ongdir" => "6"
            ) ,
            array(
                "onglabel" => "T U V",
                "ongdir" => "7"
            ) ,
            array(
                "onglabel" => "W X Y Z",
                "ongdir" => "8"
            ) ,
            array(
                "onglabel" => "A - Z",
                "ongdir" => "0"
            )
        );
        
        foreach ($onglet as $k => $v) {
            if ($v["ongdir"] == $tab) $onglet[$k]["ongclass"] = "onglets";
            else $onglet[$k]["ongclass"] = "onglet";
            $onglet[$k]["onglabel"] = str_replace(" ", "<BR>", $v["onglabel"]);
        }
        
        $action->lay->setBlockData("ONGLET", $onglet);
    }
    $paginationConfig = array(
        "type" => $paginationType,
        "onefamconfig" => $onefamOrigin,
        "tab" => $tab,
        "dirid" => $dirid,
        "catg" => $catgid,
        "famid" => $famid,
        "pds" => $pds,
        "onglet" => $wonglet ? "Y" : "N"
    );
    $action->parent->setVolatileParam("paginationConfig", $paginationConfig);
    $action->lay->set("onglet", $wonglet ? "Y" : "N");
    $action->lay->set("hasOnglet", (!empty($wonglet)));
    
    $action->lay->eSet("tkey", getDefUKey($action));
}

function generic_viewmode(Action & $action, $famid)
{
    $prefview = getHttpVars("gview");
    
    $tmode = explode(",", $action->getParam("GENE_VIEWMODE"));
    // explode parameters
    $tview = array();
    $tview[$famid] = '';
    foreach ($tmode as $v) {
        if ($v) {
            list($fid, $vmode) = explode("|", $v);
            $tview[$fid] = $vmode;
        }
    }
    switch ($prefview) {
        case "column":
        case "abstract":
            $tview[$famid] = $prefview;
            // implode parameters to change user preferences
            $tmode = array();
            foreach ($tview as $k => $v) {
                if ($k > 0) $tmode[] = "$k|$v";
            }
            $pmode = implode(",", $tmode);
            $action->setParamU("GENE_VIEWMODE", $pmode);
            
            break;
        }
        
        switch ($tview[$famid]) {
            case "column":
                $action->layout = $action->getLayoutFile("generic_listv.xml");
                $action->lay = new Layout($action->layout, $action);
                //    $action->parent->addJsRef($action->getParam("CORE_PUBURL")."/FREEDOM/Layout/sorttable.js");
                //    $column=true;
                $column = 2;
                break;

            case "abstract":
            default:
                $action->layout = $action->getLayoutFile("generic_list.xml");
                $action->lay = new Layout($action->layout, $action);
                $column = false;
                
                break;
        }
        return $column;
}

function getFamilySearches(Action $action, $dbaccess, $famid, $only = false)
{
    // search searches in primary folder
    
    /**
     * @var DocFam $fdoc
     */
    $fdoc = new_Doc($dbaccess, $famid);
    $dirid = getHttpVars("dirid"); // search
    $catgid = getHttpVars("catg", $dirid); // primary directory
    if ($catgid == 0) $catgid = $dirid;
    
    $action->lay->set("ONESEARCH", false);
    $utreeSearch = array();
    $streeSearch = array();
    $hasSysSearch = false;
    if ($famid) {
        if (isset($fdoc->dfldid) && $fdoc->dfldid > 0) {
            // shared searches
            $s = new SearchDoc($action->dbaccess, 5);
            $s->useCollection($fdoc->dfldid);
            $s->setObjectReturn();
            $s->search();
            $dl = $s->getDocumentList();
            /**
             * @var Doc $search
             */
            foreach ($dl as $ids => $search) {
                if (($search->doctype == "S") && ($search->fromid != $fdoc->id)) {
                    if ($search->control("execute") != '') {
                        continue;
                    }
                    $stitle = $search->getHTMLTitle();
                    
                    $streeSearch[$ids]["id"] = $ids;
                    $streeSearch[$ids]["title"] = $stitle;
                    $streeSearch[$ids]["selected"] = ($ids == $catgid) ? "1" : "0";
                    $streeSearch[$ids]["isreport"] = "0";
                    $streeSearch[$ids]["gui_color"] = $search->getRawValue(\Dcp\AttributeIdentifiers\Search::gui_color);
                    $streeSearch[$ids]["isparam"] = "0";
                    $keys = $search->getRawValue("se_keys");
                    if (preg_match('/\?/', $keys)) {
                        $streeSearch[$ids]["title"] = "(P)" . $stitle;
                        $streeSearch[$ids]["isparam"] = "1";
                    }
                    if ($search->fromid == 25) {
                        $streeSearch[$ids]["title"] = "(R)" . $stitle;
                        $streeSearch[$ids]["isreport"] = "1";
                    }
                }
            }
        }
        $hasSysSearch = count($streeSearch) > 0;
        $action->lay->set("ONESEARCH", ($hasSysSearch));
        
        $action->lay->setBlockData("SYSSEARCH", $streeSearch);
        // search user searches for family
        $s = new SearchDoc($action->dbaccess, 5);
        $s->addFilter("owner=%d", $action->user->id);
        // Need take account of generic parameter GENE_INHERIT
        $child = $fdoc->getChildFam();
        if ($only || count($child) == 0) {
            $s->addFilter("se_famid='%s'", $fdoc->id);
        } else {
            $childIds = array_keys($child);
            $childIds[] = $fdoc->id;
            $s->addFilter($s->sqlcond($childIds, "se_famid"));
        }
        
        $s->addFilter("usefor!='G'");
        $s->addFilter("doctype != 'T'");
        $s->addFilter("se_memo='yes'");
        $s->setObjectReturn(true);
        $dl = $s->search()->getDocumentList();
        $action->lay->set("MSEARCH", false);
        foreach ($dl as $ids => $search) {
            if (!isset($streeSearch[$ids])) {
                if ($search->control("execute") != '') {
                    continue;
                }
                $stitle = $search->getHTMLTitle();
                $utreeSearch[$ids]["id"] = $ids;
                $utreeSearch[$ids]["title"] = $stitle;
                $utreeSearch[$ids]["selected"] = ($ids == $catgid) ? "1" : "0";
                $utreeSearch[$ids]["isreport"] = "0";
                $utreeSearch[$ids]["isparam"] = "0";
                $utreeSearch[$ids]["gui_color"] = $search->getRawValue(\Dcp\AttributeIdentifiers\Search::gui_color);
                $keys = $search->getRawValue("se_keys");
                if (preg_match('/\?/', $keys)) {
                    $utreeSearch[$ids]["title"] = "(P)" . $stitle;
                    $utreeSearch[$ids]["isparam"] = "1";
                }
                if ($search->fromid == 25) {
                    $utreeSearch[$ids]["title"] = "(R)" . $stitle;
                    $utreeSearch[$ids]["isreport"] = "1";
                }
            }
        }
    }
    $action->lay->set("MSEARCH", (count($utreeSearch) > 0 && $hasSysSearch));
    $action->lay->setBlockData("USERSEARCH", $utreeSearch);
    if (count($streeSearch) + count($utreeSearch) > 0) {
        $action->lay->set("ONESEARCH", true);
    }
}
