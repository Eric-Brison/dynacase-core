<?php
/*
 * @begin-method-ignore
 * this part will be deleted when construct document class until end-method-ignore
 */
/**
 * document to present a report on one family document
 *
 * @author Anakeen 2000
 * @version $Id: Method.Report.php,v 1.20 2008/11/20 09:34:20 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage GED
 **/
class _REPORT extends _DSEARCH
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
    protected function _getInternals()
    {
        return array(
            "title" => _("doctitle"),
            "revdate" => _("revdate"),
            "revision" => _("revision"),
            "state" => _("state"),
            "owner" => _("owner")
        );
    }
    /**
     * Compute the values fo the edit display
     *
     */
    public function editreport()
    {
        global $action;
        $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/selectbox.js");
        $action->parent->AddJsRef($action->GetParam("CORE_PUBURL") . "/FREEDOM/Layout/editreport.js");

        $rfamid = GetHttpVars("sfamid", $this->getValue("SE_FAMID", 1));
        $rdoc = createDoc($this->dbaccess, $rfamid, false);
        $lattr = $rdoc->GetNormalAttributes();
        $tcolumn1 = array();
        $tcolumn2 = array();
        while ( list($k, $v) = each($lattr) ) {
            //    if ($v->visibility=="H") continue;
            $tcolumn1[$v->id] = array(
                "aid" => $v->id,
                "alabel" => $v->getLabel()
            );
        }
        $tinternals = $this->_getInternals();

        // reconstruct internals for layout
        reset($tinternals);
        while ( list($k, $v) = each($tinternals) ) {
            $tinternals[$k] = array(
                "aid" => $k,
                "alabel" => $v
            );
        }

        // display selected column
        $tcols = $this->getTValue("REP_IDCOLS");

        foreach ( $tcols as $k => $v ) {
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
    /**
     * Compute the values for the view display
     *
     * @param string $target window target name for hyperlink destination
     * @param bool $ulink if false hyperlink are not generated
     * @param bool $abstract if true only abstract attribute are generated
     */
    public function viewreport($target = "_self", $ulink = true, $abstract = false)
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
        while ( list($k, $v) = each($lattr) ) {
            //    if ($v->visibility=="H") continue;
            $tcolumn1[$v->id] = array(
                "colid" => $v->id,
                "collabel" => $v->getLabel(),
                "rightfornumber" => ($v->type == "money") ? "right" : "left"
            );
        }

        $tinternals = $this->_getInternals();
        foreach ( $tinternals as $k => $v ) {
            $tcolumn1[$k] = array(
                "colid" => $k,
                "collabel" => $v,
                "rightfornumber" => "left"
            );
        }

        $tcols = $this->getTValue("REP_IDCOLS");
        foreach ( $tcols as $k => &$vcol ) {
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
                if ($order == 'auto')
                    $order = $oa->id . '_title';
            }
        }
        $order .= " " . $this->getValue("REP_ORDERSORT");
        $tdoc = getChildDoc($this->dbaccess, $this->initid, 0, $limit, array(), $this->userid, "TABLE", $rfamid, false, $order);
        $trodd = false;
        $tcolor = $this->getTValue("REP_COLORS");
        $trow = array();
        while ( list($k, $v) = each($tdoc) ) {
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

            foreach ( $tcolumn2 as $kc => $vc ) {
                if ($v[$kc] == "")
                    $tcell[$kc] = array(
                        "cellval" => ""
                    );
                else {
                    switch ($kc) {
                    case "revdate" :
                        $cval = strftime("%d/%m/%Y %T", $v[$kc]);
                        //	  $cval = strftime ("%x %T",$v[$kc]);
                        break;
                    case "state" :
                        $cval = _($v[$kc]);
                        break;
                    case "title" :
                        if ($ulink) {
                            $trid = $v["id"];
                            $v[$kc] = $rdoc->getHTMLTitle();
                            $trlink = getparam("CORE_STANDURL") . "&app=FDL&action=FDL_CARD&id=$trid";
                            $cval = "<A target=\"rdoc" . $v["id"] . "\"  onmousedown=\"document.noselect=true;\" ";
                            $cval .= "onclick=\"subwindowm(200,600,'rdoc$trid','$trlink')\" oncontextmenu=\"popdoc(event,'$trlink');return false;\">";
                            $cval .= $v[$kc] . "</a>";
                        } else {
                            $cval = $v[$kc];
                        }
                        break;

                    default :
                        $cval = $rdoc->getHtmlValue($lattr[$kc], $v[$kc], $target, $ulink);
                        if ($lattr[$kc]->type == "image")
                            $cval = "<img width=\"40px\" src=\"$cval\">";

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

        foreach ( $tfoots as $k => $v ) {
            switch ($v) {
            case "CARD" :
                $val = count($trow);
                break;
            case "MOY" :
            case "SUM" :
                reset($trow);
                $val = 0;
                foreach ( $trow as $kr => $vr ) {
                    $ctr = $this->lay->getBlockData($vr["CELLS"]);
                    $val += $ctr[$tcols[$k]]["rawval"];
                }
                if ($v == "MOY")
                    $val = $val / count($trow);
                $val = $rdoc->getHtmlValue($lattr[$tcols[$k]], $val, $target, $ulink);
                break;

            default :
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
     *
     * @param string $target window target name for hyperlink destination
     * @param bool $ulink if false hyperlink are not generated
     * @param bool $abstract if true only abstract attribute are generated
     **/
    public function viewreportcsv($target = "_self", $ulink = true, $abstract = false)
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
        foreach ( $tcols as $kc => $vc ) {
            $oa = $rdoc->getAttribute($vc);
            $cval = ($oa) ? $oa->getLabel() : $tinternals[$vc];
            if (!$cval)
                $cval = $vc;
            $tcell[] = str_replace(array(
                ";",
                "\n",
                "\r"
            ), array(
                " - ",
                "\\n",
                ""
            ), $cval);
        }
        $trow[] = array(
            "row" => implode($tcell, ';')
        );
        while ( $doc = $s->nextDoc() ) {
            $tcell = array();

            foreach ( $tcols as $kc => $vc ) {
                //if ($v[$vc] == "") $cval="";
                if ($doc->getValue($vc) == "")
                    $cval = "";
                else {
                    switch ($vc) {
                    case "revdate" :
                        $cval = strftime("%x %T", $doc->getValue($vc));
                        break;
                    case "state" :
                        $cval = _($doc->getstate());
                        break;
                    case "title" :
                        $cval = $doc->getHTMLTitle();
                        break;
                    default :
                        $cval = str_replace(array(
                            '<BR>',
                            '<br/>'
                        ), '\\n', $doc->getHtmlAttrValue($vc, '', false, -1, false));
                        ;
                    }

                    if ($lattr[$vc]->type == "image")
                        $cval = $doc->vault_filename($vc);
                }

                $tcell[$kc] = str_replace(array(
                    ";",
                    "\n",
                    "\r"
                ), array(
                    " - ",
                    "\\n",
                    ""
                ), $cval);

            }
            $trow[] = array(
                "row" => implode($tcell, ';')
            );
        }
        if ($encoding) {
            foreach ( $trow as $k => &$v ) {
                $v["row"] = mb_convert_encoding($v["row"], $encoding, "UTF-8");
            }
        }
        $this->lay->setBlockData("rows", $trow);
    }

    /**
     * Generate data struct to csv export of a report
     *
     * @param boolean $refresh true to refresh the doc before export
     * @param boolean $isPivotExport if is pivot true
     * @param string $pivotElement id of the pivot element
     *
     * @return array
     */
    public function generateCSVReportStruct($separator = ".", $refresh = true, $isPivotExport = false, $pivotId = "id")
    {
        require_once 'WHAT/Class.twoDimensionnalArray.php';
        require_once 'FDL/Class.SearchDoc.php';

        $famId = $this->getValue("se_famid", 1);
        $limit = $this->getValue("rep_limit", "ALL");
        $order = $this->getValue("rep_idsort", "title");

        $search = new SearchDoc($this->dbaccess, $famId);
        $search->dirid = $this->initid;
        $search->slice = $limit;
        $search->orderby = $order;
        $search->setObjectReturn();
        $search->search();

        $famDoc = createDoc($this->dbaccess, $famId, false);
        $tcols = $this->getTValue("rep_idcols");

        if ($isPivotExport) {
            return $this->generatePivotCSV($search, $tcols, $famDoc, $pivotId, $refresh, $separator);
        } else {
            return $this->generateBasicCSV($search, $tcols, $famDoc, $refresh, $separator);
        }
    }

    protected function generatePivotCSV(SearchDoc $search, Array $columns, Doc $famDoc, $pivotId, $refresh, $separator)
    {
        $pivotColumnName = uniqid();

        $singleAttributes = array();
        $multipleAttributes = array();
        $resultSingleArray = array();
        $resultMultipleArray = array();

        $internals = $this->_getInternals();

        //Generate column organisation
        $resultSingleArray[$pivotColumnName] = array();

        foreach ( $columns as $currentColumnID ) {
            $attributeObject = $famDoc->getAttribute($currentColumnID);
            if (!$attributeObject) {
                $singleAttributes[] = $currentColumnID;
                $resultSingleArray[$currentColumnID] = array();
            } elseif ($attributeObject->isMultiple()) {
                if ($attributeObject->getOption('multiple') == "yes" && !$attributeObject->inArray()) {
                    $multipleAttributes[$currentColumnID] = array();
                    $multipleAttributes[$currentColumnID][] = $currentColumnID;
                    $resultMultipleArray[$currentColumnID] = array();
                    $resultMultipleArray[$currentColumnID][$pivotColumnName] = array();
                    $resultMultipleArray[$currentColumnID][$currentColumnID] = array();
                } else {
                    $arrayID = $attributeObject->fieldSet->id;
                    if (!isset($multipleAttributes[$arrayID])) {
                        $multipleAttributes[$arrayID] = array();
                        $resultMultipleArray[$arrayID] = array();
                        $resultMultipleArray[$arrayID][$pivotColumnName] = array();
                    }
                    $multipleAttributes[$arrayID][] = $currentColumnID;
                    $resultMultipleArray[$arrayID][$currentColumnID] = array();
                }
            } else {
                $singleAttributes[] = $currentColumnID;
                $resultSingleArray[$currentColumnID] = array();
            }
        }

        //Get Value
        while ( $currentDoc = $search->nextDoc() ) {
            if ($refresh) {
                $currentDoc->refresh();
            }
            $pivotAttribute = $famDoc->getAttribute($pivotId);
            $pivotValue = $pivotAttribute ? $pivotAttribute->getTextualValue($currentDoc, -1, $separator) : $this->convertInternalElement($pivotId, $currentDoc);
            $resultSingleArray[$pivotColumnName][] = $pivotValue;
            foreach ( $singleAttributes as $currentColumnID ) {
                $currentAttribute = $famDoc->getAttribute($currentColumnID);
                $resultSingleArray[$currentColumnID][] = $currentAttribute ? $currentAttribute->getTextualValue($currentDoc, -1, $separator) : $this->convertInternalElement($currentColumnID, $currentDoc);
            }
            foreach ( $multipleAttributes as $currentKey => $currentArrayID ) {
                foreach ( $currentArrayID as $currentColumnID ) {
                    $currentAttribute = $famDoc->getAttribute($currentColumnID);
                    $nbElement = count($currentDoc->getTValue($currentColumnID));
                    for($i = 0; $i < $nbElement; $i++) {
                        $resultMultipleArray[$currentKey][$currentColumnID][] = $currentAttribute->getTextualValue($currentDoc, $i, $separator);
                    }
                }
                for($i = 0; $i < $nbElement; $i++) {
                    $resultMultipleArray[$currentKey][$pivotColumnName][] = $pivotValue;
                }
            }
        }

        //Generate result array
        $firstRow = array();
        $twoDimStruct = new TwoDimensionStruct();

        //Generate first line
        $firstRow[] = _("REPORT_pivot");
        $twoDimStruct->addColumn($resultSingleArray[$pivotColumnName]);

        foreach ( $singleAttributes as $currentColumnID ) {
            $currentAttribute = $famDoc->getAttribute($currentColumnID);
            $firstRow[] = $currentAttribute ? $currentAttribute->getLabel() : $internals[$currentColumnID];
            $twoDimStruct->addColumn($resultSingleArray[$currentColumnID]);
        }

        //Generate content
        foreach ( $multipleAttributes as $currentKey => $currentArrayID ) {
            $firstRow[] = "";
            $emptyArray = array("");
            $twoDimStruct->addColumn($emptyArray);
            $firstRow[] = _("REPORT_pivot");
            $twoDimStruct->addColumn($resultMultipleArray[$currentKey][$pivotColumnName]);
            foreach ( $currentArrayID as $currentColumnID ) {
                $currentAttribute = $famDoc->getAttribute($currentColumnID);
                $firstRow[] = $currentAttribute ? $currentAttribute->getLabel() : $internals[$currentColumnID];
                $twoDimStruct->addColumn($resultMultipleArray[$currentKey][$currentColumnID]);
            }
        }

        if ($twoDimStruct->insertRow(0, $firstRow, true) == null) {
            var_export($twoDimStruct->getLastErrorMessage());
        }

        return $twoDimStruct->getArray();
    }

    /**
     * Generate a basic CSV export
     *
     * @param SearchDoc $search the result of the report
     * @param array $columns an array of id
     * @param Doc $famDoc the associated family doc
     *
     * @return array
     */
    protected function generateBasicCSV(SearchDoc $search, Array $columns, Doc $famDoc, $refresh)
    {
        $twoDimStruct = new TwoDimensionStruct();
        $firstRow = array();
        $internals = $this->_getInternals();
        foreach ( $columns as $currentColumn ) {
            $currentAttribute = $famDoc->getAttribute($currentColumn);
            $firstRow[] = $currentAttribute ? $currentAttribute->getLabel() : $internals[$currentColumn];
        }
        $twoDimStruct->addRow($firstRow);
        while ( $currentDoc = $search->nextDoc() ) {
            if ($refresh) {
                $currentDoc->refresh();
            }
            $currentRow = array();
            foreach ( $columns as $currentColumn ) {
                $currentAttribute = $famDoc->getAttribute($currentColumn);
                $currentRow[] = $currentAttribute ? $currentAttribute->getTextualValue($currentDoc, -1, $separator) : $this->convertInternalElement($currentColumn, $currentDoc);
            }
            $twoDimStruct->addRow($currentRow);
        }
        return $twoDimStruct->getArray();
    }

    protected function convertInternalElement($internalName, Doc $doc)
    {
        switch ($internalName) {
        case "revdate" :
            return strftime("%x %T", $doc->getValue($internalName));
        case "state" :
            $stateValue = $doc->getstate();
            if (empty($stateValue)){
                return "";
            }
            return _($stateValue);
        case "title" :
            return $doc->getHTMLTitle();
        case "id" :
            return $doc->id;
        case "owner":
            return $doc->owner;
        default :
            return $doc->getValue($internalName);
        }
    }
    /**
     * Compute the values for the mini view display (portal)
     *
     * @param string $target window target name for hyperlink destination
     * @param bool $ulink if false hyperlink are not generated
     * @param bool $abstract if true only abstract attribute are generated
     */
    public function viewminireport($target = "_self", $ulink = true, $abstract = false)
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
