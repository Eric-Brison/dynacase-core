<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * RSS syndication on a folder (search, folders, report....)
 *
 * @author Anakeen 2003
 * @version $Id: freedom_rss.php,v 1.11 2009/01/14 13:09:53 jerome Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/freedom_util.php");
include_once ("FDL/Lib.Dir.php");

function freedom_rss(&$action)
{
    
    $id = GetHttpVars("id", 0);
    $dhtml = (GetHttpVars("dh", 1) == 1 ? true : false);
    $action->lay->set("html", $dhtml);
    $lim = GetHttpVars("lim", 100);
    $order = GetHttpVars("order");
    
    $dbaccess = $action->GetParam("FREEDOM_DB");
    
    header('Content-type: text/xml; charset=utf-8');
    $action->lay->setEncoding("utf-8");
    
    $baseurl = $action->getparam("CORE_EXTERNURL");
    
    if (!$action->auth) $addauth = '&authtype=basic';
    else $addauth = '';
    
    $standurl = __xmlentities($action->GetParam("CORE_STANDURL"));
    $action->lay->set("standurl", $standurl);
    $action->lay->set("server", substr($baseurl, 0, strrpos($baseurl, '/')));
    
    $cssf = getparam("CORE_STANDURL") . "$addauth&app=CORE&action=CORE_CSS&session=" . $action->session->id . "&layout=FDL:RSS.CSS";
    $action->lay->set("rsscss", $cssf);
    
    $rsslink = $baseurl . __xmlentities("?sole=Y$addauth&app=FDL&action=FDL_CARD&latest=Y&id=" . $id);
    $action->lay->set("rsslink", $rsslink);
    $action->lay->set("copy", "Copyright 2006 Anakeen");
    $action->lay->set("lang", substr(getParam("CORE_LANG") , 0, 2));
    $action->lay->set("datepub", strftime("%a, %d %b %Y %H:%M:%S %z", time()));
    $action->lay->set("ttl", 60);
    $action->lay->set("category", "Freedom documents");
    $action->lay->set("generator", "Freedom version " . $action->parent->getParam("VERSION"));
    
    $doc = new_Doc($dbaccess, $id);
    $action->lay->set("lastbuild", strftime("%a, %d %b %Y %H:%M:%S %z", $doc->revdate));
    // Check right for doc access
    if ($doc->defDoctype == 'S') $aclctrl = "execute";
    else $aclctrl = "open";
    if (($err = $doc->Control($aclctrl)) != "") {
        $action->log->error($err);
        return;
    }
    $action->lay->set("icon", $doc->getIcon());
    if ($doc->doctype != 'S' && $doc->doctype != 'D') {
        
        $ldoc = array(
            getTDoc($dbaccess, $id)
        );
    } else {
        
        $filter = array();
        $famid = "";
        $report = ($doc->fromid == getIdFromName($dbaccess, "REPORT") ? true : false);
        $items = array();
        if (!$order) {
            if ($doc->getValue("REP_IDSORT")) {
                $order = $doc->getValue("REP_IDSORT");
                $order.= " " . $doc->getValue("REP_ORDERSORT");
            } else $order = "revdate desc";
        }
        $ldoc = getChildDoc($dbaccess, $doc->id, 0, $lim, $filter, $action->user->id, "TABLE", $famid, false, $order);
    }
    if ($report) {
        $tmpdoc = createDoc($dbaccess, getIdFromName($dbaccess, "REPORT") , false);
        $fdoc = createDoc($dbaccess, $doc->getValue("SE_FAMID") , false);
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
        $tcolshown = array();
        $tcols = $doc->getTValue("REP_IDCOLS");
        foreach ($tcols as $k => $v) {
            $tcolshown[$v] = $tcol1[$v];
        }
    }
    // $action->lay->set("rssname", $doc->getTitle()."  -".count($ldoc)."-");
    $action->lay->set("rssname", $doc->getTitle());
    
    $lines = array();
    setlocale(LC_TIME, "C");
    foreach ($ldoc as $kdoc => $vdoc) {
        $zdoc = getDocObject($dbaccess, $vdoc);
        $descr = '';
        
        $items[$zdoc->id] = array(
            "title" => "",
            "link" => $baseurl . __xmlentities("?sole=Y$addauth&app=FDL&action=FDL_CARD&id=" . $zdoc->id) ,
            "descr" => "",
            "revdate" => strftime("%a, %d %b %Y %H:%M:%S %z", $zdoc->revdate) ,
            "id" => $zdoc->id,
            "category" => getFamTitle($zdoc->fromid) ,
            "author" => getMailAddr($zdoc->owner, true) ,
            "rssname" => $doc->getTitle,
            "rsslink" => $rsslink,
            "report" => $report,
        );
        if ($report) {
            $lines = array();
            $i = 0;
            foreach ($tcolshown as $kc => $vc) {
                if ($zdoc->getValue($kc) == "") $lines[] = array(
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

                        default:
                            $cval = $zdoc->getHtmlValue($lattr[$kc], $zdoc->getValue($kc) , "", false);
                            if ($lattr[$kc]->type == "image") $cval = "<img width=\"30px\" src=\"$cval\">";
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
    return preg_replace(array(
        '/&/',
        '/"/',
        "/'/",
        '/</',
        '/>/'
    ) , array(
        '&amp;',
        '&quot;',
        '&apos;',
        '&lt;',
        '&gt;',
        '&apos;'
    ) , $string);
}
?>