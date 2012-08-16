<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: search_fulltext.php,v 1.9 2007/10/15 13:01:06 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage GED
 */
/**
 */
// ---------------------------------------------------------------
// $Id: search_fulltext.php,v 1.9 2007/10/15 13:01:06 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/search_fulltext.php,v $
// ---------------------------------------------------------------
include_once ("FDL/Class.DocSearch.php");
include_once ("FDL/Class.Dir.php");
include_once ("FDL/Lib.Dir.php");
include_once ("FDL/Class.QueryDir.php");
include_once ("FDL/freedom_util.php");

function fileNameToId($name)
{
    $ifile = basename($name);
    $te = explode(".", $ifile);
    return $te[0];
}

function search_error(&$action, $msg)
{
    $action->log->error($msg);
    print "<h2>" . $msg . "</h2>";
    exit;
}
// -----------------------------------
function search_fulltext(&$action)
{
    // -----------------------------------
    global $tpt, $ipt;
    
    if (!extension_loaded('mnogosearch')) search_error($action, _("mnogosearch php extension not loaded"));
    
    $baseurl = $action->GetParam("CORE_BASEURL");
    $standurl = $action->GetParam("CORE_STANDURL");
    $dbaccess = $action->GetParam("FREEDOM_DB");
    // Get all the params
    $keyword = GetHttpVars("keyword"); // keyword to search
    $title = GetHttpVars("title", _("new search ") . $keyword); // title of the search
    $latest = GetHttpVars("latest", false); // only latest revision
    $s_match = GetHttpVars("matchid", 0); // matching ploicy
    $adminstat[0]["match"] = $s_match;
    $s_searchfor = GetHttpVars("searchforid", 0); // search policy
    $adminstat[0]["searchfor"] = $s_searchfor;
    $viewfile = GetHttpVars("viewfile", false); // display files
    $famid = GetHttpVars("famid"); // famid restrictive familly
    $action->lay->Set("classdoc", _(" any familly"));
    $tclassdoc = GetClassesDoc($dbaccess, $action->user->id, 0, "TABLE");
    while (list($k, $cdoc) = each($tclassdoc)) {
        if ($famid == $cdoc["initid"]) $action->lay->Set("classdoc", $cdoc["title"]);
    }
    
    $fromdir = GetHttpVars("fromdir", false); // the keyword is case sensitive
    $dirid = GetHttpVars("dirid");
    
    $refresh = GetHttpVars("refresh", "no"); // force folder refresh
    $startpage = GetHttpVars("page", "0"); // page number
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/subwindow.js");
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/geometry.js");
    
    $searchtitle = _("search for ") . " : " . $keyword . ", ";
    if ($fromdir) $searchtitle.= _(" from current folder");
    else $searchtitle.= _(" from any folder");
    $action->lay->Set("dirtitle", $searchtitle);
    $action->lay->Set("dirid", $dirid);
    
    $with_abstract = false;
    $start = 0;
    // ------------------------------------------------------
    // definition of popup menu
    $with_popup = true;
    include_once ("FDL/popup_util.php");
    popupInit("popuplist", array(
        'vprop',
        'editdoc',
        'cancel',
        'copy',
        'duplicate',
        'ifld',
        'delete'
    ));
    $slice = "1000";
    
    $s_db = $action->GetParam("MNOGOSEARCH_DB", "pgsql://anakeen@localhost/mnoGoSearch/");
    $s_dbmode = $action->GetParam("MNOGOSEARCH_DBMODE", "crc");
    
    $search_limit = $action->GetParam("MNOGOSEARCH_SEARCHLIMIT", "%/freedom/fs/%");
    
    $udm_param = array();
    // Get first page only
    $udm_param[UDM_PARAM_PAGE_NUM] = 0;
    $udm_param[UDM_PARAM_TRACK_MODE] = UDM_TRACK_DISABLED;
    $udm_param[UDM_PARAM_PAGE_SIZE] = $action->GetParam("MNOGOSEARCH_RESULTBYPAGE", 1000);
    $udm_param[UDM_PARAM_CACHE_MODE] = $action->GetParam("MNOGOSEARCH_CACHE", UDM_CACHE_ENABLED);
    $udm_param[UDM_PARAM_PHRASE_MODE] = $action->GetParam("MNOGOSEARCH_PHRASE", UDM_PHRASE_ENABLED);
    $udm_param[UDM_PARAM_CHARSET] = $action->GetParam("MNOGOSEARCH_CHARSET", "8859-1");
    $udm_param[UDM_PARAM_STOPTABLE] = $action->GetParam("MNOGOSEARCH_STOPTABLE", "stopword");
    
    $udm_param[UDM_PARAM_SEARCH_MODE] = $s_match;
    
    $udm_param[UDM_PARAM_WORD_MATCH] = $s_searchfor;
    
    $search = udm_alloc_agent($s_db, $s_dbmode);
    if (!$search) search_error($action, _("can't connect mnogosearch db"));
    
    while (list($k, $v) = each($udm_param)) {
        if (!Udm_Set_Agent_Param($search, $k, $v)) {
            $action->log->warn("Udm_Set_Agent_Param {$k} = {$v} returns an error");
        }
    }
    
    Udm_Add_Search_Limit($search, UDM_LIMIT_URL, $search_limit);
    $res = Udm_Find($search, urldecode($keyword));
    if (Udm_Errno($search) > 0) {
        search_error($action, _("Udm_Find Error : ") . Udm_Error($search));
    }
    
    $action->lay->Set("stime", Udm_Get_Res_Param($res, UDM_PARAM_SEARCHTIME));
    $action->lay->Set("totalcnt", udm_get_doc_count($search));
    $adminstat[0]["infos"] = Udm_Get_Res_Param($res, UDM_PARAM_WORDINFO);
    
    $found = Udm_Get_Res_Param($res, UDM_PARAM_FOUND);
    $adminstat[0]["ffound"] = $found;
    $adminfile = array();
    $doclist = array();
    $idoc = 0;
    if ($found > 0) {
        $rows = Udm_Get_Res_Param($res, UDM_PARAM_NUM_ROWS);
        $adminstat[0]["fviewed"] = $rows;
        for ($i = 0; $i < $rows; $i++) {
            $resurl = Udm_Get_Res_Field($res, $i, UDM_FIELD_URL);
            $adminfile[$i]["ix"] = "";
            $adminfile[$i]["filename"] = $resurl;
            $idf = fileNameToId($resurl);
            if ($idf > 0) {
                // Querying db ...
                $filter = array();
                $filter[] = "values like '%|$idf'";
                
                if ($famid == 0) $famid = "";
                $cdirid = 0;
                if ($fromdir) {
                    $cdirid = getRChildDirId($dbaccess, $dirid);
                }
                $rq = getChildDoc($dbaccess, $cdirid, 0, 100, $filter, $action->user->id, "TABLE", $famid);
                
                if (is_array($rq) && count($rq) > 0) {
                    while (count($rq) > 0 && list($kv, $vd) = each($rq)) {
                        $adminfile[$i]["ix"].= "[" . $vd["id"] . "]";
                        if (!isset($doclist[$vd["id"]])) $doclist[$vd["id"]]["fcnt"] = 0;
                        $x = $doclist[$vd["id"]]["fcnt"];
                        $doclist[$vd["id"]]["fcnt"]++;
                        //	    $doclist[$vd["id"]][$x]["attrid"] = $vd["attrid"];
                        $doclist[$vd["id"]][$x]["file"] = $resurl;
                        $doclist[$vd["id"]][$x]["idv"] = $idf;
                        $doclist[$vd["id"]][$x]["size"] = Udm_Get_Res_Field($res, $i, UDM_FIELD_SIZE);
                        $doclist[$vd["id"]][$x]["rate"] = Udm_Get_Res_Field($res, $i, UDM_FIELD_RATING);
                        $doclist[$vd["id"]][$x]["modi"] = Udm_Get_Res_Field($res, $i, UDM_FIELD_MODIFIED);
                    }
                }
                unset($query);
            }
        }
    }
    Udm_Free_Res($res);
    Udm_Free_Agent($search);
    
    $kdiv = 1;
    $tdoc = array();
    $nbseedoc = $nbdoc = 0;
    reset($doclist);
    while (list($kd, $vd) = each($doclist)) {
        $doc = new_Doc($dbaccess, $kd);
        $nbseedoc++;
        $k = $nbdoc;
        $nbdoc++; // one more visible doc
        $docid = $doc->id;
        
        $tdoc[$k]["id"] = $docid;
        if ($with_abstract) $tdoc[$k]["blockabstract"] = "abstract_$k";
        
        $tdoc[$k]["title"] = $doc->title;
        if (strlen($doc->title) > 20) $tdoc[$k]["abrvtitle"] = substr($doc->title, 0, 12) . "... " . substr($doc->title, -5);
        else $tdoc[$k]["abrvtitle"] = $doc->title;
        $tdoc[$k]["profid"] = $doc->profid;
        $tdoc[$k]["iconsrc"] = $doc->geticon();
        $tdoc[$k]["divid"] = $kdiv;
        
        $tdoc[$k]["locked"] = "";
        if ($doc->isRevisable()) {
            if (($doc->locked > 0) && ($doc->locked == $action->parent->user->id)) $tdoc[$k]["locked"] = $action->GetIcon("clef1.gif", N_("locked") , 20, 20);
            else if ($doc->locked > 0) $tdoc[$k]["locked"] = $action->GetIcon("clef2.gif", N_("locked") , 20, 20);
            else if ($doc->locked < 0) $tdoc[$k]["locked"] = $action->GetIcon("nowrite.png", N_("fixed") , 20, 20);
            else if ($doc->lmodify == "Y") if ($doc->doctype == 'F') $tdoc[$k]["locked"] = $action->GetIcon("changed2.gif", N_("changed") , 20, 20);
        }
        
        if ($with_popup) {
            popupActive("popuplist", $kdiv, 'vprop');
            popupActive("popuplist", $kdiv, 'cancel');
            popupActive("popuplist", $kdiv, 'copy');
            popupActive("popuplist", $kdiv, 'ifld');
            popupInvisible("popuplist", $kdiv, 'editdoc');
            popupInvisible("popuplist", $kdiv, 'delete');
            popupInvisible("popuplist", $kdiv, 'duplicate');
        }
        
        $kdiv++;
        if ($doc->doctype == 'F') $tdoc[$k]["revision"] = $doc->revision;
        else $tdoc[$k]["revision"] = "";
        $tdoc[$k]["state"] = $action->Text($doc->state);
        
        if ($doc->classname == 'Dir') $tdoc[$k]["isfld"] = "true";
        else $tdoc[$k]["isfld"] = "false";
        
        if ($with_abstract) {
            // search abstract for freedom item
            $query_val->basic_elem->sup_where = array(
                "(docid=$docid)",
                $sql_cond_abs
            );
            
            $tablevalue = $query_val->Query();
            // Set the table elements
            $tableabstract = array();
            $nbabs = 0; // nb abstract
            for ($i = 0; $i < $query_val->nb; $i++) {
                
                $lvalue = chop($tablevalue[$i]->value);
                
                if ($lvalue != "") {
                    $oattr = $doc->GetAttribute($tablevalue[$i]->attrid);
                    
                    $tdoc[$k][$tablevalue[$i]->attrid] = $lvalue;
                    
                    $tableabstract[$nbabs]["name"] = $action->text($oattr->labeltext);
                    $tableabstract[$nbabs]["valid"] = $tablevalue[$i]->attrid;
                    switch ($oattr->type) {
                        case "image":
                            
                            $efile = $doc->GetHtmlValue($oattr, $lvalue, "finfo");
                            
                            $tableabstract[$nbabs]["value"] = "<IMG align=\"absbottom\" width=\"30\" SRC=\"" . $efile . "\">";
                            break;

                        default:
                            $tableabstract[$nbabs]["value"] = $doc->GetHtmlValue($oattr, $lvalue, "finfo");
                            break;
                    }
                    $nbabs++;
                }
            }
            $action->lay->SetBlockData("abstract_$k", $tableabstract);
            
            unset($tableabstract);
        }
        
        if ($viewfile) {
            $rlay = $action->GetLayoutFile("search_fulltext_result.xml");
            $rlay = new Layout($action->GetLayoutFile("search_fulltext_result.xml") , $action);
            $filed = array();
            for ($if = 0; $if < $vd["fcnt"]; $if++) {
                $value = chop($doc->GetValue($vd[$if]["attrid"]));
                $filed[$if]["imgsrc"] = $doc->GetHtmlValue($doc->GetAttribute($vd[$if]["attrid"]) , $value, "_self", "Y");
                $filed[$if]["rating"] = $vd[$if]["rate"];
                $filed[$if]["date"] = strftime("%d/%m/%Y %H:%M", $vd[$if]["modi"]);
            }
            $rlay->SetBlockData("ALLRESULT", $filed);
            $tdoc[$k]["SEARCHRESULT"] = $rlay->Gen();
            unset($filed);
            unset($rlay);
        } else {
            $tdoc[$k]["SEARCHRESULT"] = "";
        }
    }
    // Out
    //------------------------------
    // display popup action
    $tboo[0]["boo"] = "";
    $action->lay->SetBlockData("VIEWPROP", $tboo);
    $action->lay->Set("nbdiv", $kdiv - 1);
    $action->lay->SetBlockData("TABLEBODY", $tdoc);
    if ($with_popup) {
        popupGen($kdiv - 1);
        $licon = new Layout($action->Getparam("CORE_PUBDIR") . "/FDL/Layout/manageicon.js", $action);
        $licon->Set("nbdiv", $kdiv - 1);
        $action->parent->AddJsCode($licon->gen());
    }
    // when slicing
    $pagefolder[$startpage + 1] = $nbseedoc + $start;
    $action->Register("pagefolder", $pagefolder);
    $action->lay->Set("next", $startpage + 1);
    $action->lay->Set("prev", $startpage - 1);
    $action->lay->Set("nbdoc", $nbdoc);
    
    if ($action->user->id == 1) {
        $action->lay->SetBlockData("ADMIN", $adminstat);
        $action->lay->SetBlockData("ADMINFILES", $adminfile);
    }
    return $nbdoc;
}
?>
