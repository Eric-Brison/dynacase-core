<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Full Text Search document
 *
 * @author Anakeen
 * @version $Id: fullsearch.php,v 1.10 2008/01/04 17:56:37 eric Exp $
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
 * @global string $dirid Http var : search identifier
 */
function fullsearchresult(Action & $action)
{
    $famid = $action->getArgument("famid", 0);
    $keyword = $action->getArgument("_se_key", $action->getArgument("keyword")); // keyword to search
    $page = $action->getArgument("page", 0); // page number
    $dirid = $action->getArgument("dirid", 0); // special search
    $slice = 10;
    $start = $page * $slice;
    
    $action->lay->rSet("isdetail", false);
    $action->lay->rSet("page", (int)($page + 1));
    $action->lay->rSet("dirid", json_encode($dirid));
    $action->lay->rSet("SUBSEARCH", ($start > 0));
    $initpage = false;
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/resizeimg.js");
    
    $dbaccess = $action->dbaccess;
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
    $action->lay->rSet("INITSEARCH", $nosearch);
    $kfams = array();
    
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
    
    $searchMode = $action->getParam("FGSEARCH_SEARCHMODE", "words");
    /* $bfam = array(); */
    $tclassdoc = getNonSystemFamilies($dbaccess, $action->user->id, "TABLE");
    if (!$nosearch) {
        
        $sqlfilters = array();
        $famfilter = $or = $and = "";
        if (count($kfams) > 0) {
            $famid = 0;
            
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
        $keys = $vardids = $displayedIds = '';
        
        $s = new SearchDoc($dbaccess, $famid);
        
        try {
            $s->addFilter("usefor !~ '^S'");
            $s->setObjectReturn();
            if ($keyword != "") {
                
                $s->addGeneralFilter($keyword, true, $searchMode === "characters");
                $s->setPertinenceOrder();
            } else {
                $sdoc = new_doc($dbaccess, $dirid);
                $tkeys = $sdoc->getMultipleRawValues("se_keys");
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
            if ($s->getError()) {
                addLogMsg($s->getSearchInfo());
                $action->exitError($s->getError());
            }
        }
        catch(Exception $e) {
            $action->exitError(sprintf(_("Incorrect filter %s") , $keyword));
        }
        
        if ($start == 0) {
            if ($s->count() < ($slice + 1)) $globalCount = $s->count();
            else {
                $sc = new SearchDoc($dbaccess, $famid);
                if ($keyword) $sc->addGeneralFilter($keyword, true, $searchMode === "characters");
                if ($dirid) {
                    $sc->useCollection($dirid);
                } else {
                    foreach ($sqlfilters as $filter) $sc->addFilter($filter);
                }
                $sc->addFilter("usefor !~ '^S'");
                $sc->excludeConfidential();
                $globalCount = $sc->onlyCount();
            }
        }
        
        $workdoc = new Doc($dbaccess);
        if ($famid) $famtitle = $workdoc->getTitle($famid);
        
        if ($s->count() == ($slice + 1)) {
            $action->lay->rSet("notthenend", true);
            
            $notTheEnd = true;
        } else {
            $action->lay->rSet("notthenend", false);
            $notTheEnd = false;
        }
        
        $action->lay->rSet("notfirst", ($start != 0));
        $action->lay->eSet("theFollowingText", _("View next results"));
        $c = 0;
        
        $k = 0;
        $tdocs = array();
        while ($doc = $s->getNextDoc()) {
            
            if ($doc->confidential) {
                if (($doc->profid > 0) && ($workdoc->controlId($doc->profid, "confidential") != "")) {
                    continue;
                }
            }
            $displayedIds[] = $doc->initid;
            $c++;
            $tdocs[$k]["number"] = htmlspecialchars($c + $start, ENT_QUOTES);
            $tdocs[$k]["title"] = $doc->getHTMLTitle();
            $tdocs[$k]["id"] = $doc->id;
            /* Escape elements of highlight string */
            $highlight = $s->getHighLightText($doc, '<strong>', '</strong>', $action->GetParam("FULLTEXT_HIGHTLIGHTSIZE", 200) , $searchMode === "words");
            $tokens = preg_split('!(</?strong>)!', $highlight, -1, PREG_SPLIT_DELIM_CAPTURE);
            foreach ($tokens as & $token) {
                if ($token == '<strong>' || $token == '</strong>') {
                    continue;
                }
                $token = htmlspecialchars($token, ENT_QUOTES);
            }
            $tdocs[$k]["htext"] = str_replace('[', '&#1B;', nl2br(join('', $tokens)));
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
            
            $action->lay->eSetBlockData("PAGES", $tpages);
        }
        
        $action->lay->setBlockData("DOCS", $tdocs);
        
        $action->lay->eset("dirid", $dirid);
        if ($dirid != 0) {
            $sdoc = new_doc($dbaccess, $dirid);
            if ($sdoc->isAffected()) {
                $action->lay->rSet("isdetail", false);
                $action->lay->eSet("searchtitle", $sdoc->title);
                $action->lay->rSet("dirid", $sdoc->id);
            }
        }
    } else {
        $action->lay->rSet("cpage", "0");
        $action->lay->rSet("notfirst", false);
        $action->lay->rSet("notthenend", false);
    }
    
    $famsuffix = ($famid == 0 ? "" : sprintf("<span class=\"families\">(%s %s)</span>", _("family search result") , $famtitle));
    if ($globalCount == 0) {
        $action->lay->rSet("resulttext", sprintf(_("No document found for <strong>%s</strong>%s") , htmlspecialchars($keyword) , $famsuffix));
    } else if ($globalCount == 1) {
        $action->lay->rSet("resulttext", sprintf(_("One document for <strong>%s</strong>%s") , htmlspecialchars($keyword) , $famsuffix));
    } else {
        $action->lay->rSet("resulttext", sprintf(_("Found <strong>%d</strong>  Result for <strong>%s</strong>%s") , $globalCount, htmlspecialchars($keyword) , $famsuffix));
    }
    $action->lay->rSet("displayBottomBar", ($globalCount == 0 ? false : true));
    $action->lay->rSet("displayTopBar", ($page == 0));
    
    $action->lay->eSet("searchdate", Doc::getDate(0, 0, 0, true));
}

function strtr8($s, $c1, $c2)
{
    $s9 = utf8_decode($s);
    $s9 = strtr($s9, utf8_decode($c1) , utf8_decode($c2));
    return utf8_encode($s9);
}
