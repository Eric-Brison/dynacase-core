<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * RSS syndication on a folder (search, folders, report....)
 *
 * @author Anakeen
 * @version $Id: freedom_rss.php,v 1.11 2009/01/14 13:09:53 jerome Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/freedom_util.php");
include_once ("FDL/Lib.Dir.php");

function freedom_rss(Action & $action)
{
    
    $id = GetHttpVars("id", 0);
    $dhtml = (GetHttpVars("dh", 1) == 1 ? true : false);
    $action->lay->set("html", $dhtml);
    $lim = GetHttpVars("lim", 100);
    $order = GetHttpVars("order");
    
    $dbaccess = $action->dbaccess;
    
    header('Content-type: text/xml; charset=utf-8');
    
    $baseurl = $action->getparam("CORE_EXTERNURL");
    
    if (!$action->auth) $addauth = '&authtype=basic';
    else $addauth = '';
    
    $standurl = __xmlentities($action->GetParam("CORE_STANDURL"));
    $action->lay->set("standurl", $standurl);
    $action->lay->set("server", substr($baseurl, 0, strrpos($baseurl, '/')));
    
    $cssf = getparam("CORE_STANDURL") . "$addauth&app=CORE&action=CORE_CSS&session=" . $action->session->id . "&layout=FDL:RSS.CSS";
    $action->lay->set("rsscss", $cssf);
    
    setlocale(LC_TIME, "C");
    $rsslink = $baseurl . __xmlentities("?sole=Y$addauth&app=FDL&action=FDL_CARD&latest=Y&id=" . $id);
    $action->lay->set("rsslink", $rsslink);
    $action->lay->set("copy", "Copyright 2006 Anakeen");
    $action->lay->set("lang", substr(getParam("CORE_LANG") , 0, 2));
    $action->lay->set("datepub", strftime("%a, %d %b %Y %H:%M:%S %z", time()));
    $action->lay->set("ttl", 60);
    $action->lay->set("category", "Dynacase documents");
    $action->lay->set("generator", "Dynacase version " . $action->parent->getParam("VERSION"));
    
    $doc = new_Doc($dbaccess, $id);
    $action->lay->set("lastbuild", strftime("%a, %d %b %Y %H:%M:%S %z", intval($doc->revdate)));
    // Check right for doc access
    if ($doc->defDoctype == 'S') $aclctrl = "execute";
    else $aclctrl = "open";
    if (($err = $doc->Control($aclctrl)) != "") {
        $action->log->error($err);
        return;
    }
    $report = false;
    $tcolshown = array();
    $items = array();
    $action->lay->set("icon", $doc->getIcon());
    if ($doc->doctype != 'S' && $doc->doctype != 'D') {
        
        $ldoc = array(
            getTDoc($dbaccess, $id)
        );
    } else {
        
        $filter = array();
        $famid = "";
        $report = ($doc->fromid == getIdFromName($dbaccess, "REPORT") ? true : false);
        if (!$order) {
            if ($doc->getRawValue("REP_IDSORT")) {
                $order = $doc->getRawValue("REP_IDSORT");
                $order.= " " . $doc->getRawValue("REP_ORDERSORT");
            } else $order = "revdate desc";
        }
        $ldoc = internalGetDocCollection($dbaccess, $doc->id, 0, $lim, $filter, $action->user->id, "TABLE", $famid, false, $order);
    }
    $lattr = array();
    if ($report) {
        /**
         * @var \Dcp\Family\REPORT $tmpdoc
         */
        $tmpdoc = createDoc($dbaccess, getIdFromName($dbaccess, "REPORT") , false);
        $fdoc = createDoc($dbaccess, $doc->getRawValue("SE_FAMID") , false);
        $lattr = $fdoc->GetNormalAttributes();
        $tcol1 = array();
        foreach ($lattr as $k => $v) {
            $tcol1[$v->id] = array(
                "colid" => $v->id,
                "collabel" => $v->getLabel() ,
                "rightfornumber" => ($v->type == "money") ? "right" : "left"
            );
        }
        $tinternals = $tmpdoc->_getInternals();
        foreach ($tinternals as $k => $v) {
            $tcol1[$k] = array(
                "colid" => $k,
                "collabel" => $v,
                "rightfornumber" => "left"
            );
        }
        
        $tcols = $doc->getMultipleRawValues("REP_IDCOLS");
        foreach ($tcols as $val) {
            $tcolshown[$val] = $tcol1[$val];
        }
    }
    // $action->lay->set("rssname", $doc->getTitle()."  -".count($ldoc)."-");
    $action->lay->set("rssname", __xmlentities($doc->getTitle()));
    
    $lines = array();
    foreach ($ldoc as $kdoc => $vdoc) {
        $zdoc = getDocObject($dbaccess, $vdoc);
        $descr = '';
        
        $items[$zdoc->id] = array(
            "title" => "",
            "link" => $baseurl . __xmlentities("?sole=Y$addauth&app=FDL&action=FDL_CARD&id=" . $zdoc->id) ,
            "descr" => "",
            "revdate" => strftime("%a, %d %b %Y %H:%M:%S %z", intval($zdoc->revdate)) ,
            "id" => $zdoc->id,
            "category" => $zdoc->fromname,
            "author" => __xmlentities(getMailAddr($zdoc->owner, true)) ,
            "rssname" => __xmlentities($doc->getTitle()) ,
            "rsslink" => $rsslink,
            "report" => $report,
        );
        if ($report) {
            $lines = array();
            $i = 0;
            foreach ($tcolshown as $kc => $vc) {
                if ($zdoc->getRawValue($kc) == "") $lines[] = array(
                    "attr" => $vc["collabel"],
                    "val" => ""
                );
                else {
                    switch ($kc) {
                        case "revdate":
                            $cval = strftime("%x %T", $vdoc[$kc]);
                            break;

                        case "state":
                            $cval = _($vdoc[$kc]);
                            break;

                        case "title":
                            $cval = $zdoc->getTitle();
                            break;

                        default:
                            if (isset($lattr[$kc])) {
                                $cval = $zdoc->getHtmlValue($lattr[$kc], $zdoc->getRawValue($kc) , "", false);
                                if ($lattr[$kc]->type == "image") $cval = "<img width=\"30px\" src=\"$cval\">";
                            } else $cval = $zdoc->getPropertyValue($kc);
                        }
                        if ($i == 0) {
                            $items[$zdoc->id]["title"] = __xmlentities(html_entity_decode($cval, ENT_NOQUOTES, 'UTF-8'));
                            $i++;
                        } else {
                            $cval = __xmlentities($cval);
                            $lines[] = array(
                                "attr" => $vc["collabel"],
                                "val" => ($cval)
                            );
                        }
                    }
            }
            $action->lay->setBlockData("lines" . $zdoc->id, $lines);
        } else {
            $items[$zdoc->id]["descr"] = ($dhtml ? __xmlentities(($zdoc->viewdoc("FDL:VIEWTHUMBCARD"))) : "...");
            $items[$zdoc->id]["title"] = __xmlentities($zdoc->getTitle());
        }
    }
    $action->lay->setBlockData("Items", $items);
    $action->lay->set("report", $report);
}

function __xmlentities($string)
{
    return Doc::htmlEncode($string);
}
