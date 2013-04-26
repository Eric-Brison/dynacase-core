<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * View set of documents of same family
 *
 * @author Anakeen
 * @version $Id: generic_list.php,v 1.43 2008/10/30 09:23:46 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
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
    $dirid = GetHttpVars("dirid"); // directory to see
    $catgid = GetHttpVars("catg", $dirid); // category
    $startpage = GetHttpVars("page", "0"); // page to see
    $tab = GetHttpVars("tab", "0"); // tab to see 1 for ABC, 2 for DEF, ...
    $onglet = GetHttpVars("onglet"); // if you want onglet
    $famid = GetHttpVars("famid"); // family restriction
    $clearkey = (GetHttpVars("clearkey", "N") == "Y"); // delete last user key search
    $sqlorder = GetHttpVars("sqlorder"); // family restriction
    $onefamOrigin = GetHttpVars("onefam"); // onefam orgigin
    $target = "finfo$famid";
    setHttpVar("target", $target);
    if (!($famid > 0)) $famid = getDefFam($action);
    
    $paginationType = "basic";
    $column = generic_viewmode($action, $famid); // choose the good view mode
    $dbaccess = $action->dbaccess;
    $packGeneric = "";
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/DHTMLapi.js", false, $packGeneric);
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/geometry.js", false, $packGeneric);
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/AnchorPosition.js", false, $packGeneric);
    $action->parent->AddJsRef($action->GetParam("CORE_PUBURL") . "/FDL/Layout/common.js", false, $packGeneric);
    $action->parent->AddJsRef($action->Getparam("CORE_PUBURL") . "/FDL/Layout/popupfunc.js", false, $packGeneric);
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
    $action->lay->Set("pds", "");
    if ($catgid) {
        $catg = new_Doc($dbaccess, $catgid);
        $pds = $catg->urlWhatEncodeSpec("");
        $action->lay->Set("pds", $pds);
        
        $action->lay->Set("fldtitle", $dir->getHTMLTitle());
    } else {
        if ($dirid == 0) {
            $action->lay->Set("fldtitle", _("precise search"));
            $action->lay->Set("pds", "");
        } else {
            $action->lay->Set("fldtitle", $dir->getHTMLTitle());
        }
    }
    
    $action->lay->Set("ONEFAMORIGIN", $onefamOrigin);
    $action->lay->Set("famtarget", $target);
    $action->lay->Set("dirid", $dirid);
    $action->lay->Set("tab", $tab);
    $action->lay->Set("catg", $catgid);
    $action->lay->Set("famid", $famid);
    $mode = getSearchMode($action, $famid);
    $action->lay->Set("FULLMODE", ($mode == "FULL"));
    $slice = $action->GetParam("CARD_SLICE_LIST", 5);
    //  $action->lay->Set("next",$start+$slice);
    //$action->lay->Set("prev",$start-$slice);
    $action->lay->Set("nexticon", "");
    $action->lay->Set("previcon", "");
    
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
    getFamilySearches($action, $dbaccess, $famid);
    if ($dirid) {
        if ($dir->fromid == 38) {
            $famid = 0; // special researches
            setHttpVar("sqlorder", "--"); // no sort possible
            
        }
        $only = (getInherit($action, $famid) == "N");
        viewfolder($action, true, false, $column, $slice, array() , ($only) ? -(abs($famid)) : abs($famid) , $paginationType);
        // can see next
        $action->lay->Set("nexticon", $action->GetIcon("next.png", N_("next") , 16));
    }
    if ($startpage > 0) {
        // can see prev
        $action->lay->Set("previcon", $action->GetIcon("prev.png", N_("prev") , 16));
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
        
        while (list($k, $v) = each($onglet)) {
            if ($v["ongdir"] == $tab) $onglet[$k]["ongclass"] = "onglets";
            else $onglet[$k]["ongclass"] = "onglet";
            $onglet[$k]["onglabel"] = str_replace(" ", "<BR>", $v["onglabel"]);
        }
        
        $action->lay->SetBlockData("ONGLET", $onglet);
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
    $action->lay->Set("onglet", $wonglet ? "Y" : "N");
    $action->lay->Set("hasOnglet", (!empty($wonglet)));
    
    $action->lay->set("tkey", str_replace('"', '&quot;', getDefUKey($action)));
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
            while (list($k, $v) = each($tview)) {
                if ($k > 0) $tmode[] = "$k|$v";
            }
            $pmode = implode(",", $tmode);
            $action->setParamU("GENE_VIEWMODE", $pmode);
            
            break;
        }
        
        switch ($tview[$famid]) {
            case "column":
                $action->layout = $action->GetLayoutFile("generic_listv.xml");
                $action->lay = new Layout($action->layout, $action);
                //    $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FREEDOM/Layout/sorttable.js");
                //    $column=true;
                $column = 2;
                break;

            case "abstract":
            default:
                $action->layout = $action->GetLayoutFile("generic_list.xml");
                $action->lay = new Layout($action->layout, $action);
                $column = false;
                
                break;
        }
        return $column;
}

function getFamilySearches(Action $action, $dbaccess, $famid)
{
    // search searches in primary folder
    
    /**
     * @var DocFam $fdoc
     */
    $fdoc = new_Doc($dbaccess, $famid);
    $dirid = GetHttpVars("dirid"); // search
    $catgid = GetHttpVars("catg", $dirid); // primary directory
    if ($catgid == 0) $catgid = $dirid;
    
    $streeSearch = array();
    if ($fdoc->dfldid > 0) {
        $homefld = new_Doc($dbaccess, $fdoc->dfldid);
        $stree = array();
        if ($homefld->id > 0) {
            $stree = internalGetDocCollection($dbaccess, $homefld->id, "0", "ALL", array() , $action->user->id, "TABLE", 5);
        }
        
        foreach ($stree as $k => $v) {
            if (($v["doctype"] == "S") && ($v["fromid"] != $fdoc->id)) {
                $stitle = htmlspecialchars($v["title"]);
                $streeSearch[$v["id"]] = $v;
                $streeSearch[$v["id"]]["selected"] = ($v["id"] == $catgid) ? "1" : "0";
                $streeSearch[$v["id"]]["isreport"] = "0";
                $streeSearch[$v["id"]]["isparam"] = "0";
                $keys = getv($v, "se_keys");
                if (preg_match('/\?/', $keys)) {
                    $streeSearch[$v["id"]]["title"] = "(P)" . $stitle;
                    $streeSearch[$v["id"]]["isparam"] = "1";
                }
                if ($v["fromid"] == 25) {
                    $streeSearch[$v["id"]]["title"] = "(R)" . $stitle;
                    $streeSearch[$v["id"]]["isreport"] = "1";
                }
            }
        }
    }
    $hasSysSearch = count($streeSearch) > 0;
    $action->lay->set("ONESEARCH", ($hasSysSearch));
    
    $action->lay->SetBlockData("SYSSEARCH", $streeSearch);
    // search user searches for family
    $filter[] = "owner=" . $action->user->id;
    $filter[] = "se_famid='$famid'";
    $filter[] = "usefor!='G'";
    $filter[] = "doctype != 'T'";
    $filter[] = "se_memo='yes'";
    $action->lay->set("MSEARCH", false);
    $stree = internalGetDocCollection($dbaccess, "0", "0", "ALL", $filter, $action->user->id, "TABLE", 5);
    $streeSearch = array();
    foreach ($stree as $k => $v) {
        if (!isset($streeSearch[$v["id"]])) $streeSearch[$v["id"]] = $v;
        $streeSearch[$v["id"]]["selected"] = ($v["id"] == $catgid) ? "1" : "0";
        $streeSearch[$v["id"]]["isreport"] = "0";
        $streeSearch[$v["id"]]["isparam"] = "0";
        $keys = getv($v, "se_keys");
        if (preg_match('/\?/', $keys)) {
            $streeSearch[$v["id"]]["title"] = "(P)" . $streeSearch[$v["id"]]["title"];
            $streeSearch[$v["id"]]["isparam"] = "1";
        }
        if ($v["fromid"] == 25) {
            $streeSearch[$v["id"]]["title"] = "(R)" . $streeSearch[$v["id"]]["title"];
            $streeSearch[$v["id"]]["isreport"] = "1";
        }
    }
    $action->lay->set("MSEARCH", (count($streeSearch) > 0 && $hasSysSearch));
    $action->lay->SetBlockData("USERSEARCH", $streeSearch);
    if (count($streeSearch) > 0) {
        $action->lay->set("ONESEARCH", true);
    }
}
