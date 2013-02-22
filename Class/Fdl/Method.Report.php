<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/*
 * @begin-method-ignore
 * this part will be deleted when construct document class until end-method-ignore
*/
/**
 * document to present a report on one family document
 *
 * @author Anakeen
 * @version $Id: Method.Report.php,v 1.20 2008/11/20 09:34:20 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage GED
 *
 */
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
    /**
     * public because use in RSS
     *
     */
    public function _getInternals()
    {
        return array(
            "title" => _("doctitle") ,
            "revdate" => _("revdate") ,
            "id" => _("document id") ,
            "revision" => _("revision") ,
            "state" => _("step") ,
            "owner" => _("owner")
        );
    }
    /**
     * Compute the values fo the edit display
     * @templateController
     *
     */
    public function editreport()
    {
        global $action;
        $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/selectbox.js");
        $action->parent->AddJsRef($action->GetParam("CORE_PUBURL") . "/FREEDOM/Layout/editreport.js");
        
        $rfamid = GetHttpVars("sfamid", $this->getRawValue("SE_FAMID", 1));
        $rdoc = createDoc($this->dbaccess, $rfamid, false);
        $lattr = $rdoc->GetNormalAttributes();
        $tcolumn1 = array();
        $tcolumn2 = array();
        /**
         * @var NormalAttribute $v
         */
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
        $tcols = $this->getMultipleRawValues("REP_IDCOLS");
        
        foreach ($tcols as $k => $vx) {
            if (isset($tcolumn1[$vx])) {
                $tcolumn2[$vx] = $tcolumn1[$vx];
                unset($tcolumn1[$vx]);
            }
            if (isset($tinternals[$vx])) {
                $tcolumn2[$vx] = $tinternals[$vx];
                unset($tinternals[$vx]);
            }
        }
        
        $this->lay->setBlockData("COLUMN1", $tcolumn1);
        $this->lay->setBlockData("INTERNALS", $tinternals);
        
        $this->lay->setBlockData("COLUMN2", $tcolumn2);
    }
    /**
     * Compute the values for the view display
     * @templateController
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
        $rfamid = $this->getRawValue("SE_FAMID", 1);
        $rdoc = createDoc($this->dbaccess, $rfamid, false);
        $lattr = $rdoc->GetNormalAttributes();
        $tcolumn1 = array();
        $tcolumn2 = array();
        foreach ($lattr as $k => $v) {
            //    if ($v->visibility=="H") continue;
            $tcolumn1[$v->id] = array(
                "colid" => $v->id,
                "collabel" => mb_ucfirst($v->getLabel()) ,
                "rightfornumber" => ($v->type == "money") ? "right" : "left"
            );
        }
        
        $tinternals = $this->_getInternals();
        foreach ($tinternals as $k => $v) {
            $tcolumn1[$k] = array(
                "colid" => $k,
                "collabel" => mb_ucfirst($v) ,
                "rightfornumber" => "left"
            );
        }
        
        $tcols = $this->getMultipleRawValues("REP_IDCOLS");
        $tDisplayOption = $this->getMultipleRawValues("REP_DISPLAYOPTION");
        foreach ($tcols as $k => & $vcol) {
            if ($vcol) {
                $tcolumn2[$k] = $tcolumn1[$vcol];
                if ($tDisplayOption[$k] == "docid") {
                    $tcolumn2[$k]["collabel"].= ' (' . _("report:docid") . ')';
                }
            }
        }
        $this->lay->setBlockData("COLS", $tcolumn2);
        $this->lay->set("HASCOLS", count($tcolumn2) > 0);
        include_once ("FDL/Lib.Dir.php");
        
        $this->lay->set("reportstyle", $this->getRawValue("REP_STYLE", "perso"));
        $this->lay->set("isperso", ($this->getRawValue("REP_STYLE", "perso") == "perso"));
        if ($this->isParameterizable() && $ulink) {
            $this->lay->setBlockData("PARAMS", array(
                array(
                    "zou"
                )
            ));
        }
        // --------------------------
        // display body
        $limit = $this->getRawValue("REP_LIMIT", "ALL");
        $order = $this->getRawValue("REP_IDSORT", "title");
        $oa = $rdoc->getAttribute($order);
        if ($oa) {
            if (($oa->type == "docid") && ($oa->getOption("doctitle") != "")) {
                $order = $oa->getOption("doctitle");
                if ($order == 'auto') $order = $oa->id . '_title';
            }
        }
        $order.= " " . $this->getRawValue("REP_ORDERSORT");
        $s = new SearchDoc($this->dbaccess, $this->getRawValue("se_famid"));
        $s->useCollection($this->initid);
        $s->setOrder($order);
        $s->returnsOnly($tcols);
        $s->setObjectReturn();
        $limit = intval($limit);
        $maxDisplayLimit = $this->getFamilyParameterValue("rep_maxdisplaylimit", 1000) + 1;
        if ($limit == 0) $limit = $maxDisplayLimit;
        else $limit = min($limit, $maxDisplayLimit);
        $s->setSlice($limit);
        $s->search();
        $err = $s->getError();
        if ($err) {
            addWarningMsg($err);
            return $err;
        }
        $needRemoveLast = false;
        if ($s->count() >= $maxDisplayLimit) {
            addWarningMsg(sprintf(_("Max display limit %s reached. Use export to see all") , $maxDisplayLimit - 1));
            $needRemoveLast = true;
        }
        $trodd = false;
        $tcolor = $this->getMultipleRawValues("REP_COLORS");
        $trow = array();
        $k = 0;
        while ($rdoc = $s->getNextDoc()) {
            $k++;
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
            
            foreach ($tcolumn2 as $ki => $vc) {
                $kc = $vc["colid"];
                if ($rdoc->getRawValue($kc) == "") $tcell[$ki] = array(
                    "cellval" => ""
                );
                else {
                    switch ($kc) {
                        case "revdate":
                            // $cval= (date("Y-m-d H:i:s", $rdoc->getRawValue($kc)));
                            $cval = (date("Y-m-d H:i:s", $rdoc->getRawValue($kc)));
                            // $cval = strftime("%d/%m/%Y %T", $rdoc->getRawValue($kc));
                            //	  $cval = strftime ("%x %T",$rdoc->getRawValue($kc]);
                            break;

                        case "state":
                            $cval = $rdoc->getStatelabel();
                            break;

                        case "title":
                            if ($ulink) {
                                $cval = $rdoc->getDocAnchor($rdoc->id, 'rdoc' . $rdoc->id, true, false, true, "fixed");
                            } else {
                                $cval = $rdoc->getHtmlTitle();
                            }
                            break;

                        default:
                            if ($tDisplayOption[$ki] == "docid") {
                                $cval = $rdoc->getRawValue($kc);
                            } else {
                                $cval = $rdoc->getHtmlValue($lattr[$kc], $rdoc->getRawValue($kc) , $target, $ulink);
                            }
                            if ($lattr[$kc]->type == "image") $cval = "<img width=\"40px\" src=\"$cval\">";
                        }
                        $tcell[$ki] = array(
                            "cellval" => $cval,
                            "rawval" => $rdoc->getRawValue($kc)
                        );
                }
                $tcell[$ki]["bgcell"] = current($tcolor);
                next($tcolor);
                $tcell[$ki]["tdoddoreven"] = $tdodd ? "tdodd" : "tdeven";
                $tcell[$ki]["rightfornumber"] = (isset($lattr[$kc]) && $lattr[$kc]->type == "money") ? "right" : "left";
                $tdodd = !$tdodd;
            }
            $this->lay->setBlockData("row$k", $tcell);
        }
        
        if ($needRemoveLast) array_pop($trow);
        $this->lay->setBlockData("ROWS", $trow);
        // ---------------------
        // footer
        $tfoots = $this->getMultipleRawValues("REP_FOOTS");
        $tlfoots = array();
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
                        if (isset($ctr[$k]["rawval"])) $val+= $ctr[$k]["rawval"];
                    }
                    if ($v == "MOY") $val = $val / count($trow);
                    if (!$rdoc) {
                        $rdoc = createTmpDoc($this->dbaccess, $this->getRawValue("se_famid"));
                    }
                    $val = $rdoc->getHtmlValue($lattr[$tcols[$k]], $val, $target, $ulink);
                    break;

                default:
                    $val = "-";
                }
                $footRight = '';
                if (isset($tcolumn2[$k])) {
                    $footRight = $tcolumn2[$k]["rightfornumber"];
                }
                $tlfoots[] = array(
                    "footval" => $val,
                    "rightfornumber" => $footRight
                );
            }
            $this->lay->setBlockData("TFOOT", $tlfoots);
            $this->lay->set("TITLE", $this->getHTMLTitle());
            return $err;
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
    public function generateCSVReportStruct($isPivotExport = false, $pivotId = "id", $separator = ".", $dateFormat = "US", $refresh = true, $stripHtmlTags = false)
    {
        require_once 'WHAT/Class.twoDimensionalArray.php';
        require_once 'FDL/Class.SearchDoc.php';
        
        $famId = $this->getRawValue("se_famid", 1);
        $limit = $this->getRawValue("rep_limit", "ALL");
        $order = $this->getRawValue("rep_idsort", "title");
        
        $mb0 = microtime(true);
        $this->setStatus(_("Doing search request"));
        $search = new SearchDoc($this->dbaccess, $famId);
        $search->dirid = $this->initid;
        $search->slice = $limit;
        $search->orderby = trim($order . " " . $this->getRawValue("rep_ordersort"));
        $search->setObjectReturn();
        // print_r($search->getSearchInfo());
        $famDoc = createDoc($this->dbaccess, $famId, false);
        $tcols = $this->getMultipleRawValues("rep_idcols");
        $tcolsOption = $this->getMultipleRawValues("rep_displayoption");
        
        $search->returnsOnly($tcols);
        
        if ($isPivotExport) {
            $search->search();
            $this->setStatus(_("Doing render"));
            return $this->generatePivotCSV($search, $tcols, $famDoc, $pivotId, $refresh, $separator, $dateFormat);
        } else {
            $this->setStatus(_("Doing render"));
            return $this->generateBasicCSV($search, $tcols, $tcolsOption, $famDoc, $refresh, $separator, $dateFormat, $stripHtmlTags);
        }
    }
    
    public static function setStatus($s)
    {
        global $action;
        $expVarName = $action->getParam("exportSession");
        if ($expVarName) $action->Register($expVarName, array(
            "status" => $s
        ));
    }
    
    protected function generatePivotCSV(SearchDoc $search, Array $columns, Doc $famDoc, $pivotId, $refresh, $separator, $dateFormat)
    {
        $convertFormat = array(
            "dateFormat" => $dateFormat,
            'decimalSeparator' => $separator
        );
        
        $pivotColumnName = uniqid();
        
        $singleAttributes = array();
        $multipleAttributes = array();
        $resultSingleArray = array();
        $resultMultipleArray = array();
        
        $internals = $this->_getInternals();
        //Generate column organisation
        $resultSingleArray[$pivotColumnName] = array();
        
        foreach ($columns as $currentColumnID) {
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
        $nbDoc = $search->count();
        $k = 0;
        while ($currentDoc = $search->getNextDoc()) {
            $k++;
            if ($k % 10 == 0) $this->setStatus(sprintf(_("Pivot rendering %d/%d") , $k, $nbDoc));
            if ($refresh) {
                $currentDoc->refresh();
            }
            $pivotAttribute = $famDoc->getAttribute($pivotId);
            $pivotValue = $pivotAttribute ? $pivotAttribute->getTextualValue($currentDoc, -1, $convertFormat) : $this->convertInternalElement($pivotId, $currentDoc);
            $resultSingleArray[$pivotColumnName][] = $pivotValue;
            foreach ($singleAttributes as $currentColumnID) {
                $currentAttribute = $famDoc->getAttribute($currentColumnID);
                $resultSingleArray[$currentColumnID][] = $currentAttribute ? $currentAttribute->getTextualValue($currentDoc, -1, $convertFormat) : $this->convertInternalElement($currentColumnID, $currentDoc);
            }
            foreach ($multipleAttributes as $currentKey => $currentArrayID) {
                foreach ($currentArrayID as $currentColumnID) {
                    $currentAttribute = $famDoc->getAttribute($currentColumnID);
                    $nbElement = count($currentDoc->getMultipleRawValues($currentColumnID));
                    for ($i = 0; $i < $nbElement; $i++) {
                        $resultMultipleArray[$currentKey][$currentColumnID][] = $currentAttribute->getTextualValue($currentDoc, $i, $convertFormat);
                    }
                }
                for ($i = 0; $i < $nbElement; $i++) {
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
        
        foreach ($singleAttributes as $currentColumnID) {
            $currentAttribute = $famDoc->getAttribute($currentColumnID);
            $firstRow[] = $currentAttribute ? $currentAttribute->getLabel() : $internals[$currentColumnID];
            $twoDimStruct->addColumn($resultSingleArray[$currentColumnID]);
        }
        //Generate content
        foreach ($multipleAttributes as $currentKey => $currentArrayID) {
            $firstRow[] = "";
            $emptyArray = array(
                ""
            );
            $twoDimStruct->addColumn($emptyArray);
            $firstRow[] = _("REPORT_pivot");
            $twoDimStruct->addColumn($resultMultipleArray[$currentKey][$pivotColumnName]);
            foreach ($currentArrayID as $currentColumnID) {
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
    protected function generateBasicCSV(SearchDoc $search, Array $columns, Array $displayOptions, Doc $famDoc, $refresh, $separator, $dateFormat, $stripHtmlFormat = true)
    {
        $fc = new FormatCollection();
        $dl = $search->getDocumentList();
        $fc->useCollection($dl);
        if ($separator) $fc->setDecimalSeparator($separator);
        $fc->relationIconSize = 0;
        $fc->stripHtmlTags($stripHtmlFormat);
        switch ($dateFormat) {
            case 'US':
                $fc->setDateStyle(DateAttributeValue::isoWTStyle);
                break;

            case 'FR':
                $fc->setDateStyle(DateAttributeValue::frenchStyle);
                break;

            case 'ISO':
                $fc->setDateStyle(DateAttributeValue::isoStyle);
                break;
        }
        $isAttrInArray = array();
        foreach ($columns as $k => $col) {
            if (empty($col)) {
                unset($columns[$k]);
            } else {
                if ($famDoc->getAttribute($col)) {
                    $fc->addAttribute($col);
                    $isAttrInArray[$col] = $famDoc->getAttribute($col)->inArray();
                } else {
                    $fc->addProperty($col);
                }
            }
        }
        $fc->setNc('-');
        $fc->setHookAdvancedStatus(function ($s)
        {
            _REPORT::setStatus($s);
        });
        $r = $fc->render();
        $this->setStatus(_("Doing csv render"));
        $out = array();
        $line = array();
        foreach ($columns as $kc => $col) {
            if (isset(Doc::$infofields[$col]["label"])) {
                $line[$kc] = _(Doc::$infofields[$col]["label"]);
            } else {
                $line[$kc] = $famDoc->getLabel($col);
                if ($displayOptions[$kc] == "docid") $line[$kc].= ' (' . _("report:docid") . ')';
            }
        }
        $out[] = $line;
        foreach ($r as $k => $render) {
            $line = array();
            foreach ($columns as $kc => $col) {
                $cellValue = '';
                $dDocid = ($displayOptions[$kc] == "docid");
                if (isset($render["attributes"][$col])) {
                    /**
                     * @var StandardAttributeValue $dv
                     */
                    $dv = $render["attributes"][$col];
                    if (is_array($dv)) {
                        $vs = array();
                        foreach ($dv as $rv) {
                            if (is_array($rv)) {
                                $vsv = array();
                                foreach ($rv as $rvv) {
                                    $vsv[] = ($dDocid) ? $rvv->value : $rvv->displayValue;
                                }
                                $vs[] = implode(', ', $vsv);
                            } else {
                                $vs[] = strtr(($dDocid) ? $rv->value : $rv->displayValue, "\n", "\r");
                            }
                        }
                        $cellValue = implode(empty($isAttrInArray[$col]) ? ", " : "\n", $vs);
                    } else {
                        
                        $cellValue = ($dDocid) ? $dv->value : $dv->displayValue;
                    }
                } else {
                    if (isset($render["properties"][$col])) {
                        $cellValue = $render["properties"][$col];
                        if (is_object($cellValue)) {
                            $cellValue = $cellValue->displayValue;
                        }
                    }
                }
                $line[] = $cellValue;
            }
            $out[] = $line;
        }
        
        return $out;
    }
    protected function convertInternalElement($internalName, Doc $doc)
    {
        switch ($internalName) {
            case "revdate":
                return strftime("%x %T", $doc->getRawValue($internalName));
            case "state":
                return $doc->getStatelabel();
            case "title":
                return $doc->getHTMLTitle();
            case "id":
                return $doc->id;
            case "owner":
                return $doc->owner;
            default:
                return $doc->getRawValue($internalName);
        }
    }
    /**
     * Compute the values for the mini view display (portal)
     * @templateController
     *
     * @param string $target window target name for hyperlink destination
     * @param bool $ulink if false hyperlink are not generated
     * @param bool $abstract if true only abstract attribute are generated
     */
    public function viewminireport($target = "_self", $ulink = true, $abstract = false)
    {
        $this->viewreport($target, $ulink, $abstract);
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
