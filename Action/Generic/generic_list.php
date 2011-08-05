<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * View set of documents of same family
 *
 * @author Anakeen 2000
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
 * @global dirid Http var : folder identificator to see
 * @global catg Http var :
 * @global page Http var : page number
 * @global tabs Http var : tab number 1 for ABC, 2 for DEF, if onglet=Y..
 * @global onglet Http var : [Y|N] Y if want see alphabetics tabs
 * @global famid Http var : main family identificator
 * @global sqlorder Http var : order by attribute
 * @global gview Http var : [abstract|column] view mode
 */
function generic_list(&$action)
{
    // Set the globals elements
    // Get all the params
    $dirid = GetHttpVars("dirid"); // directory to see
    $catgid = GetHttpVars("catg", $dirid); // category
    $startpage = GetHttpVars("page", "0"); // page to see
    $tab = GetHttpVars("tab", "0"); // tab to see 1 for ABC, 2 for DEF, ...
    $onglet = GetHttpVars("onglet", 'N'); // if you want onglet
    $famid = GetHttpVars("famid"); // family restriction
    $clearkey = (GetHttpVars("clearkey", "N") == "Y"); // delete last user key search
    $sqlorder = GetHttpVars("sqlorder"); // family restriction
    $target = "finfo$famid";
    setHttpVar("target", $target);
    if (!($famid > 0)) $famid = getDefFam($action);
    
    $column = generic_viewmode($action, $famid); // choose the good view mode
    $dbaccess = $action->GetParam("FREEDOM_DB");
    $action->parent->addCssRef("GENERIC:generic_list.css", true);
    //change famid if it is a simplesearch
    $sfamid = $famid;
    if ($dirid) {
        $dir = new_doc($dbaccess, $dirid);
        if ($dir->isAlive() && ($dir->defDoctype == 'S')) {
            $sfamid = $dir->getValue("se_famid");
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
    if ($catg->doctype == 'D') $action->lay->set("folderid", $catg->id);
    $action->lay->Set("pds", "");
    if ($catgid) {
        $catg = new_Doc($dbaccess, $catgid);
        $action->lay->Set("pds", $catg->urlWhatEncodeSpec(""));
        
        $action->lay->Set("fldtitle", $dir->getHTMLTitle());
    } else {
        if ($dirid == 0) {
            $action->lay->Set("fldtitle", _("precise search"));
            $action->lay->Set("pds", "");
        } else {
            $action->lay->Set("fldtitle", $dir->getHTMLTitle());
        }
    }
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
        $sqlorder = getDefUSort($action, "title", $sfamid);
        setHttpVar("sqlorder", $sqlorder);
    }
    
    if ($famid > 0) {
        if ($sqlorder != "") {
            $ndoc = createDoc($dbaccess, $sfamid, false);
            if ($sqlorder[0] == "-") $sqlorder = substr($sqlorder, 1);
            if (!in_array($sqlorder, $ndoc->fields)) setHttpVar("sqlorder", "");
        }
    }
    if ($clearkey) {
        $action->parent->param->Set("GENE_LATESTTXTSEARCH", setUkey($action, $famid, $keyword) , PARAM_USER . $action->user->id, $action->parent->id);
    }
    getFamilySearches($action, $dbaccess, $famid);
    if ($dirid) {
        if ($dir->fromid == 38) {
            $famid = 0; // special researches
            setHttpVar("sqlorder", "--"); // no sort possible
            
        }
        $only = (getInherit($action, $famid) == "N");
        if (viewfolder($action, true, false, $column, $slice, array() , ($only) ? -(abs($famid)) : abs($famid)) == $slice) {
            // can see next
            $action->lay->Set("nexticon", $action->GetIcon("next.png", N_("next") , 16));
        }
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
    
    $action->lay->Set("onglet", $wonglet ? "Y" : "N");
    
    $action->lay->set("tkey", str_replace('"', '&quot;', getDefUKey($action)));
}

function generic_viewmode(&$action, $famid)
{
    $prefview = getHttpVars("gview");
    
    $tmode = explode(",", $action->getParam("GENE_VIEWMODE"));
    // explode parameters
    while (list($k, $v) = each($tmode)) {
        list($fid, $vmode) = explode("|", $v);
        $tview[$fid] = $vmode;
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
            $action->parent->param->Set("GENE_VIEWMODE", $pmode, PARAM_USER . $action->user->id, $action->parent->id);
            
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

function getFamilySearches($action, $dbaccess, $famid)
{
    // search searches in primary folder
    $fdoc = new_Doc($dbaccess, $famid);
    $dirid = GetHttpVars("dirid"); // search
    $catgid = GetHttpVars("catg", $dirid); // primary directory
    if ($catgid == 0) $catgid = $dirid;
    if ($fdoc->dfldid > 0) {
        $homefld = new_Doc($dbaccess, $fdoc->dfldid);
        $stree = array();
        if ($homefld->id > 0) $stree = getChildDoc($dbaccess, $homefld->id, "0", "ALL", array() , $action->user->id, "TABLE", 5);
        
        $streeSearch = array();
        foreach ($stree as $k => $v) {
            if (($v["doctype"] == "S") && ($v["fromid"] != $fdoc->id)) {
                $streeSearch[$v["id"]] = $v;
                $streeSearch[$v["id"]]["selected"] = ($v["id"] == $catgid) ? "selected" : "";
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
        }
    }
    
    $action->lay->set("ONESEARCH", (count($streeSearch) > 0));
    
    $action->lay->SetBlockData("SYSSEARCH", $streeSearch);
    // search user searches for family
    $filter[] = "owner=" . $action->user->id;
    $filter[] = "se_famid='$famid'";
    $filter[] = "usefor!='G'";
    $filter[] = "doctype != 'T'";
    $filter[] = "se_memo='yes'";
    $action->lay->set("MSEARCH", false);
    $stree = getChildDoc($dbaccess, "0", "0", "ALL", $filter, $action->user->id, "TABLE", 5);
    $streeSearch = array();
    foreach ($stree as $k => $v) {
        if (!isset($streeSearch[$v["id"]])) $streeSearch[$v["id"]] = $v;
        $streeSearch[$v["id"]]["selected"] = ($v["id"] == $catgid) ? "selected" : "";
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
    $action->lay->set("MSEARCH", (count($stree) > 0));
    $action->lay->SetBlockData("USERSEARCH", $streeSearch);
    if (count($streeSearch) > 0) $action->lay->set("ONESEARCH", true);
}
?>
