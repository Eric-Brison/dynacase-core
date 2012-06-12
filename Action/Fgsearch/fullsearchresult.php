<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Full Text Search document
 *
 * @author Anakeen 2007
 * @version $Id: fullsearch.php,v 1.10 2008/01/04 17:56:37 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage GED
 */
/**
 */

include_once ("FDL/Class.SearchDoc.php");
include_once ("FDL/Class.DocSearch.php");

include_once ("FDL/freedom_util.php");
/**
 * Fulltext Search document
 * @param Action &$action current action
 * @global string $keyword Http var : word to search in any values
 * @global string $famid Http var : restrict to this family identioficator
 * @global string $start Http var : page number
 * @global string $dirid Http var : search identificator
 */
function fullsearchresult(Action & $action)
{
    
    $famid = GetHttpVars("famid", 0);
    $keyword = GetHttpVars("_se_key", GetHttpVars("keyword")); // keyword to search
    $target = GetHttpVars("target"); // target window when click on document
    $page = GetHttpVars("page", 0); // page number
    $dirid = GetHttpVars("dirid", 0); // special search
    $slice = 10;
    $start = $page * $slice;
    
    $action->lay->set("isdetail", false);
    $action->lay->set("page", $page + 1);
    $action->lay->set("dirid", $dirid);
    $action->lay->set("SUBSEARCH", ($start > 0));
    $initpage = false;
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/resizeimg.js");
    $orderby = "title";
    $dbaccess = $action->GetParam("FREEDOM_DB");
    if (!is_numeric($famid)) $famid = getFamIdFromName($dbaccess, $famid);
    $famtitle = "";
    $globalCount = 0;
    $nosearch = false;
    if (($keyword == "") && ($dirid == 0) && ($famid == 0)) {
        if ($initpage) {
            $action->lay = new Layout(getLayoutFile("FGSEARCH", "fullsearch_empty.xml") , $action);
            return;
        }
        $nosearch = true;
    }
    $action->lay->set("INITSEARCH", $nosearch);
    $kfams = array();
    $fkeyword = $keyword;
    if ($keyword != "") {
        // process family specification
        $kl = explode(":", $keyword);
        if (count($kl) > 1) {
            $keyword = $kl[1];
            $faml = $kl[0];
            $tf = explode(",", $faml);
            foreach ($tf as $k => $v) {
                if ($v == "") continue;
                $v = trim($v);
                if ($v[0] != "~") {
                    $b = true;
                    $n = $v;
                } else {
                    $b = false;
                    $n = substr($v, 1);
                }
                $kfams[] = array(
                    "include" => $b,
                    "kfam" => $n
                );
            }
        }
    }
    /* $bfam = array(); */
    $tclassdoc = getNonSystemFamilies($dbaccess, $action->user->id, "TABLE");
    if (!$nosearch) {
        
        $sqlfilters = array();
        $famfilter = $or = $and = "";
        if (count($kfams) > 0) {
            $famid = 0;
            $tmpdoc = new Doc($dbaccess);
            foreach ($kfams as $k => $v) {
                foreach ($tclassdoc as $kdoc => $cdoc) {
                    if (strstr(strtolower($cdoc["title"]) , $v["kfam"]) != false) {
                        if ($v["include"]) $or.= ($or != "" ? " OR " : "") . "(fromid" . ($v["include"] ? "=" : "!=") . $cdoc["initid"] . ")";
                        else $and.= ($and != "" ? " AND " : "") . "(fromid" . ($v["include"] ? "=" : "!=") . $cdoc["initid"] . ")";
                    }
                }
            }
            if ($or != "") $famfilter = "($or)";
            if ($and != "") $famfilter.= ($famfilter != "" ? " AND " : "") . " ($and)";
        }
        $keys = '';
        
        $s = new SearchDoc($dbaccess, $famid);
        $s->addFilter("usefor !~ '^S'");
        $s->setObjectReturn();
        if ($keyword != "") {
            $s->addGeneralFilter($keyword, true);
            $s->setPertinenceOrder();
        } else {
            $sdoc = new_doc($dbaccess, $dirid);
            $tkeys = $sdoc->getTValue("se_keys");
            foreach ($tkeys as $k => $v) if (!$v) unset($tkeys[$k]);
            $keys = implode('|', $tkeys);
        }
        if ($famfilter != "") $sqlfilters[] = $famfilter;
        
        if ($dirid) {
            $s->useCollection($dirid);
            $vardids = "did_$dirid";
        } else {
            $vardids = "did_$famid$keys";
            foreach ($sqlfilters as $filter) {
                $s->addFilter($filter);
            }
        }
        $displayedIds = array();
        if ($start > 0) {
            $displayedIds = $action->read($vardids);
            if ($displayedIds && count($displayedIds) > 0) {
                $sqlExclude = sprintf("initid not in (%s)", implode(",", $displayedIds));
                $s->addFilter($sqlExclude);
            } else {
                $s->setStart($start);
            }
        }
        $s->setSlice($slice + 1);
        $s->excludeConfidential();
        
        $s->search();
        if ($s->getError()) addLogMsg($s->getSearchInfo());
        //print_r2($s->getSearchInfo());
        if ($start == 0) {
            if ($s->count() < ($slice + 1)) $globalCount = $s->count();
            else {
                $sc = new SearchDoc($dbaccess, $famid);
                if ($keyword) $sc->addGeneralFilter($keyword, true);
                if ($dirid) {
                    $sc->useCollection($dirid);
                } else {
                    foreach ($sqlfilters as $filter) $sc->addFilter($filter);
                }
                $sc->excludeConfidential();
                $globalCount = $sc->onlyCount();
            }
        }
        
        $workdoc = new Doc($dbaccess);
        if ($famid) $famtitle = $workdoc->getTitle($famid);
        
        $dbid = getDbid($dbaccess);
        if ($s->count() == ($slice + 1)) {
            $action->lay->set("notthenend", true);
            
            $notTheEnd = true;
        } else {
            $action->lay->set("notthenend", false);
            $notTheEnd = false;
        }
        
        $action->lay->set("notfirst", ($start != 0));
        $action->lay->set("theFollowingText", _("View next results"));
        $c = 0;
        
        $k = 0;
        $tdocs = array();
        while ($doc = $s->nextDoc()) {
            
            if ($doc->confidential) {
                if (($doc->profid > 0) && ($workdoc->controlId($doc->profid, "confidential") != "")) {
                    continue;
                }
            }
            $displayedIds[] = $doc->initid;
            $c++;
            $tdocs[$k]["number"] = $c + $start;
            
            $tdocs[$k]["title"] = $doc->getHTMLTitle();
            $tdocs[$k]["id"] = $doc->id;
            $tdocs[$k]["htext"] = str_replace('[', '&#1B;', nl2br($s->getHighLightText($doc, '<strong>', '</strong>', $action->GetParam("FULLTEXT_HIGHTLIGHTSIZE", 200))));
            $tdocs[$k]["iconsrc"] = $doc->getIcon('', 20);
            $tdocs[$k]["mdate"] = strftime("%a %d %b %Y", $doc->revdate);
            $k++;
        }
        
        if ($notTheEnd) {
            array_pop($tdocs);
            array_pop($displayedIds);
        }
        $action->register($vardids, $displayedIds);
        $tpages = array();
        if ($start > 0) {
            for ($i = 0; $i < $start; $i+= $slice) {
                $tpages[] = array(
                    "xpage" => $i / $slice + 1,
                    "xstart" => $i
                );
            }
            
            $action->lay->setBlockData("PAGES", $tpages);
        }
        
        $action->lay->setBlockData("DOCS", $tdocs);
        
        $action->lay->set("dirid", $dirid);
        if ($dirid != 0) {
            $sdoc = new_doc($dbaccess, $dirid);
            if ($sdoc->isAffected()) {
                $action->lay->set("isdetail", false);
                $action->lay->set("searchtitle", $sdoc->title);
                $action->lay->set("dirid", $sdoc->id);
            }
        }
    } else {
        $action->lay->set("cpage", "0");
        $action->lay->set("notfirst", false);
        $action->lay->set("notthenend", false);
    }
    $action->lay->set("famid", $famid);
    $action->lay->set("searchtitle", sprintf(_("Search %s") , $keyword));
    if ($fkeyword == "") $action->lay->set("key", _("search dynacase documents"));
    else $action->lay->set("key", str_replace("\"", "&quot;", $fkeyword));
    
    $famsuffix = ($famid == 0 ? "" : sprintf("<span class=\"families\">(%s %s)</span>", _("family search result") , $famtitle));
    if ($globalCount == 0) {
        $action->lay->set("resulttext", sprintf(_("No document found for <strong>%s</strong>%s") , $keyword, $famsuffix));
    } else if ($globalCount == 1) {
        $action->lay->set("resulttext", sprintf(_("One document for <strong>%s</strong>%s") , $keyword, $famsuffix));
    } else {
        $action->lay->set("resulttext", sprintf(_("Found <strong>%d</strong>  Result for <strong>%s</strong>%s") , $globalCount, $keyword, $famsuffix));
    }
    $action->lay->set("displayBottomBar", ($globalCount == 0 ? false : true));
    $action->lay->set("displayTopBar", ($page == 0));
    
    $action->lay->set("searchdate", Doc::getDate(0, 0, 0, 0, true));
}

function strtr8($s, $c1, $c2)
{
    $s9 = utf8_decode($s);
    $s9 = strtr($s9, utf8_decode($c1) , utf8_decode($c2));
    return utf8_encode($s9);
}
?>
