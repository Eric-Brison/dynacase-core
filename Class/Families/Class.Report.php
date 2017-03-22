<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * document to present a report on one family document
 */
namespace Dcp\Core;

use \Dcp\AttributeIdentifiers\Report as MyAttributes;
class Report extends \Dcp\Family\Dsearch
{
    
    public $defaultedit = "FREEDOM:EDITREPORT";
    public $defaultview = "FREEDOM:VIEWREPORT";
    
    public $cviews = array(
        "FREEDOM:VIEWREPORT",
        "FREEDOM:VIEWMINIREPORT:T"
    );
    public $eviews = array(
        "FREEDOM:EDITREPORT"
    );
    
    protected $attributeGrants = array();
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
        if ($rdoc === false) {
            $rdoc = new_Doc('');
        }
        $lattr = $rdoc->GetNormalAttributes();
        $tcolumn1 = array();
        $tcolumn2 = array();
        /**
         * @var \NormalAttribute $v
         */
        foreach ($lattr as $k => $v) {
            //    if ($v->visibility=="H") continue;
            $tcolumn1[$v->id] = array(
                "aid" => $v->id,
                "alabel" => $v->getLabel()
            );
        }
        $tinternals = $this->_getInternals();
        // reconstruct internals for layout
        reset($tinternals);
        foreach ($tinternals as $k => $v) {
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
     * Secondary view to choose columns
     * @templateController
     */
    public function reportchoosecolumns($target = "_self", $ulink = true, $abstract = false)
    {
        global $action;
        
        $action->parent->addJsRef("lib/jquery/jquery.js");
        
        $doc = new_doc($this->dbaccess, $this->getRawValue(MyAttributes::se_famid));
        $toa = $doc->getNormalAttributes();
        $tattr = array();
        
        $colums = getReportColumns($this->dbaccess, $doc->id);
        
        $proFrame = new \FieldSetAttribute("_prop", $doc->id, _("Document Properties"));
        $proAttr = array();
        foreach ($colums as $k => $col) {
            $colId = $col[1];
            if (isset(\Doc::$infofields[$colId])) {
                $proAttr[$colId] = new \NormalAttribute($colId, $doc->id, _(\Doc::$infofields[$colId]["label"]) , "text", "", false, $k, "", "R", false, false, false, $proFrame, "", "", "");
            }
        }
        $toa = array_merge($proAttr, $toa);
        $relTypes = array(
            "docid",
            "account",
            "thesaurus"
        );
        
        $currentArray = '';
        $currentTab = '';
        $currentFrame = '';
        $currentStruct = '';
        
        $structArray = '';
        $structTab = '';
        $structFrame = '';
        /**
         * @var \NormalAttribute $oa
         */
        foreach ($toa as $oa) {
            
            if ($oa->type == "array") continue;
            
            $struct = $oa->fieldSet->id;
            
            $structTab = $structFrame = $structArray = '';
            if ($struct) {
                $soa = $doc->getAttribute($struct);
                if ($struct == '_prop') {
                    $soa = $proFrame;
                }
                $structArray = $structFrame = $structTab = '';
                if ($soa->type == "array") {
                    $structArray = $soa->id;
                } else if ($soa->type == "frame") {
                    $structFrame = $soa->id;
                }
                if (isset($soa->fieldSet) && $soa->fieldSet->id != \Adoc::HIDDENFIELD) {
                    if ($soa->fieldSet->type == "tab") {
                        $structTab = $soa->fieldSet->id;
                    }
                    if ($soa->fieldSet->type == "frame") {
                        $structFrame = $soa->fieldSet->id;
                        if (isset($soa->fieldSet->fieldSet) && $soa->fieldSet->fieldSet->type == "tab") {
                            $structTab = $soa->fieldSet->fieldSet->id;
                        }
                    }
                }
            }
            if ($currentStruct) {
                $soa = $doc->getAttribute($currentStruct);
                if ($currentStruct == '_prop') {
                    $soa = $proFrame;
                }
                $currentArray = $currentFrame = $currentTab = '';
                if ($soa->type == "array") {
                    $currentArray = $soa->id;
                } else if ($soa->type == "frame") {
                    $currentFrame = $soa->id;
                }
                if (isset($soa->fieldSet) && $soa->fieldSet->id != \Adoc::HIDDENFIELD) {
                    if ($soa->fieldSet->type == "tab") {
                        $currentTab = $soa->fieldSet->id;
                    }
                    if ($soa->fieldSet->type == "frame") {
                        $currentFrame = $soa->fieldSet->id;
                        if (isset($soa->fieldSet->fieldSet) && $soa->fieldSet->fieldSet->type == "tab") {
                            $currentTab = $soa->fieldSet->fieldSet->id;
                        }
                    }
                }
            }
            if ($struct) {
                if ($structArray != $currentArray && $currentArray) {
                    $tattr[] = $this->getColumnBlockItem($doc, $currentArray, false);
                }
                if ($structFrame != $currentFrame && $currentFrame) {
                    $tattr[] = $this->getColumnBlockItem($doc, $currentFrame, false);
                }
                if ($structTab != $currentTab && $currentTab) {
                    $tattr[] = $this->getColumnBlockItem($doc, $currentTab, false);
                }
                if ($structTab != $currentTab && $structTab) {
                    
                    $tattr[] = $this->getColumnBlockItem($doc, $structTab, true);
                }
                if ($structFrame != $currentFrame && $structFrame) {
                    $tattr[] = $this->getColumnBlockItem($doc, $structFrame, true);
                }
                if ($structArray != $currentArray && $structArray) {
                    $tattr[] = $this->getColumnBlockItem($doc, $structArray, true);
                }
                $tattr[] = array(
                    "attrid" => $oa->id,
                    "doption" => '',
                    "newframe" => false,
                    "newtab" => false,
                    "newarray" => false,
                    "endframe" => false,
                    "goodattr" => true,
                    "endtab" => false,
                    "endarray" => false,
                    "need" => $oa->needed,
                    "hidden" => $oa->visibility == 'H',
                    "attrname" => htmlspecialchars($oa->getLabel())
                );
                if (in_array($oa->type, $relTypes)) {
                    $tattr[] = array(
                        "attrid" => $oa->id,
                        "doption" => 'docid',
                        "newframe" => false,
                        "newtab" => false,
                        "newarray" => false,
                        "endframe" => false,
                        "goodattr" => true,
                        "endtab" => false,
                        "endarray" => false,
                        "need" => $oa->needed,
                        "hidden" => $oa->visibility == 'H',
                        "attrname" => htmlspecialchars($oa->getLabel()) . '<i> (' . _("report:docid") . ')</i>'
                    );
                }
            }
            
            $currentStruct = $struct;
        }
        
        if ($structArray) {
            $tattr[] = $this->getColumnBlockItem($doc, $structArray, false);
        }
        if ($structFrame) {
            $tattr[] = $this->getColumnBlockItem($doc, $structFrame, false);
        }
        if ($structTab) {
            $tattr[] = $this->getColumnBlockItem($doc, $structTab, false);
        }
        
        $this->lay->setBlockData("ATTRS", $tattr);
        $this->lay->set("famid", $doc->id);
        $this->lay->set("famtitle", $doc->getHtmlTitle());
        $this->lay->set("famicon", $doc->getIcon("", 48));
    }
    
    protected function getColumnBlockItem(\Doc & $doc, $attrid, $isNew)
    {
        /**
         * @var \NormalAttribute $soa
         */
        $soa = $doc->getAttribute($attrid);
        if ($attrid == "_prop") {
            $soa = new \FieldSetAttribute("_prop", $doc->id, _("Document Properties"));
        }
        return array(
            "attrid" => $soa->id,
            "goodattr" => false,
            "doption" => '',
            "newframe" => $isNew && $soa->type == "frame",
            "newtab" => $isNew && $soa->type == "tab",
            "newarray" => $isNew && $soa->type == "array",
            "endframe" => (!$isNew) && $soa->type == "frame",
            "endtab" => (!$isNew) && $soa->type == "tab",
            "endarray" => (!$isNew) && $soa->type == "array",
            "need" => isset($soa->needed) && $soa->needed,
            "hidden" => $soa->visibility == 'H',
            "selected" => false,
            "attrname" => $soa->getLabel()
        );
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
        if ($rdoc === false) {
            $err = sprintf(_('Family [%s] not found') , $rfamid);
            $this->lay->template = htmlspecialchars($err, ENT_QUOTES);
            $this->lay->noparse = true;
            return $err;
        }
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
                $tcolumn2[$k] = isset($tcolumn1[$vcol]) ? $tcolumn1[$vcol] : null;
                if ($tDisplayOption[$k] == "docid") {
                    $tcolumn2[$k]["collabel"].= ' (' . _("report:docid") . ')';
                }
            }
        }
        
        $tcols[] = "cvid";
        $tcols[] = "wid";
        $tcols[] = "state";
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
        $s = new \SearchDoc($this->dbaccess, $this->getRawValue("se_famid"));
        $s->useCollection($this->initid);
        $s->setOrder($order);
        $s->returnsOnly($tcols);
        $s->setObjectReturn();
        $limit = intval($limit);
        $maxDisplayLimit = intval($this->getFamilyParameterValue("rep_maxdisplaylimit", 1000)) + 1;
        if ($limit == 0) $limit = $maxDisplayLimit;
        else $limit = min($limit, $maxDisplayLimit);
        $s->setSlice($limit);
        try {
            $s->search();
        }
        catch(\Exception $e) {
            /*
             * Prevent exception from stopping the view's composition
             * The error will be handled and reported with the getError() method below
            */
        }
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
        \Dcp\VerifyAttributeAccess::clearCache();
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
                    $visible = true;
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
                                $visible = \Dcp\VerifyAttributeAccess::isAttributeAccessGranted($rdoc, $lattr[$kc]);
                                $cval = $rdoc->getRawValue($kc);
                            } else {
                                $cval = $rdoc->getPropertyValue($kc);
                                if ($cval === false) {
                                    $visible = \Dcp\VerifyAttributeAccess::isAttributeAccessGranted($rdoc, $lattr[$kc]);
                                    $cval = $rdoc->getHtmlValue($lattr[$kc], $rdoc->getRawValue($kc) , $target, $ulink);
                                }
                            }
                            if (isset($lattr[$kc]) && $lattr[$kc]->type == "image") {
                                $cval = "<img width=\"40px\" src=\"$cval\">";
                            }
                    }
                    if ($visible) {
                        $tcell[$ki] = array(
                            "cellval" => $cval,
                            "rawval" => $rdoc->getRawValue($kc)
                        );
                    } else {
                        $tcell[$ki] = array(
                            "cellval" => $this->getFamilyParameterValue(MyAttributes::rep_noaccesstext) ,
                            "rawval" => ""
                        );
                    }
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
                    if (isset($lattr[$tcols[$k]])) {
                        $val = $rdoc->getHtmlValue($lattr[$tcols[$k]], $val, $target, $ulink);
                    }
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
    public function generateCSVReportStruct($isPivotExport = false, $pivotId = "id", $separator = ".", $dateFormat = "US", $refresh = true, $stripHtmlTags = false, $renderNumber = "format")
    {
        require_once 'WHAT/Class.twoDimensionalArray.php';
        require_once 'FDL/Class.SearchDoc.php';
        
        $famId = $this->getRawValue("se_famid", 1);
        $limit = $this->getRawValue("rep_limit", "ALL");
        $order = $this->getRawValue("rep_idsort", "title");
        
        $mb0 = microtime(true);
        $this->setStatus(_("Doing search request"));
        $search = new \SearchDoc($this->dbaccess, $famId);
        $search->dirid = $this->initid;
        $search->slice = $limit;
        $search->orderby = trim($order . " " . $this->getRawValue("rep_ordersort"));
        $search->setObjectReturn();
        // print_r($search->getSearchInfo());
        $famDoc = createDoc($this->dbaccess, $famId, false);
        $tcols = $this->getMultipleRawValues("rep_idcols");
        $tcolsOption = $this->getMultipleRawValues("rep_displayoption");
        $searchCols = $tcols;
        $searchCols[] = "cvid";
        $searchCols[] = "wid";
        $search->returnsOnly($searchCols);
        
        if ($isPivotExport) {
            $search->search();
            $this->setStatus(_("Doing render"));
            return $this->generatePivotCSV($search, $tcols, $famDoc, $pivotId, $refresh, $separator, $dateFormat, $stripHtmlTags, $renderNumber);
        } else {
            $this->setStatus(_("Doing render"));
            return $this->generateBasicCSV($search, $tcols, $tcolsOption, $famDoc, $refresh, $separator, $dateFormat, $stripHtmlTags, $renderNumber);
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
    
    protected function generatePivotCSV(\SearchDoc $search, Array $columns, \Doc $famDoc, $pivotId, $refresh, $separator, $dateFormat, $stripHtmlTags, $renderNumber = "format")
    {
        $convertFormat = array(
            "dateFormat" => $dateFormat,
            'decimalSeparator' => $separator,
            'stripHtmlTags' => $stripHtmlTags,
            'renderNumber' => $renderNumber
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
            $pivotValue = $pivotAttribute ? $this->getCellValue($currentDoc, $pivotAttribute, $convertFormat) : $this->convertInternalElement($pivotId, $currentDoc);
            $resultSingleArray[$pivotColumnName][] = $pivotValue;
            foreach ($singleAttributes as $currentColumnID) {
                $currentAttribute = $famDoc->getAttribute($currentColumnID);
                $resultSingleArray[$currentColumnID][] = $currentAttribute ? $this->getCellValue($currentDoc, $currentAttribute, $convertFormat) : $this->convertInternalElement($currentColumnID, $currentDoc);
            }
            $nbElement = 0;
            foreach ($multipleAttributes as $currentKey => $currentArrayID) {
                foreach ($currentArrayID as $currentColumnID) {
                    $currentAttribute = $famDoc->getAttribute($currentColumnID);
                    $nbElement = count($currentDoc->getMultipleRawValues($currentColumnID));
                    for ($i = 0; $i < $nbElement; $i++) {
                        $resultMultipleArray[$currentKey][$currentColumnID][] = $this->getCellValue($currentDoc, $currentAttribute, $convertFormat, $i);
                    }
                }
                for ($i = 0; $i < $nbElement; $i++) {
                    $resultMultipleArray[$currentKey][$pivotColumnName][] = $pivotValue;
                }
            }
        }
        //Generate result array
        $firstRow = array();
        $twoDimStruct = new \TwoDimensionStruct();
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
    
    protected function getCellValue(\Doc $doc, \BasicAttribute $oa, $format, $index = - 1)
    {
        return $oa->getTextualValue($doc, $index, $format);
    }
    /**
     * Generate a basic CSV export
     *
     * @param \SearchDoc $search the result of the report
     * @param array $columns an array of id
     * @param \Doc $famDoc the associated family doc
     *
     * @return array
     */
    protected function generateBasicCSV(\SearchDoc $search, Array $columns, Array $displayOptions, \Doc $famDoc, $refresh, $separator, $dateFormat, $stripHtmlFormat = true, $renderNumber = "format")
    {
        $fc = new \FormatCollection();
        $dl = $search->getDocumentList();
        $fc->useCollection($dl);
        
        $htmlNoAccess = new \DOMDocument();
        $htmlNoAccess->loadHTML($this->getFamilyParameterValue(MyAttributes::rep_noaccesstext));
        
        $fc->setNoAccessText(trim($htmlNoAccess->textContent));
        if ($separator) $fc->setDecimalSeparator($separator);
        $fc->relationIconSize = 0;
        $fc->stripHtmlTags($stripHtmlFormat);
        switch ($dateFormat) {
            case 'US':
                $fc->setDateStyle(\DateAttributeValue::isoWTStyle);
                break;

            case 'FR':
                $fc->setDateStyle(\DateAttributeValue::frenchStyle);
                break;

            case 'ISO':
                $fc->setDateStyle(\DateAttributeValue::isoStyle);
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
        
        $fc->setLongtextMultipleBrToCr(" "); // longtext are in a single line if multiple
        $fc->setNc('-');
        $fc->setHookAdvancedStatus(function ($s)
        {
            \Dcp\Family\Report::setStatus($s);
        });
        $r = $fc->render();
        $this->setStatus(_("Doing csv render"));
        $out = array();
        $line = array();
        foreach ($columns as $kc => $col) {
            if (isset(\Doc::$infofields[$col]["label"])) {
                $line[$kc] = _(\Doc::$infofields[$col]["label"]);
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
                if (isset($render["attributes"][$col])) {
                    $oa = $famDoc->getAttribute($col);
                    $cellValue = \FormatCollection::getDisplayValue($render["attributes"][$col], $oa, -1, array(
                        'displayDocId' => ($displayOptions[$kc] == "docid") ,
                        'stripHtmlTags' => $stripHtmlFormat
                    ));
                    if ($renderNumber === "raw" && in_array($oa->type, array(
                        "int",
                        "double",
                        "money"
                    ))) {
                        if (is_array($render["attributes"][$col])) {
                            $numValues = [];
                            foreach ($render["attributes"][$col] as $arender) {
                                $oneValue = $arender->value;
                                if ($separator) {
                                    $oneValue = str_replace(".", $separator, $oneValue);
                                }
                                $numValues[] = $oneValue;
                            }
                            $cellValue = implode("\n", $numValues);
                        } else {
                            $cellValue = $render["attributes"][$col]->value;
                            if ($separator) {
                                $cellValue = str_replace(".", $separator, $cellValue);
                            }
                        }
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
    protected function convertInternalElement($internalName, \Doc $doc)
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
}
