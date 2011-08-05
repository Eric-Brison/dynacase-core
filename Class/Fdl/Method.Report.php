<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 * document to present a report on one family document
 *
 * @author Anakeen 2000
 * @version $Id: Method.Report.php,v 1.20 2008/11/20 09:34:20 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage GED
 */
/**
 */
/**
 * @begin-method-ignore
 * this part will be deleted when construct document class until end-method-ignore
 */
Class _REPORT extends _DSEARCH
{
    /*
     * @end-method-ignore
    */
    var $defaultedit = "FREEDOM:EDITREPORT";
    var $defaultview = "FREEDOM:VIEWREPORT";
    
    var $cviews = array(
        "FREEDOM:VIEWREPORT",
        "FREEDOM:VIEWMINIREPORT:T"
    );
    var $eviews = array(
        "FREEDOM:EDITREPORT"
    );
    function _getInternals()
    {
        return array(
            "title" => _("doctitle") ,
            "revdate" => _("revdate") ,
            "revision" => _("revision") ,
            "state" => _("state")
        );
    }
    function editreport()
    {
        global $action;
        $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/selectbox.js");
        $action->parent->AddJsRef($action->GetParam("CORE_PUBURL") . "/FREEDOM/Layout/editreport.js");
        
        $rfamid = GetHttpVars("sfamid", $this->getValue("SE_FAMID", 1));
        $rdoc = createDoc($this->dbaccess, $rfamid, false);
        $lattr = $rdoc->GetNormalAttributes();
        $tcolumn1 = array();
        $tcolumn2 = array();
        while (list($k, $v) = each($lattr)) {
            //    if ($v->visibility=="H") continue;
            $tcolumn1[$v->id] = array(
                "aid" => $v->id,
                "alabel" => $v->getLabel()
            );
        }
        $tinternals = $this->_getInternals();
        // reconstruct internals for layout
        reset($tinternals);
        while (list($k, $v) = each($tinternals)) {
            $tinternals[$k] = array(
                "aid" => $k,
                "alabel" => $v
            );
        }
        // display selected column
        $tcols = $this->getTValue("REP_IDCOLS");
        
        foreach ($tcols as $k => $v) {
            if (isset($tcolumn1[$v])) {
                $tcolumn2[$v] = $tcolumn1[$v];
                unset($tcolumn1[$v]);
            }
            if (isset($tinternals[$v])) {
                $tcolumn2[$v] = $tinternals[$v];
                unset($tinternals[$v]);
            }
        }
        
        $this->lay->setBlockData("COLUMN1", $tcolumn1);
        $this->lay->setBlockData("INTERNALS", $tinternals);
        
        $this->lay->setBlockData("COLUMN2", $tcolumn2);
    }
    function viewreport($target = "_self", $ulink = true, $abstract = false)
    {
        global $action;
        $this->viewattr($target, $ulink, $abstract);
        $this->viewprop($target, $ulink, $abstract);
        $action->parent->AddCssRef("FREEDOM:viewreport.css", true);
        $action->parent->AddJsRef($action->GetParam("CORE_PUBURL") . "/FREEDOM/Layout/sorttable.js");
        // --------------------------
        // display headers column
        $rfamid = $this->getValue("SE_FAMID", 1);
        $rdoc = createDoc($this->dbaccess, $rfamid, false);
        $lattr = $rdoc->GetNormalAttributes();
        $tcolumn1 = array();
        $tcolumn2 = array();
        while (list($k, $v) = each($lattr)) {
            //    if ($v->visibility=="H") continue;
            $tcolumn1[$v->id] = array(
                "colid" => $v->id,
                "collabel" => $v->getLabel() ,
                "rightfornumber" => ($v->type == "money") ? "right" : "left"
            );
        }
        
        $tinternals = $this->_getInternals();
        foreach ($tinternals as $k => $v) {
            $tcolumn1[$k] = array(
                "colid" => $k,
                "collabel" => $v,
                "rightfornumber" => "left"
            );
        }
        
        $tcols = $this->getTValue("REP_IDCOLS");
        foreach ($tcols as $k => & $vcol) {
            if ($vcol) {
                $tcolumn2[$vcol] = $tcolumn1[$vcol];
            }
        }
        $this->lay->setBlockData("COLS", $tcolumn2);
        $this->lay->set("HASCOLS", count($tcolumn2) > 0);
        include_once ("FDL/Lib.Dir.php");
        
        $this->lay->set("reportstyle", $this->getValue("REP_STYLE", "perso"));
        $this->lay->set("isperso", ($this->getValue("REP_STYLE", "perso") == "perso"));
        if ($this->isParameterizable() && $ulink) {
            $this->lay->setBlockData("PARAMS", array(
                array(
                    "zou"
                )
            ));
        }
        // --------------------------
        // display body
        $limit = $this->getValue("REP_LIMIT", "ALL");
        $order = $this->getValue("REP_IDSORT", "title");
        $oa = $rdoc->getAttribute($order);
        if ($oa) {
            if (($oa->type == "docid") && ($oa->getOption("doctitle") != "")) {
                $order = $oa->getOption("doctitle");
                if ($order == 'auto') $order = $oa->id . '_title';
            }
        }
        $order.= " " . $this->getValue("REP_ORDERSORT");
        $tdoc = getChildDoc($this->dbaccess, $this->initid, 0, $limit, array() , $this->userid, "TABLE", $rfamid, false, $order);
        $trodd = false;
        $tcolor = $this->getTValue("REP_COLORS");
        $trow = array();
        while (list($k, $v) = each($tdoc)) {
            $rdoc->Affect($v);
            $trow[$k] = array(
                "CELLS" => "row$k",
                "docid" => $rdoc->id,
                "troddoreven" => $trodd ? "trodd" : "treven"
            );
            $trodd = !$trodd;
            $tdodd = false;
            $tcell = array();
            reset($tcolumn2);
            reset($tcolor);
            
            foreach ($tcolumn2 as $kc => $vc) {
                if ($v[$kc] == "") $tcell[$kc] = array(
                    "cellval" => ""
                );
                else {
                    switch ($kc) {
                        case "revdate":
                            $cval = strftime("%d/%m/%Y %T", $v[$kc]);
                            //	  $cval = strftime ("%x %T",$v[$kc]);
                            break;

                        case "state":
                            $cval = _($v[$kc]);
                            break;

                        case "title":
                            if ($ulink) {
                                $trid = $v["id"];
                                $v[$kc] = $rdoc->getHTMLTitle();
                                $trlink = getparam("CORE_STANDURL") . "&app=FDL&action=FDL_CARD&id=$trid";
                                $cval = "<A target=\"rdoc" . $v["id"] . "\"  onmousedown=\"document.noselect=true;\" ";
                                $cval.= "onclick=\"subwindowm(200,600,'rdoc$trid','$trlink')\" oncontextmenu=\"popdoc(event,'$trlink');return false;\">";
                                $cval.= $v[$kc] . "</a>";
                            } else {
                                $cval = $v[$kc];
                            }
                            break;

                        default:
                            $cval = $rdoc->getHtmlValue($lattr[$kc], $v[$kc], $target, $ulink);
                            if ($lattr[$kc]->type == "image") $cval = "<img width=\"40px\" src=\"$cval\">";
                        }
                        $tcell[$kc] = array(
                            "cellval" => $cval,
                            "rawval" => $v[$kc]
                        );
                }
                $tcell[$kc]["bgcell"] = current($tcolor);
                next($tcolor);
                $tcell[$kc]["tdoddoreven"] = $tdodd ? "tdodd" : "tdeven";
                $tcell[$kc]["rightfornumber"] = ($lattr[$kc]->type == "money") ? "right" : "left";
                $tdodd = !$tdodd;
            }
            $this->lay->setBlockData("row$k", $tcell);
        }
        $this->lay->setBlockData("ROWS", $trow);
        // ---------------------
        // footer
        $tfoots = $this->getTValue("REP_FOOTS");
        
        foreach ($tfoots as $k => $v) {
            switch ($v) {
                case "CARD":
                    $val = count($trow);
                    break;

                case "MOY":
                case "SUM":
                    reset($trow);
                    $val = 0;
                    foreach ($trow as $kr => $vr) {
                        $ctr = $this->lay->getBlockData($vr["CELLS"]);
                        $val+= $ctr[$tcols[$k]]["rawval"];
                    }
                    if ($v == "MOY") $val = $val / count($trow);
                    $val = $rdoc->getHtmlValue($lattr[$tcols[$k]], $val, $target, $ulink);
                    break;

                default:
                    $val = "-";
                }
                $tlfoots[] = array(
                    "footval" => $val,
                    "rightfornumber" => $tcolumn2[$tcols[$k]]["rightfornumber"]
                );
        }
        $this->lay->setBlockData("TFOOT", $tlfoots);
        $this->lay->set("TITLE", $this->getHTMLTitle());
    }
    /**
     * view to export as csv
     */
    function viewreportcsv($target = "_self", $ulink = true, $abstract = false)
    {
        include_once ("FDL/Class.SearchDoc.php");
        $rfamid = $this->getValue("SE_FAMID", 1);
        $encoding = getHttpVars("encoding");
        
        $limit = $this->getValue("REP_LIMIT", "ALL");
        $order = $this->getValue("REP_IDSORT", "title");
        
        $s = new SearchDoc($this->dbaccess, $rfamid);
        $s->dirid = $this->initid;
        $s->slice = $limit;
        $s->orderby = $order;
        $s->setObjectReturn();
        $s->search();
        
        $rdoc = createDoc($this->dbaccess, $rfamid, false);
        $lattr = $rdoc->GetNormalAttributes();
        $tcols = $this->getTValue("REP_IDCOLS");
        
        $trow = array();
        // show head label attributes
        $tinternals = $this->_getInternals();
        
        $tcell = array();
        foreach ($tcols as $kc => $vc) {
            $oa = $rdoc->getAttribute($vc);
            $cval = ($oa) ? $oa->getLabel() : $tinternals[$vc];
            if (!$cval) $cval = $vc;
            $tcell[] = str_replace(array(
                ";",
                "\n",
                "\r"
            ) , array(
                " - ",
                "\\n",
                ""
            ) , $cval);
        }
        $trow[] = array(
            "row" => implode($tcell, ';')
        );
        while ($doc = $s->nextDoc()) {
            $tcell = array();
            
            foreach ($tcols as $kc => $vc) {
                //if ($v[$vc] == "") $cval="";
                if ($doc->getValue($vc) == "") $cval = "";
                else {
                    switch ($vc) {
                        case "revdate":
                            $cval = strftime("%x %T", $doc->getValue($vc));
                            break;

                        case "state":
                            $cval = _($doc->getstate());
                            break;

                        case "title":
                            $cval = $doc->getHTMLTitle();
                            break;

                        default:
                            $cval = str_replace(array(
                                '<BR>',
                                '<br/>'
                            ) , '\\n', $doc->getHtmlAttrValue($vc, '', false, -1, false));;
                    }
                    
                    if ($lattr[$vc]->type == "image") $cval = $doc->vault_filename($vc);
                }
                
                $tcell[$kc] = str_replace(array(
                    ";",
                    "\n",
                    "\r"
                ) , array(
                    " - ",
                    "\\n",
                    ""
                ) , $cval);
            }
            $trow[] = array(
                "row" => implode($tcell, ';')
            );
        }
        if ($encoding) {
            foreach ($trow as $k => & $v) {
                $v["row"] = mb_convert_encoding($v["row"], $encoding, "UTF-8");
            }
        }
        $this->lay->setBlockData("rows", $trow);
    }
    
    function viewminireport($target = "_self", $ulink = true, $abstract = false)
    {
        return $this->viewreport($target, $ulink, $abstract);
    }
    /**
     * @begin-method-ignore
     * this part will be deleted when construct document class until end-method-ignore
     */
}
/*
 * @end-method-ignore
*/
?>
