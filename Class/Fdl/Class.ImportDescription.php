<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Import documents
 *
 * @author Anakeen
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/import_file.php");

class importDocumentDescription
{
    const attributePrefix = ":ATTR:";
    const documentPrefix = ":DOC:";
    private $dirid = 0;
    private $analyze = false;
    private $policy = "update";
    private $reinit = false;
    private $csvSeparator = SEPCHAR;
    private $csvEnclosure = '';
    private $csvLinebreak = '\n';
    private $beginLine = 0;
    private $familyIcon = 0;
    private $nLine = 0;
    private $nbDoc = 0;
    private $ods2CsvFile = '';
    private $reset = array();
    /* Store erroneous family's ORDER line to prevent import of document from that family */
    private $badOrderErrors = array();
    /**
     * @var bool verify attribute access (visibility "I")
     */
    private $verifyAttributeAccess = true;
    /**
     * @var StructAttribute
     */
    private $structAttr = null;
    /**
     * @var array
     */
    private $colOrders = array();
    /**
     * @var array
     */
    private $tcr = array();
    private $dbaccess = '';
    private $needCleanStructure = false;
    private $needCleanParamsAndDefaults = false;
    private $importFileName = '';
    /*
     * @var ressource
    */
    private $fdoc;
    /**
     * @var DocFam
     */
    private $doc;
    /**
     * Store attributes defined/updated by the current import session.
     *
     * @var array
     */
    private $importedAttribute = array();
    /**
     * Store known logical names
     *
     * @var array
     */
    private $knownLogicalNames = array();
    private $userIds = [];
    /**
     * @param string $importFile
     * @throws Dcp\Exception
     */
    public function __construct($importFile)
    {
        if (seemsODS($importFile)) {
            $this->ods2CsvFile = ods2csv($importFile);
            $this->fdoc = fopen($this->ods2CsvFile, "r");
        } else {
            $this->fdoc = fopen($importFile, "r");
        }
        if (!$this->fdoc) {
            throw new Dcp\Exception(sprintf("no import file found : %s", $importFile));
        }
        $this->importFileName = $importFile;
        $this->dbaccess = getDbAccess();
    }
    /**
     * @param boolean $verifyAttributeAccess
     */
    public function setVerifyAttributeAccess($verifyAttributeAccess)
    {
        $this->verifyAttributeAccess = $verifyAttributeAccess;
    }
    
    public function analyzeOnly($analyze)
    {
        $this->analyze = $analyze;
    }
    
    public function setPolicy($policy)
    {
        if (!$policy) {
            $policy = "update";
        }
        $this->policy = $policy;
    }
    
    public function setImportDirectory($dirid)
    {
        $this->dirid = $dirid;
    }
    
    public function reinitAttribute($reinit)
    {
        $this->reinit = $reinit;
    }
    
    public function reset($reset)
    {
        
        if ($reset && !is_array($reset)) {
            $reset = array(
                $reset
            );
        }
        $this->reset = $reset;
    }
    public function setComma($comma)
    {
        $this->csvSeparator = $comma;
    }
    
    public function setCsvOptions($csvSeparator = ';', $csvEnclosure = '"', $csvLinebreak = '\n')
    {
        $this->csvSeparator = $csvSeparator;
        $this->csvEnclosure = $csvEnclosure;
        $this->csvLinebreak = $csvLinebreak;
        
        $this->setAutoCsvOptions();
        return array(
            "separator" => $this->csvSeparator,
            "enclosure" => $this->csvEnclosure,
            "linebreak" => $this->csvLinebreak
        );
    }
    /**
     * Detect csv options - separator and enclosure arguments are modified if set to auto
     * @param $csvFileName
     * @param string &$separator need to set to 'auto' to detect
     * @param string &$enclosure need to set to 'auto' to detect
     * @return array associaive array "enclosure", "separator" keys
     * @throws Dcp\Exception
     */
    public static function detectAutoCsvOptions($csvFileName, &$separator = 'auto', &$enclosure = 'auto')
    {
        $content = file_get_contents($csvFileName);
        if ($separator == 'auto') {
            $detector = new \Dcp\Utils\CSVFormatDetector\Detector();
            $detected = $detector->detect($content);
            if (!isset($detected['separator']['char']) || $detected['separator']['char'] === null) {
                throw new Dcp\Exception(sprintf("cannot find csv separator in %s file", $csvFileName));
            }
            $separator = $detected['separator']['char'];
        }
        if ($enclosure == 'auto') {
            $detector = new \Dcp\Utils\CSVFormatDetector\Detector();
            $detector->separators = array(
                $separator
            );
            $detected = $detector->detect($content);
            if (isset($detected['enclosure']['char']) && $detected['enclosure']['char'] !== null) {
                $enclosure = $detected['enclosure']['char'];
            } else {
                $enclosure = '';
            }
        }
        return array(
            "separator" => $separator,
            "enclosure" => $enclosure
        );
    }
    
    protected function setAutoCsvOptions()
    {
        if (!$this->ods2CsvFile) {
            if (($this->csvSeparator == 'auto') || ($this->csvEnclosure == 'auto')) {
                $this->detectAutoCsvOptions($this->importFileName, $this->csvSeparator, $this->csvEnclosure);
            }
        } else {
            // converted from ods
            // separator is ; no enclosure
            $this->csvEnclosure = '';
            $this->csvSeparator = ';';
            $this->csvLinebreak = '\n';
        }
    }
    public function import()
    {
        setMaxExecutionTimeTo(300);
        
        $this->nbDoc = 0; // number of imported document
        $this->dbaccess = getDbAccess();
        $this->structAttr = null;
        $this->colOrders = array();
        $this->ods2CsvFile = "";
        
        $this->nLine = 0;
        $this->beginLine = 0;
        $csvLinebreak = $this->csvLinebreak;
        if (!$this->csvSeparator && !$csvLinebreak) {
            $csvLinebreak = '\n';
        }
        while (!feof($this->fdoc)) {
            if (!$this->csvEnclosure) {
                $buffer = rtrim(fgets($this->fdoc, 16384));
                $data = explode($this->csvSeparator, $buffer);
                $data = array_map(function ($v) use ($csvLinebreak)
                {
                    return str_replace(array(
                        $csvLinebreak,
                        ALTSEPCHAR
                    ) , array(
                        "\n",
                        ';'
                    ) , $v);
                }
                , $data);
            } else {
                $data = fgetcsv($this->fdoc, 0, $this->csvSeparator, $this->csvEnclosure);
                if ($data === false) {
                    continue;
                }
                if ($csvLinebreak) {
                    $data = array_map(function ($v) use ($csvLinebreak)
                    {
                        return str_replace($csvLinebreak, "\n", $v);
                    }
                    , $data);
                }
            }
            $this->nLine++;
            
            if (!isUTF8($data)) $data = array_map("utf8_encode", $data);
            // return structure
            if (count($data) < 1) continue;
            $this->tcr[$this->nLine] = array(
                "err" => "",
                "msg" => "",
                "specmsg" => "",
                "folderid" => 0,
                "foldername" => "",
                "filename" => "",
                "title" => "",
                "id" => "",
                "values" => array() ,
                "familyid" => 0,
                "familyname" => "",
                "action" => " "
            );
            $this->tcr[$this->nLine]["title"] = substr($data[0], 0, 10);
            $data[0] = trim($data[0]);
            switch ($data[0]) {
                    // -----------------------------------
                    
                case "BEGIN":
                    $this->doBegin($data);
                    break;
                    // -----------------------------------
                    
                case "END":
                    
                    $this->doEnd($data);
                    
                    break;

                case "RESET":
                    $this->doReset($data);
                    break;
                    // -----------------------------------
                    
                case "DOC":
                    
                    $this->doDoc($data);
                    break;
                    // -----------------------------------
                    
                case "SEARCH":
                    $this->doSearch($data);
                    
                    break;
                    // -----------------------------------
                    
                case "TYPE":
                    if (!$this->doc) {
                        break;
                    }
                    
                    $this->doc->doctype = $data[1];
                    $this->tcr[$this->nLine]["msg"] = sprintf(_("set doctype to '%s'") , $data[1]);
                    break;
                    // -----------------------------------
                    
                case "GENVERSION":
                    if (!$this->doc) {
                        break;
                    }
                    
                    $this->doc->genversion = $data[1];
                    $this->tcr[$this->nLine]["msg"] = sprintf(_("generate version '%s'") , $data[1]);
                    break;
                    // -----------------------------------
                    
                case "MAXREV":
                    if (!$this->doc) {
                        break;
                    }
                    
                    $this->doc->maxrev = intval($data[1]);
                    $this->tcr[$this->nLine]["msg"] = sprintf(_("max revision '%d'") , $data[1]);
                    break;
                    // -----------------------------------
                    
                case "ICON": // for family
                    $this->doIcon($data);
                    break;

                case "DOCICON":
                    $this->doDocIcon($data);
                    break;

                case "DOCATAG":
                    $this->doDocAtag($data);
                    break;

                case "DFLDID":
                    $this->doDfldid($data);
                    break;

                case "CFLDID":
                    $this->doCfldid($data);
                    break;

                case "WID":
                    $this->doWid($data);
                    break;

                case "CVID":
                    $this->doCvid($data);
                    break;

                case "SCHAR":
                    if (!$this->doc) {
                        break;
                    }
                    
                    $this->doc->schar = $data[1];
                    $this->tcr[$this->nLine]["msg"] = sprintf(_("set special characteristics to '%s'") , $data[1]);
                    break;
                    // -----------------------------------
                    
                case "CLASS":
                    $this->doClass($data);
                    break;

                case "METHOD":
                    $this->doMethod($data);
                    break;

                case "USEFORPROF":
                    if (!$this->doc) {
                        break;
                    }
                    
                    $this->doc->usefor = "P";
                    $this->tcr[$this->nLine]["msg"] = sprintf(_("change special use to '%s'") , $this->doc->usefor);
                    break;

                case "USEFOR":
                    if (!$this->doc) {
                        break;
                    }
                    
                    $this->doc->usefor = $data[1];
                    $this->tcr[$this->nLine]["msg"] = sprintf(_("change special use to '%s'") , $this->doc->usefor);
                    break;

                case "TAG":
                    $this->doATag($data);
                    break;

                case "CPROFID":
                    $this->doCprofid($data);
                    break;

                case "PROFID":
                    $this->doProfid($data);
                    break;

                case "DEFAULT":
                    $this->doDefault($data);
                    break;

                case "INITIAL":
                    $this->doInitial($data);
                    break;

                case "IATTR":
                    $this->doIattr($data);
                    break;

                case "PARAM":
                case "OPTION":
                case "ATTR":
                case "MODATTR":
                    $this->doAttr($data);
                    break;

                case "ORDER":
                    $this->doOrder($data);
                    break;

                case "KEYS":
                    $this->doKeys($data);
                    break;

                case "TAGABLE":
                    $this->doTagable($data);
                    break;

                case "PROFIL":
                    $this->doProfil($data);
                    break;

                case "ACCESS":
                    $this->doAccess($data);
                    break;

                case "LDAPMAP":
                    $this->doLdapmap($data);
                    
                    break;

                case "PROP":
                    $this->doProp($data);
                    break;

                default:
                    // uninterpreted line
                    unset($this->tcr[$this->nLine]);
            }
        }
        
        fclose($this->fdoc);
        
        if ($this->ods2CsvFile) {
            unlink($this->ods2CsvFile);
        } // temporary csvfile
        return $this->tcr;
    }
    /**
     * add application tag
     * @param array $data
     */
    protected function doATag(array $data)
    {
        if (!$this->doc) return;
        $err = $this->doc->AddATag($data[1]);
        if (!$err) $this->tcr[$this->nLine]["msg"] = sprintf(_("change application tag to '%s'") , $this->doc->atags);
        else {
            $this->tcr[$this->nLine]["err"] = "ATAG:" . $err;
            $this->tcr[$this->nLine]["action"] = "ignored";
        }
    }
    /**
     * analyze BEGIN
     * @param array $data line of description file
     */
    protected function doBegin(array $data)
    {
        $err = "";
        $data = array_map("trim", $data);
        // search from name or from id
        try {
            $this->doc = null;
            $this->beginLine = $this->nLine;
            $check = new CheckBegin();
            $this->tcr[$this->nLine]["err"] = $check->check($data)->getErrors();
            if ($this->tcr[$this->nLine]["err"] && $this->analyze) {
                $this->tcr[$this->nLine]["msg"] = sprintf(_("Element can't be perfectly analyze, some error might occur or be corrected when importing"));
                $this->tcr[$this->nLine]["action"] = "warning"; #_("warning")
                return;
            }
            if ($this->tcr[$this->nLine]["err"] == "") {
                if (($data[3] == "") || ($data[3] == "-")) $this->doc = new DocFam($this->dbaccess, getFamIdFromName($this->dbaccess, $data[5]) , '', 0, false);
                else $this->doc = new DocFam($this->dbaccess, $data[3], '', 0, false);
                
                $this->familyIcon = "";
                
                if (!$this->doc->isAffected()) {
                    
                    if (!$this->analyze) {
                        $this->doc = new DocFam($this->dbaccess);
                        
                        if (isset($data[3]) && ($data[3] > 0)) $this->doc->id = $data[3]; // static id
                        if (is_numeric($data[1])) $this->doc->fromid = $data[1];
                        else $this->doc->fromid = getFamIdFromName($this->dbaccess, $data[1]);
                        if (isset($data[5])) $this->doc->name = $data[5]; // internal name
                        $err = $this->doc->Add();
                    }
                    $this->tcr[$this->nLine]["msg"] = sprintf(_("create %s family %s") , $data[2], $data[5]);
                    $this->tcr[$this->nLine]["action"] = "added";
                } else {
                    $this->tcr[$this->nLine]["action"] = "updated";
                    $this->tcr[$this->nLine]["msg"] = sprintf(_("update %s family %s") , $data[2], $data[5]);
                }
                if ($data[1] && ($data[1] != '-')) {
                    if ($data[1] == '--') $this->doc->fromid = 0;
                    else if (is_numeric($data[1])) $this->doc->fromid = $data[1];
                    else $this->doc->fromid = getFamIdFromName($this->dbaccess, $data[1]);
                }
                if ($data[2] && ($data[2] != '-')) $this->doc->title = $data[2];
                if ($data[4] && ($data[4] != '-')) $this->doc->classname = $data[4]; // new classname for familly
                if ($data[4] == "--") $this->doc->classname = '';
                $this->tcr[$this->nLine]["err"].= $check->checkClass($data, $this->doc)->getErrors();
                
                if ($data[5] && ($data[5] != '-')) $this->doc->name = $data[5]; // internal name
                $this->tcr[$this->nLine]["err"].= $err;
                
                if ($this->reinit) {
                    $this->tcr[$this->nLine]["msg"].= sprintf(_("reinit all attributes"));
                    if ($this->analyze) return;
                    $oattr = new DocAttr($this->dbaccess);
                    $oattr->docid = intval($this->doc->id);
                    if ($oattr->docid > 0) {
                        $err = $oattr->exec_query(sprintf("delete from docattr where docid=%d", $oattr->docid));
                        // $err .= $oattr->exec_query(sprintf("update docfam set defval=null,param=null  where id=%d",  $oattr->docid));
                        
                    }
                    $this->tcr[$this->nLine]["err"].= $err;
                }
                if ($this->reset) {
                    foreach ($this->reset as $reset) {
                        $this->doReset(array(
                            "RESET",
                            $reset
                        ));
                    }
                }
            } else {
                $this->tcr[$this->nLine]["err"].= $err;
            }
        }
        catch(Exception $e) {
            $this->tcr[$this->nLine]["err"].= $e->getMessage();
        }
        if ($this->tcr[$this->nLine]["err"]) $this->tcr[$this->nLine]["action"] = "ignored";
    }
    /**
     * analyze END
     * @param array $data line of description file
     */
    protected function doEnd(array $data)
    {
        if (!$this->doc) return;
        // add messages
        $msg = sprintf(_("modify %s family") , $this->doc->title);
        $this->tcr[$this->nLine]["msg"] = $msg;
        
        $ferr = '';
        for ($i = $this->beginLine; $i < $this->nLine; $i++) {
            if (!empty($this->tcr[$i]["err"])) $ferr.= $this->tcr[$i]["err"];
        }
        if ($this->analyze) {
            $this->nbDoc++;
            if ($ferr) {
                $this->tcr[$this->beginLine]["action"] = "warning";
            }
            return;
        }
        if ((count($data) > 3) && ($data[3] != "")) $this->doc->doctype = "S";
        if ($ferr == "") {
            $now = gettimeofday();
            $this->doc->revdate = $now['sec'];
            $this->doc->modify();
            
            $check = new CheckEnd($this);
            if ($this->doc->doctype == "C") {
                global $tFamIdName;
                $check->checkMaxAttributes($this->doc);
                $err = $check->getErrors();
                
                if ($err && $this->analyze) {
                    $this->tcr[$this->nLine]["msg"].= sprintf(_("Element can't be perfectly analyze, some error might occur or be corrected when importing"));
                    $this->tcr[$this->nLine]["action"] = "warning";
                    return;
                }
                if ($err == '') {
                    if (strpos($this->doc->usefor, "W") !== false) $this->doc->postImport(); //special to add calculated attributes
                    $msg = \Dcp\FamilyImport::refreshPhpPgDoc($this->dbaccess, $this->doc->id);
                    if ($msg !== '') {
                        $this->tcr[$this->nLine]["err"].= $msg;
                        $this->tcr[$this->nLine]["action"] = "ignored";
                        $this->tcr[$this->beginLine]["action"] = "ignored";
                        return;
                    }
                    if (isset($tFamIdName)) $tFamIdName[$this->doc->name] = $this->doc->id; // refresh getFamIdFromName for multiple family import
                    $checkCr = checkDb::verifyDbFamily($this->doc->id);
                    if (count($checkCr) > 0) {
                        $this->tcr[$this->nLine]["err"].= ErrorCode::getError('ATTR1700', implode(",", $checkCr));
                    } else {
                        // Need to update child family in case of new attribute
                        $childsFams = ($this->doc->getChildFam());
                        foreach ($childsFams as $famInfo) {
                            \Dcp\FamilyImport::createDocFile($this->dbaccess, $famInfo);
                        }
                    }
                } else {
                    $this->tcr[$this->nLine]["err"].= $err;
                }
            }
            
            if ($this->needCleanParamsAndDefaults) {
                $this->needCleanParamsAndDefaults = false;
                $this->cleanDefaultAndParametersValues();
            }
            
            $this->tcr[$this->nLine]["err"].= $check->check($data, $this->doc)->getErrors();
            if ($this->tcr[$this->nLine]["err"] && $this->analyze) {
                $this->tcr[$this->nLine]["msg"].= sprintf(_("Element can't be perfectly analyze, some error might occur or be corrected when importing"));
                $this->tcr[$this->nLine]["action"] = "warning";
                return;
            }
            
            if ((!$this->analyze) && ($this->familyIcon != "")) $this->doc->changeIcon($this->familyIcon);
            $this->tcr[$this->nLine]["msg"].= $this->doc->postImport();
            if (!$this->tcr[$this->nLine]["err"]) {
                $check->checkMaxAttributes($this->doc);
                $this->tcr[$this->nLine]["err"] = $check->getErrors();
                if ($this->tcr[$this->nLine]["err"] && $this->analyze) {
                    $this->tcr[$this->nLine]["msg"].= sprintf(_("Element can't be perfectly analyze, some error might occur or be corrected when importing"));
                    $this->tcr[$this->nLine]["action"] = "warning";
                    return;
                }
            }
            
            $this->doc->addHistoryEntry(_("Update by importation"));
            
            $this->nbDoc++;
            
            clearCacheDoc($this->doc->id);
            if ($this->tcr[$this->nLine]["err"]) {
                $this->tcr[$this->beginLine]["action"] = "ignored";
                $this->tcr[$this->nLine]["action"] = "ignored";
            }
        } else {
            $this->tcr[$this->beginLine]["action"] = "ignored";
            $this->tcr[$this->nLine]["action"] = "ignored";
        }
        if ($this->needCleanStructure) {
            $this->needCleanStructure = false;
            if (!$this->tcr[$this->nLine]["err"]) {
                $this->cleanStructure();
            }
        }
    }
    /**
     * Delete undeclared sql columns
     */
    protected function cleanStructure()
    {
        if (!$this->doc) return;
        
        $orphanAttributes = CheckDb::getOrphanAttributes($this->doc->id);
        if ($orphanAttributes) {
            
            $sql = array();
            foreach ($orphanAttributes as $orphanAttrId) {
                $sql[] = sprintf("alter table doc%d drop column %s cascade; ", $this->doc->id, $orphanAttrId);
                
                $this->tcr[$this->nLine]["msg"].= "\nDestroy values for \"$orphanAttrId\".";
            }
            $sql[] = sprintf("create view family.\"%s\" as select * from doc%d", strtolower($this->doc->name) , $this->doc->id);
            
            foreach ($sql as $aSql) {
                simpleQuery('', $aSql);
            }
        }
    }
    
    protected function cleanDefaultAndParametersValues()
    {
        
        $defs = $this->doc->getOwnDefValues();
        foreach ($defs as $aid => $v) {
            if (!$this->doc->getAttribute($aid)) {
                $this->doc->setDefValue($aid, '', false);
                $this->tcr[$this->nLine]["msg"].= "\nClear default value \"$aid\".";
            }
        }
        $defs = $this->doc->getOwnParams();
        foreach ($defs as $aid => $v) {
            if (!$this->doc->getAttribute($aid)) {
                $this->doc->setParam($aid, '', false);
                $this->tcr[$this->nLine]["msg"].= "\nClear parameter value \"$aid\".";
            }
        }
        
        $this->doc->modify();
    }
    /**
     * analyze RESET²
     * @param array $data line of description file
     */
    protected function doReset(array $data)
    {
        if (!$this->doc) return;
        $err = "";
        $data = array_map("trim", $data);
        $check = new CheckReset();
        $this->tcr[$this->nLine]["err"] = $check->check($data, $this->doc)->getErrors();
        if ($this->tcr[$this->nLine]["err"] && $this->analyze) {
            $this->tcr[$this->nLine]["msg"] = sprintf(_("Element can't be perfectly analyze, some error might occur or be corrected when importing"));
            $this->tcr[$this->nLine]["action"] = "warning";
            return;
        }
        if (!$this->tcr[$this->nLine]["err"]) {
            switch (strtolower($data[1])) {
                case 'attributes':
                    $this->tcr[$this->nLine]["msg"].= "\n" . sprintf(_("reinit all attributes"));
                    if ($this->analyze) {
                        return;
                    }
                    
                    $sql = sprintf("delete from docattr where docid=%d", $this->doc->id);
                    simpleQuery($this->dbaccess, $sql);
                    
                    $this->needCleanParamsAndDefaults = true;
                    break;

                case 'default':
                    $this->tcr[$this->nLine]["msg"].= "\n" . sprintf(_("Reset defaults values"));
                    $this->doc->defval = '';
                    break;

                case 'parameters':
                    $this->tcr[$this->nLine]["msg"].= "\n" . sprintf(_("Reset parameters values"));
                    $this->doc->param = '';
                    break;

                case 'enums':
                    $this->tcr[$this->nLine]["msg"].= "\n" . sprintf(_("Reset enums definition"));
                    $sql = sprintf("update docattr set phpfunc=null from docenum where docattr.docid=docenum.famid and docattr.id = docenum.attrid and docattr.type ~ 'enum' and docattr.docid=%d", $this->doc->id);
                    simpleQuery($this->dbaccess, $sql);
                    $sql = sprintf("delete from docenum where famid=%d", $this->doc->id);
                    simpleQuery($this->dbaccess, $sql);
                    
                    break;

                case 'properties':
                    
                    $this->tcr[$this->nLine]["msg"].= "\n" . sprintf(_("reinit all properties"));
                    if ($this->analyze) {
                        return;
                    }
                    $this->doc->resetPropertiesParameters();
                    break;

                case 'structure':
                    $this->tcr[$this->nLine]["msg"].= "\n" . sprintf(_("Reset attribute structure"));
                    if ($this->analyze) {
                        return;
                    }
                    $sql = sprintf("delete from docattr where docid=%d", $this->doc->id);
                    simpleQuery($this->dbaccess, $sql);
                    $this->needCleanStructure = true;
                    $this->needCleanParamsAndDefaults = true;
                    break;
            }
        } else {
            $this->tcr[$this->nLine]["action"] = "ignored";
        }
        $this->tcr[$this->nLine]["err"].= $err;
    }
    /**
     * analyze DOC
     * @param array $data line of description file
     */
    protected function doDoc(array $data)
    {
        $check = new CheckDoc();
        $this->tcr[$this->nLine]["err"] = $check->check($data)->getErrors();
        $famName = $check->getParsedFamName();
        if ($this->tcr[$this->nLine]["err"]) {
            if ($this->analyze) {
                $this->tcr[$this->nLine]["msg"] = sprintf(_("Element can't be perfectly analyze, some error might occur or be corrected when importing"));
                $this->tcr[$this->nLine]["action"] = "warning";
            } else {
                $this->tcr[$this->nLine]["action"] = "ignored";
            }
            return;
        }
        if ($famName !== false && isset($this->badOrderErrors[$famName])) {
            /* Do not import the document if the ORDER line of its family was erroneous */
            if ($this->analyze) {
                $this->tcr[$this->nLine]["msg"] = sprintf(_("Cannot import document because the ORDER line for family '%s' is incorrect: %s") , $famName, $this->badOrderErrors[$famName]);
                $this->tcr[$this->nLine]["action"] = "warning";
            } else {
                $this->tcr[$this->nLine]["msg"] = sprintf(_("Cannot import document because the ORDER line for family '%s' is incorrect: %s") , $famName, $this->badOrderErrors[$famName]);
                $this->tcr[$this->nLine]["action"] = "ignored";
            }
            return;
        }
        // case of specific order
        if (is_numeric($data[1])) $fromid = $data[1];
        else $fromid = getFamIdFromName($this->dbaccess, $data[1]);
        
        if (isset($tkeys[$fromid])) $tk = $tkeys[$fromid];
        else $tk = array(
            "title"
        );
        
        $torder = array();
        if (isset($this->colOrders[$fromid])) {
            $torder = $this->colOrders[$fromid];
        }
        // $this->tcr[$this->nLine] = csvAddDoc($this->dbaccess, $data, $this->dirid, $this->analyze, '', $this->policy, $tk, array() , $torder);
        $oImportDocument = new importSingleDocument();
        if ($tk) {
            $oImportDocument->setKey($tk);
        }
        if ($torder) {
            $oImportDocument->setOrder($torder);
        }
        $oImportDocument->analyzeOnly($this->analyze);
        $oImportDocument->setPolicy($this->policy);
        $oImportDocument->setTargetDirectory($this->dirid);
        $oImportDocument->setVerifyAttributeAccess($this->verifyAttributeAccess);
        /**
         * Append current document's logical name to list of known logical names
         * and configure the importer to use this list to check for unknown
         * logical names
         */
        if ($data[2] != '' && !in_array($data[2], $this->knownLogicalNames)) {
            $this->knownLogicalNames[] = $data[2];
        }
        $oImportDocument->setKnownLogicalNames($this->knownLogicalNames);
        
        $this->tcr[$this->nLine] = $oImportDocument->import($data)->getImportResult();
        
        if ($this->tcr[$this->nLine]["err"] == "") {
            $this->nbDoc++;
        } else {
            $check->addError($this->tcr[$this->nLine]["err"]);
            $this->tcr[$this->nLine]["msg"] = sprintf(_("Element can't be perfectly analyze, some error might occur or be corrected when importing"));
            $this->tcr[$this->nLine]["action"] = "warning";
        }
    }
    /**
     * analyze SEARCH
     * @param array $data line of description file
     */
    protected function doSearch(array $data)
    {
        $err = '';
        if ($data[1] != '') {
            $this->tcr[$this->nLine]["id"] = $data[1];
            /**
             * @var DocSearch $search
             */
            $search = new_Doc($this->dbaccess, $data[1]);
            if (!$search->isAffected()) {
                $search = createDoc($this->dbaccess, 5);
                if (!$this->analyze) {
                    if ($data[1] && is_numeric($data[1])) {
                        $search->id = $data[1]; // static id
                        
                    }
                    $err = $search->Add();
                    if ($data[1] && !is_numeric($data[1])) {
                        $search->setLogicalName($data[1]);
                    }
                }
                $this->tcr[$this->nLine]["msg"] = sprintf(_("update %s search") , $data[3]);
                $this->tcr[$this->nLine]["action"] = "updated";
            }
        } else {
            $search = createDoc($this->dbaccess, 5);
            if (!$this->analyze) {
                $err = $search->Add();
            }
            $this->tcr[$this->nLine]["msg"] = sprintf(_("add %s search") , $data[3]);
            $this->tcr[$this->nLine]["action"] = "added";
            $this->tcr[$this->nLine]["err"].= $err;
        }
        if (($err != "") && ($search->id > 0)) { // case only modify
            if ($search->Select($search->id)) $err = "";
        }
        if (!$this->analyze) {
            // update title in finish
            $search->title = $data[3];
            $err = $search->modify();
            $this->tcr[$this->nLine]["err"].= $err;
            
            if (($data[4] != "")) { // specific search
                $err = $search->AddStaticQuery($data[4]);
                $this->tcr[$this->nLine]["err"].= $err;
            }
            
            if ($data[2] != '') { // dirid
                
                /**
                 * @var Dir $dir
                 */
                $dir = new_Doc($this->dbaccess, $data[2]);
                if ($dir->isAlive() && method_exists($dir, "insertDocument")) $dir->insertDocument($search->id);
            }
        }
        $this->nbDoc++;
    }
    /**
     * analyze DOCICON
     * @param array $data line of description file
     */
    protected function doDocIcon(array $data)
    {
        $idoc = new_doc($this->dbaccess, $data[1]);
        if (!$this->analyze) $idoc->changeIcon($data[2]);
        if ($idoc->isAlive()) $this->tcr[$this->nLine]["msg"] = sprintf(_("document %s : set icon to '%s'") , $idoc->title, $data[2]);
        else {
            $this->tcr[$this->nLine]["err"] = sprintf(_("no change icon : document %s not found") , $data[1]);
            $this->tcr[$this->nLine]["action"] = "ignored";
        }
    }
    /**
     * analyze DOCATAG
     * @param array $data line of description file
     */
    protected function doDocAtag(array $data)
    {
        $check = new CheckDocATag();
        $this->tcr[$this->nLine]["err"] = $check->check($data)->getErrors();
        if ($this->tcr[$this->nLine]["err"] && $this->analyze) {
            $this->tcr[$this->nLine]["msg"] = sprintf(_("Element can't be perfectly analyze, some error might occur or be corrected when importing"));
            $this->tcr[$this->nLine]["action"] = "warning";
            return;
        }
        if ($this->tcr[$this->nLine]["err"]) {
            $this->tcr[$this->nLine]["action"] = "ignored";
            return;
        }
        $idoc = new_doc($this->dbaccess, $data[1]);
        
        $i = 4;
        $tags = [];
        while (!empty($data[$i])) {
            $tags[] = $data[$i];
            $i++;
        }
        
        $tagAction = $data[3];
        if (!$tagAction) {
            $tagAction = "ADD";
        }
        
        if (!$this->analyze) {
            if ($tagAction === "SET") {
                $idoc->atags = '';
                if (!$tags) {
                    $err = $idoc->modify(true, array("atags"), true);
                    if ($err) {
                        $this->tcr[$this->nLine]["err"] = $err;
                    }
                }
            }
            foreach ($tags as $tag) {
                if ($tagAction === "DELETE") {
                    $err = $idoc->delATag($tag);
                } else {
                    $err = $idoc->addATag($tag);
                }
                if ($err) {
                    $this->tcr[$this->nLine]["err"] = $err;
                }
            }
        }
        switch ($tagAction) {
            case "ADD":
                $this->tcr[$this->nLine]["msg"] = sprintf(_("Add atags \"%s\"") , implode("\", \"", $tags));
                break;

            case "DELETE":
                $this->tcr[$this->nLine]["msg"] = sprintf(_("Del atags \"%s\"") , implode("\", \"", $tags));
                break;

            case "SET":
                $this->tcr[$this->nLine]["msg"] = sprintf(_("Set atags \"%s\"") , implode("\", \"", $tags));
                break;
        }
    }
    /**
     * analyze ICON
     * @param array $data line of description file
     */
    protected function doIcon(array $data)
    {
        if (empty($data[1])) {
            $this->tcr[$this->nLine]["msg"] = sprintf(_("No Icon specified"));
        } elseif ($this->doc->icon == "") {
            $this->familyIcon = $data[1]; // reported to end section
            $this->tcr[$this->nLine]["msg"] = sprintf(_("set icon to '%s'") , $data[1]);
        } else {
            $this->tcr[$this->nLine]["msg"] = sprintf(_("icon already set. No update allowed"));
        }
    }
    /**
     * analyze DFLDID
     * @param array $data line of description file
     */
    protected function doDfldid(array $data)
    {
        if (!$this->doc) return;
        if (!isset($data[1])) $data[1] = '';
        $check = new CheckDfldid();
        $this->tcr[$this->nLine]["err"] = $check->check($data, $this->doc)->getErrors();
        if ($this->tcr[$this->nLine]["err"] && $this->analyze) {
            $this->tcr[$this->nLine]["msg"] = sprintf(_("Element can't be perfectly analyze, some error might occur or be corrected when importing"));
            $this->tcr[$this->nLine]["action"] = "warning";
            return;
        }
        if (!$this->tcr[$this->nLine]["err"]) {
            $fldid = 0;
            if ($data[1] == "auto") {
                if ($this->doc->dfldid == "") {
                    if (!$this->analyze) {
                        // create auto
                        include_once ("FDL/freedom_util.php");
                        $fldid = createAutoFolder($this->doc);
                        $this->tcr[$this->nLine]["msg"].= sprintf(_("create default folder (id [%d])\n") , $fldid);
                    }
                } else {
                    $fldid = $this->doc->dfldid;
                    $this->tcr[$this->nLine]["msg"] = sprintf(_("default folder already set. Auto ignored"));
                }
            } elseif (is_numeric($data[1])) $fldid = $data[1];
            else $fldid = getIdFromName($this->dbaccess, $data[1], 2);
            $this->doc->dfldid = $fldid;
            $this->tcr[$this->nLine]["msg"].= sprintf(_("set default folder to '%s'") , $data[1]);
        } else {
            $this->tcr[$this->nLine]["action"] = "ignored";
        }
    }
    /**
     * analyze CFLDID
     * @param array $data line of description file
     */
    protected function doCfldid(array $data)
    {
        if (!$this->doc) return;
        
        $check = new CheckCfldid();
        $this->tcr[$this->nLine]["err"] = $check->check($data, $this->doc)->getErrors();
        if ($this->tcr[$this->nLine]["err"] && $this->analyze) {
            $this->tcr[$this->nLine]["msg"] = sprintf(_("Element can't be perfectly analyze, some error might occur or be corrected when importing"));
            $this->tcr[$this->nLine]["action"] = "warning";
            return;
        }
        if (!$this->tcr[$this->nLine]["err"]) {
            if (is_numeric($data[1])) $cfldid = $data[1];
            else $cfldid = getIdFromName($this->dbaccess, $data[1]);
            $this->doc->cfldid = $cfldid;
            $this->tcr[$this->nLine]["msg"] = sprintf(_("set primary folder to '%s'") , $data[1]);
        } else {
            $this->tcr[$this->nLine]["action"] = "ignored";
        }
    }
    /**
     * analyze WID
     * @param array $data line of description file
     */
    protected function doWid(array $data)
    {
        if (!$this->doc) return;
        if (!isset($data[1])) $data[1] = '';
        $check = new CheckWid();
        $this->tcr[$this->nLine]["err"] = $check->check($data, $this->doc)->getErrors();
        if ($this->tcr[$this->nLine]["err"] && $this->analyze) {
            $this->tcr[$this->nLine]["msg"] = sprintf(_("Element can't be perfectly analyze, some error might occur or be corrected when importing"));
            $this->tcr[$this->nLine]["action"] = "warning";
            return;
        }
        if ($this->tcr[$this->nLine]["err"]) {
            $this->tcr[$this->nLine]["action"] = "ignored";
            return;
        }
        if (is_numeric($data[1])) $wid = $data[1];
        else $wid = getIdFromName($this->dbaccess, $data[1], 20);
        if ($data[1]) {
            try {
                $wdoc = new_doc($this->dbaccess, $wid);
                if (!$wdoc->isAlive()) {
                    $this->tcr[$this->nLine]["err"] = sprintf(_("WID : workflow '%s' not found") , $data[1]);
                } else {
                    if (!is_subclass_of($wdoc, "WDoc")) {
                        $this->tcr[$this->nLine]["err"] = sprintf(_("WID : workflow '%s' is not a workflow") , $data[1]);
                    } else {
                        $this->doc->wid = $wdoc->id;
                    }
                }
                $this->tcr[$this->nLine]["msg"] = sprintf(_("set default workflow to '%s'") , $data[1]);
            }
            catch(Exception $e) {
                $this->tcr[$this->nLine]["err"] = sprintf(_("WID : %s") , $e->getMessage());
            }
            if ($this->tcr[$this->nLine]["err"]) $this->tcr[$this->nLine]["action"] = "ignored";
        } else {
            $this->doc->wid = '';
            
            $this->tcr[$this->nLine]["msg"] = _("unset default workflow");
        }
    }
    /**
     * analyze CVID
     * @param array $data line of description file
     */
    protected function doCvid(array $data)
    {
        if (!$this->doc) return;
        $check = new CheckCvid();
        $this->tcr[$this->nLine]["err"] = $check->check($data, $this->doc)->getErrors();
        if ($this->tcr[$this->nLine]["err"] && $this->analyze) {
            $this->tcr[$this->nLine]["msg"] = sprintf(_("Element can't be perfectly analyze, some error might occur or be corrected when importing"));
            $this->tcr[$this->nLine]["action"] = "warning";
            return;
        }
        if ($this->tcr[$this->nLine]["err"]) {
            $this->tcr[$this->nLine]["action"] = "ignored";
            return;
        }
        
        if (is_numeric($data[1])) $cvid = $data[1];
        else $cvid = getIdFromName($this->dbaccess, $data[1], 28);
        
        if ($data[1]) {
            try {
                $cvdoc = new_doc($this->dbaccess, $cvid);
                if (!$cvdoc->isAlive()) {
                    $this->tcr[$this->nLine]["err"] = sprintf(_("CVID : view control '%s' not found") , $data[1]);
                } else {
                    $this->doc->ccvid = $cvdoc->id;
                }
                $this->tcr[$this->nLine]["msg"] = sprintf(_("set default view control to '%s'") , $data[1]);
            }
            catch(Exception $e) {
                $this->tcr[$this->nLine]["err"] = sprintf(_("CVID : %s") , $e->getMessage());
            }
            if ($this->tcr[$this->nLine]["err"]) $this->tcr[$this->nLine]["action"] = "ignored";
        } else {
            $this->doc->ccvid = '';
            
            $this->tcr[$this->nLine]["msg"] = _("unset default view control");
        }
    }
    /**
     * analyze CLASS
     * @param array $data line of description file
     */
    protected function doClass(array $data)
    {
        if (!$this->doc) return;
        $data = array_map("trim", $data);
        $check = new CheckClass();
        $this->tcr[$this->nLine]["err"] = $check->check($data, $this->doc)->getErrors();
        if ($this->tcr[$this->nLine]["err"] && $this->analyze) {
            $this->tcr[$this->nLine]["msg"] = sprintf(_("Element can't be perfectly analyze, some error might occur or be corrected when importing"));
            $this->tcr[$this->nLine]["action"] = "warning";
            return;
        }
        if ($this->tcr[$this->nLine]["err"]) {
            $this->tcr[$this->nLine]["action"] = "ignored";
            return;
        }
        $this->doc->classname = $data[1];
    }
    /**
     * analyze METHOD
     * @param array $data line of description file
     */
    protected function doMethod(array $data)
    {
        if (!$this->doc) return;
        $data = array_map("trim", $data);
        $check = new CheckMethod();
        $this->tcr[$this->nLine]["err"] = $check->check($data, $this->doc)->getErrors();
        if ($this->tcr[$this->nLine]["err"] && $this->analyze) {
            $this->tcr[$this->nLine]["msg"] = sprintf(_("Element can't be perfectly analyze, some error might occur or be corrected when importing"));
            $this->tcr[$this->nLine]["action"] = "warning";
            return;
        }
        if ($this->tcr[$this->nLine]["err"]) {
            $this->tcr[$this->nLine]["action"] = "ignored";
            return;
        }
        
        if (!isset($data[1])) $aMethod = null;
        else $aMethod = $data[1];
        $s1 = ($aMethod) ? $aMethod[0] : '';
        if (($s1 == "+") || ($s1 == "*")) {
            if ($s1 == "*") $method = $aMethod;
            else $method = substr($aMethod, 1);
            
            if ($this->doc->methods == "") {
                $this->doc->methods = $method;
            } else {
                $this->doc->methods.= "\n$method";
                // not twice
                $tmeth = explode("\n", $this->doc->methods);
                $tmeth = array_unique($tmeth);
                $this->doc->methods = implode("\n", $tmeth);
            }
        } else $this->doc->methods = $aMethod;
        
        $this->tcr[$this->nLine]["msg"] = sprintf(_("change methods to '%s'") , $this->doc->methods);
        if ($this->doc->methods) {
            $tmethods = explode("\n", $this->doc->methods);
            foreach ($tmethods as $method) {
                $fileMethod = ($method && $method[0] == '*') ? substr($method, 1) : $method;
                if (!file_exists(sprintf("FDL/%s", $fileMethod))) {
                    $this->tcr[$this->nLine]["err"].= sprintf("Method file '%s' not found.", $fileMethod);
                }
            }
        }
        if ($this->tcr[$this->nLine]["err"]) $this->tcr[$this->nLine]["action"] = "ignored";
    }
    /**
     * analyze CPROFID
     * @param array $data line of description file
     */
    protected function doCprofid(array $data)
    {
        if (!$this->doc) return;
        $check = new CheckCprofid();
        $this->tcr[$this->nLine]["err"] = $check->check($data, $this->doc)->getErrors();
        if ($this->tcr[$this->nLine]["err"] && $this->analyze) {
            $this->tcr[$this->nLine]["msg"] = sprintf(_("Element can't be perfectly analyze, some error might occur or be corrected when importing"));
            $this->tcr[$this->nLine]["action"] = "warning";
            return;
        }
        if ($this->tcr[$this->nLine]["err"]) {
            $this->tcr[$this->nLine]["action"] = "ignored";
            return;
        }
        
        if (is_numeric($data[1])) $pid = $data[1];
        else $pid = getIdFromName($this->dbaccess, $data[1], 3);
        $this->doc->cprofid = $pid;
        $this->tcr[$this->nLine]["msg"] = sprintf(_("change default creation profile id  to '%s'") , $data[1]);
    }
    /**
     * analyze PROFID
     * @param array $data line of description file
     */
    protected function doProfid(array $data)
    {
        if (!$this->doc) return;
        
        $check = new CheckProfid();
        $this->tcr[$this->nLine]["err"] = $check->check($data)->getErrors();
        if ($this->tcr[$this->nLine]["err"] && $this->analyze) {
            $this->tcr[$this->nLine]["msg"] = sprintf(_("Element can't be perfectly analyze, some error might occur or be corrected when importing"));
            $this->tcr[$this->nLine]["action"] = "warning";
            return;
        }
        if ($this->tcr[$this->nLine]["err"]) {
            $this->tcr[$this->nLine]["action"] = "ignored";
            return;
        }
        if (is_numeric($data[1])) $pid = $data[1];
        else $pid = getIdFromName($this->dbaccess, $data[1], 3);
        $this->doc->setProfil($pid); // change profile
        $this->tcr[$this->nLine]["msg"] = sprintf(_("change profile id  to '%s'") , $data[1]);
    }
    /**
     * analyze INITIAL
     * @param array $data line of description file
     */
    protected function doInitial(array $data)
    {
        
        if (!$this->doc) return;
        $check = new CheckInitial();
        $this->tcr[$this->nLine]["err"] = $check->check($data, $this->doc)->getErrors();
        if ($this->tcr[$this->nLine]["err"] && $this->analyze) {
            $this->tcr[$this->nLine]["msg"] = sprintf(_("Element can't be perfectly analyze, some error might occur or be corrected when importing"));
            $this->tcr[$this->nLine]["action"] = "warning";
            return;
        }
        if ($this->tcr[$this->nLine]["err"]) {
            $this->tcr[$this->nLine]["action"] = "ignored";
            return;
        }
        
        if (!isset($data[2])) $data[2] = '';
        $attrid = trim(strtolower($data[1]));
        $newValue = $data[2];
        $opt = (isset($data[3])) ? trim(strtolower($data[3])) : null;
        $force = (str_replace(" ", "", $opt) == "force=yes");
        $params = $this->doc->getOwnParams();
        $previousValue = isset($params[$attrid]) ? $params[$attrid] : null;
        if ((!empty($previousValue)) && (!$force)) {
            // reset default
            $this->tcr[$this->nLine]["msg"] = sprintf("keep default value %s : %s. No use %s", $attrid, $previousValue, $data[2]);
        } else {
            
            if ($force || ($previousValue === null)) {
                $this->doc->setParam($attrid, $newValue, false);
                $this->tcr[$this->nLine]["msg"] = "reset default parameter";
            }
            $this->tcr[$this->nLine]["msg"].= sprintf(_("add default value %s %s") , $attrid, $data[2]);
        }
    }
    /**
     * analyze DEFAULT
     * @param array $data line of description file
     */
    protected function doDefault(array $data)
    {
        
        if (!$this->doc) return;
        $check = new CheckDefault();
        $this->tcr[$this->nLine]["err"] = $check->check($data, $this->doc)->getErrors();
        if ($this->tcr[$this->nLine]["err"] && $this->analyze) {
            $this->tcr[$this->nLine]["msg"] = sprintf(_("Element can't be perfectly analyze, some error might occur or be corrected when importing"));
            $this->tcr[$this->nLine]["action"] = "warning";
            return;
        }
        if ($this->tcr[$this->nLine]["err"]) {
            $this->tcr[$this->nLine]["action"] = "ignored";
            return;
        }
        
        if (!isset($data[2])) $data[2] = '';
        $attrid = trim(strtolower($data[1]));
        $defv = $data[2];
        $opt = (isset($data[3])) ? trim(strtolower($data[3])) : null;
        $force = (str_replace(" ", "", $opt) == "force=yes");
        $ownDef = $this->doc->getOwnDefValues();
        if ((!empty($ownDef[$attrid])) && (!$force)) {
            // reset default
            $this->tcr[$this->nLine]["msg"] = sprintf("keep default value %s : %s. No use %s", $attrid, $ownDef[$attrid], $data[2]);
        } else {
            $this->doc->setDefValue($attrid, $defv, false);
            if ($force || (!$this->doc->getParameterRawValue($attrid))) {
                // TODO : not really exact here : must verify if it is really a parameter
                //$this->doc->setParam($attrid, $defv);
                //$this->tcr[$this->nLine]["msg"] = "reset default parameter";
                
            }
            $this->tcr[$this->nLine]["msg"].= sprintf(_("add default value %s %s") , $attrid, $data[2]);
        }
    }
    /**
     * analyze ACCESS
     * @param array $data line of description file
     */
    protected function doAccess(array $data)
    {
        
        global $action;
        $check = new CheckAccess();
        $this->tcr[$this->nLine]["err"] = $check->check($data, $action)->getErrors();
        if ($this->tcr[$this->nLine]["err"] && $this->analyze) {
            $this->tcr[$this->nLine]["msg"] = sprintf(_("Element can't be perfectly analyze, some error might occur or be corrected when importing"));
            $this->tcr[$this->nLine]["action"] = "warning";
            return;
        }
        if ($this->tcr[$this->nLine]["err"]) {
            $this->tcr[$this->nLine]["action"] = "ignored";
            return;
        }
        if (ctype_digit(trim($data[1]))) $wid = trim($data[1]);
        else {
            $pid = getIdFromName($this->dbaccess, trim($data[1]));
            $tdoc = getTDoc($this->dbaccess, $pid);
            $wid = getv($tdoc, "us_whatid");
        }
        $idapp = $action->parent->GetIdFromName($data[2]);
        if ($idapp == 0) {
            $this->tcr[$this->nLine]["err"] = sprintf(_("%s application not exists") , $data[2]);
        } else {
            $this->tcr[$this->nLine]["msg"] = "user #$wid";
            array_shift($data);
            array_shift($data);
            array_shift($data);
            $q = new QueryDb("", "Acl");
            $q->AddQuery("id_application=$idapp");
            $la = $q->Query(0, 0, "TABLE");
            if (!$la) {
                $this->tcr[$this->nLine]["err"] = sprintf(_("%s application has no aclss") , $data[2]);
            } else {
                $tacl = array();
                foreach ($la as $k => $v) {
                    $tacl[$v["name"]] = $v["id"];
                }
                
                $p = new Permission();
                $p->id_user = $wid;
                $p->id_application = $idapp;
                foreach ($data as $v) {
                    $v = trim($v);
                    if ($v != "") {
                        if ($this->analyze) {
                            $this->tcr[$this->nLine]["msg"].= "\n" . sprintf(_("try add acl %s") , $v);
                            $this->tcr[$this->nLine]["action"] = "added";
                            continue;
                        }
                        if (substr($v, 0, 1) == '-') {
                            $aclneg = true;
                            $v = substr($v, 1);
                        } else $aclneg = false;
                        if (isset($tacl[$v])) {
                            $p->id_acl = $tacl[$v];
                            if ($aclneg) $p->id_acl = - $p->id_acl;
                            $p->deletePermission($p->id_user, $p->id_application, $p->id_acl);
                            $err = $p->Add();
                            if ($err) $this->tcr[$this->nLine]["err"].= "\n$err";
                            else {
                                if ($aclneg) $this->tcr[$this->nLine]["msg"].= "\n" . sprintf(_("add negative acl %s") , $v);
                                else $this->tcr[$this->nLine]["msg"].= "\n" . sprintf(_("add acl %s") , $v);
                            }
                        } else {
                            $this->tcr[$this->nLine]["err"].= "\n" . sprintf(_("unknow acl %s") , $v);
                        }
                    }
                }
            }
        }
        if ($this->tcr[$this->nLine]["err"]) $this->tcr[$this->nLine]["action"] = "ignored";
    }
    /**
     * analyze TAGABLE
     * @param array $data tagable parameter
     */
    protected function doTagable(array $data)
    {
        if (!$this->doc) {
            return;
        }
        if (class_exists("CheckTagable")) {
            /** @noinspection PhpUndefinedClassInspection
             * Defined in dynacase-tags module
             */
            $check = new CheckTagable();
        } else {
            $this->tcr[$this->nLine]["err"] = ErrorCode::getError('PROP0102', "TAGABLE", "dynacase-tags");
            $this->tcr[$this->nLine]["action"] = "ignored";
            error_log("ERROR:" . $this->tcr[$this->nLine]["err"]);
            return;
        }
        /**
         * @var CheckData $check
         */
        $this->tcr[$this->nLine]["err"] = $check->check($data)->getErrors();
        if ($this->tcr[$this->nLine]["err"] && $this->analyze) {
            $this->tcr[$this->nLine]["msg"] = sprintf(_("Element can't be perfectly analyze, some error might occur or be corrected when importing"));
            $this->tcr[$this->nLine]["action"] = "warning";
            return;
        }
        if ($this->tcr[$this->nLine]["err"]) {
            $this->tcr[$this->nLine]["action"] = "ignored";
            return;
        }
        $this->doc->tagable = $data[1] === "no" ? "" : $data[1];
        $this->tcr[$this->nLine]["msg"] = sprintf(_("change tagable parameter to '%s'") , $this->doc->tagable);
    }
    /**
     * analyze PROFIL
     * @param array $data line of description file
     */
    protected function doProfil(array $data)
    {
        $check = new CheckProfil();
        $this->tcr[$this->nLine]["err"] = $check->check($data)->getErrors();
        if ($this->tcr[$this->nLine]["err"] && $this->analyze) {
            $this->tcr[$this->nLine]["msg"] = sprintf(_("Element can't be perfectly analyze, some error might occur or be corrected when importing"));
            $this->tcr[$this->nLine]["action"] = "warning";
            return;
        }
        if ($this->tcr[$this->nLine]["err"]) {
            $this->tcr[$this->nLine]["action"] = "ignored";
            return;
        }
        
        if (ctype_digit(trim($data[1]))) $pid = trim($data[1]);
        else $pid = getIdFromName($this->dbaccess, trim($data[1]));
        
        if (!($pid > 0)) $this->tcr[$this->nLine]["err"] = sprintf(_("profil id unkonow %s") , $data[1]);
        else {
            clearCacheDoc(); // need to reset computed acls
            
            /**
             * @var PDoc $pdoc
             */
            $pdoc = new_Doc($this->dbaccess, $pid);
            if ($pdoc->isAlive()) {
                $this->tcr[$this->nLine]["msg"] = sprintf(_("change profil %s") , $data[1]);
                $this->tcr[$this->nLine]["action"] = "modprofil";
                if ($this->analyze) return;
                $fpid = $data[2];
                if (($fpid != "") && (!is_numeric($fpid))) $fpid = getIdFromName($this->dbaccess, $fpid);
                if ($fpid != "") {
                    // profil related of other profil
                    $pdoc->setProfil($fpid);
                    $this->tcr[$this->nLine]["err"] = $pdoc->modify(false, array(
                        "profid"
                    ) , true);
                } else {
                    // specific profil
                    if ($pdoc->profid != $pid) {
                        $pdoc->setProfil($pid);
                        $pdoc->SetControl(false);
                        $pdoc->disableEditControl(); // need because new profil is not enable yet
                        $this->tcr[$this->nLine]["err"] = $pdoc->modify();
                    }
                    
                    $defaultUseType = trim($data[2]);
                    $optprof = strtoupper(trim($data[3]));
                    $initialPerms = array();
                    $profilingHasChanged = false;
                    if ($optprof == "RESET") {
                        $pdoc->removeControl();
                        $this->tcr[$this->nLine]["msg"].= "\n" . sprintf(_("reset profil %s") , $pid);
                    } elseif ($optprof == "SET") {
                        $initialPerms = array_merge(DocPerm::getPermsForDoc($pdoc->id) , DocPermExt::getPermsForDoc($pdoc->id));
                        $pdoc->removeControl();
                        $this->tcr[$this->nLine]["msg"].= "\n" . sprintf(_("set profile %s") , $pid);
                    }
                    $tacls = array_slice($data, 2);
                    foreach ($tacls as $acl) {
                        if (preg_match("/([^=]+)=(.*)/", $acl, $reg)) {
                            $tuid = explode(",", $reg[2]);
                            $aclname = trim($reg[1]);
                            
                            $perr = "";
                            if ($optprof == "DELETE") {
                                foreach ($tuid as $uid) {
                                    $perr.= $pdoc->delControl($this->getProfilUid($defaultUseType, $uid) , $aclname);
                                    $this->tcr[$this->nLine]["msg"].= "\n" . sprintf(_("delete %s for %s") , $aclname, $uid);
                                }
                            } else { // the "ADD" by default
                                foreach ($tuid as $uid) {
                                    $perr.= $pdoc->addControl($this->getProfilUid($defaultUseType, $uid) , $aclname);
                                    $this->tcr[$this->nLine]["msg"].= "\n" . sprintf(_("add %s for %s") , $aclname, $uid);
                                }
                            }
                            $this->tcr[$this->nLine]["err"] = $perr;
                        }
                    }
                    if ($optprof == "SET") {
                        $newPerms = array_merge(DocPerm::getPermsForDoc($pdoc->id) , DocPermExt::getPermsForDoc($pdoc->id));
                        $profilingHasChanged = (serialize($newPerms) != serialize($initialPerms));
                    }
                    if ($optprof == "RESET" || ($optprof == "SET" && $profilingHasChanged)) {
                        // need reset all documents
                        $pdoc->addHistoryEntry(_('Recomputing profiled documents') , DocHisto::INFO, 'RECOMPUTE_PROFILED_DOCUMENT');
                        $pdoc->recomputeProfiledDocument();
                    }
                }
            } else {
                $this->tcr[$this->nLine]["err"] = sprintf(_("profil id unknow %s") , $data[1]);
            }
        }
        if ($this->tcr[$this->nLine]["err"]) $this->tcr[$this->nLine]["action"] = "ignored";
    }
    
    protected function getProfilUid($defaultReferenceType, $reference)
    {
        
        $reference = trim($reference);
        $this->extractAccount($defaultReferenceType, $reference, $type, $value);
        switch ($type) {
            case ':useAccount':
                return $this->getUserId($value);
                break;

            case ':useAttribute':
                return self::attributePrefix . $value;
                break;

            case ':useDocument':
                return self::documentPrefix . $value;
                break;

            default:
                return $value;
        }
    }
    
    private function extractAccount($defaultReferenceType, $reference, &$type, &$value)
    {
        if (preg_match('/^attribute\((.*)\)$/', $reference, $reg)) {
            $type = ":useAttribute";
            $value = trim($reg[1]);
        } elseif (preg_match('/^account\((.*)\)$/', $reference, $reg)) {
            $type = ":useAccount";
            $value = trim($reg[1]);
        } elseif (preg_match('/^document\((.*)\)$/', $reference, $reg)) {
            $type = ":useDocument";
            $value = trim($reg[1]);
        } else {
            $value = $reference;
            $type = $defaultReferenceType;
        }
    }
    
    protected function getUserId($login)
    {
        $login = mb_strtolower($login);
        if (!isset($this->userIds[$login])) {
            simpleQuery("", sprintf("select id from users where login='%s'", pg_escape_string($login)) , $uid, true, true);
            if (!$uid) {
                throw new \Dcp\Exception("PRFL0204", $login);
            }
            $this->userIds[$login] = $uid;
        }
        return $this->userIds[$login];
    }
    /**
     * analyze KEYS
     * @param array $data line of description file
     */
    protected function doKeys(array $data)
    {
        $check = new CheckKeys();
        $this->tcr[$this->nLine]["err"] = $check->check($data)->getErrors();
        if ($this->tcr[$this->nLine]["err"] && $this->analyze) {
            $this->tcr[$this->nLine]["msg"] = sprintf(_("Element can't be perfectly analyze, some error might occur or be corrected when importing"));
            $this->tcr[$this->nLine]["action"] = "warning";
            return;
        }
        if ($this->tcr[$this->nLine]["err"]) {
            $this->tcr[$this->nLine]["action"] = "ignored";
            return;
        }
        if (is_numeric($data[1])) $orfromid = $data[1];
        else $orfromid = getFamIdFromName($this->dbaccess, $data[1]);
        
        $tkeys[$orfromid] = getOrder($data);
        if (($tkeys[$orfromid][0] == "") || (count($tkeys[$orfromid]) == 0)) {
            $this->tcr[$this->nLine]["err"] = sprintf(_("error in import keys : %s") , implode(" - ", $tkeys[$orfromid]));
            unset($tkeys[$orfromid]);
            $this->tcr[$this->nLine]["action"] = "ignored";
        } else {
            $this->tcr[$this->nLine]["msg"] = sprintf(_("new import keys : %s") , implode(" - ", $tkeys[$orfromid]));
        }
    }
    /**
     * analyze ORDER
     * @param array $data line of description file
     */
    protected function doOrder(array $data)
    {
        $check = new CheckOrder();
        $this->tcr[$this->nLine]["err"] = $check->check($data)->getErrors();
        $famName = $check->getParsedFamName();
        if ($this->tcr[$this->nLine]["err"]) {
            if ($famName !== false) {
                $this->badOrderErrors[$famName] = $this->tcr[$this->nLine]["err"];
            }
            if ($this->analyze) {
                $this->tcr[$this->nLine]["msg"] = sprintf(_("Element can't be perfectly analyze, some error might occur or be corrected when importing"));
                $this->tcr[$this->nLine]["action"] = "warning";
            } else {
                $this->tcr[$this->nLine]["action"] = "ignored";
            }
            return;
        }
        if ($famName !== false && isset($this->badOrderErrors[$famName])) {
            unset($this->badOrderErrors[$famName]);
        }
        if (is_numeric($data[1])) $orfromid = $data[1];
        else $orfromid = getFamIdFromName($this->dbaccess, $data[1]);
        
        $this->colOrders[$orfromid] = getOrder($data);
        $this->tcr[$this->nLine]["msg"] = sprintf(_("new column order %s") , implode(" - ", $this->colOrders[$orfromid]));
    }
    /**
     * analyze LDAPMAP
     * @param array $data line of description file
     */
    protected function doLdapmap(array $data)
    {
        $err = '';
        if (is_numeric($data[1])) $fid = $data[1];
        else $fid = getFamIdFromName($this->dbaccess, $data[1]);
        $aid = (trim($data[2]));
        $index = $data[5];
        $oa = new DocAttrLDAP($this->dbaccess, array(
            $fid,
            $aid,
            $index
        ));
        //	print_r2($oa);
        if (substr($data[2], 0, 2) == "::") $oa->ldapname = $data[2];
        else $oa->ldapname = strtolower(trim($data[2]));
        
        $oa->ldapclass = trim($data[4]);
        $oa->famid = $fid;
        $oa->ldapmap = $data[3];
        $oa->index = $index;
        $oa->ldapname = $aid;
        
        if ($oa->isAffected()) {
            if (!$this->analyze) $err = $oa->modify();
            $this->tcr[$this->nLine]["msg"] = sprintf(_("LDAP Attribute modified to %s %s") , $oa->ldapname, $oa->ldapmap);
            $this->tcr[$this->nLine]["action"] = "updated";
        } else {
            if (!$this->analyze) $err = $oa->add();
            
            $this->tcr[$this->nLine]["msg"] = sprintf(_("LDAP Attribute added to %s %s") , $oa->ldapname, $oa->ldapmap);
            $this->tcr[$this->nLine]["action"] = "added";
        }
        $this->tcr[$this->nLine]["err"].= $err;
        if ($this->tcr[$this->nLine]["err"]) $this->tcr[$this->nLine]["action"] = "ignored";
    }
    /**
     * Verify compatibility between 2 type
     * @param string $curType
     * @param string $newType
     * @return bool
     */
    protected function isTypeCompatible($curType, $newType)
    {
        if ($curType == $newType) return true;
        $tc = array(
            "docid" => "account",
            "text" => "longtext",
            "longtext" => "htmltext",
            "file" => "image",
            "image" => "file",
            "integer" => "int", // old compatibility
            "float" => "double"
            // old compatibility
            
        );
        return isset($tc[$curType]) && ($tc[$curType] == $newType);
    }
    /**
     * analyze IATTR
     * @param array $data line of description file
     */
    protected function doAttr(array $data)
    {
        
        if (!$this->doc) return;
        $check = new CheckAttr();
        $this->tcr[$this->nLine]["err"] = $check->check($data, $this->doc)->getErrors();
        if ($this->tcr[$this->nLine]["err"] && $this->analyze) {
            $this->tcr[$this->nLine]["msg"] = sprintf(_("Element can't be perfectly analyze, some error might occur or be corrected when importing"));
            $this->tcr[$this->nLine]["action"] = "warning";
            return;
        }
        if ($this->tcr[$this->nLine]["err"]) {
            $this->tcr[$this->nLine]["action"] = "ignored";
            return;
        }
        
        if (!$this->structAttr) {
            $this->structAttr = new StructAttribute();
        }
        $this->structAttr->set($data);
        
        if (trim($data[1]) == '') {
            $this->tcr[$this->nLine]["err"].= sprintf(_("attr key is empty"));
        } else {
            $modattr = ($data[0] == "MODATTR");
            if ($data[0] == "MODATTR") $this->structAttr->id = ':' . $this->structAttr->id; // to mark the modified
            $this->tcr[$this->nLine]["msg"].= sprintf(_("update %s attribute") , $this->structAttr->id);
            if ($this->analyze) return;
            $oattr = new DocAttr($this->dbaccess, array(
                $this->doc->id,
                strtolower($this->structAttr->id)
            ));
            
            if ($oattr->isAffected()) {
                // modification of type is forbidden
                $curType = trim(strtok($oattr->type, '('));
                $newType = trim(strtok($this->structAttr->type, '('));
                if ($curType != $newType && (!$this->isTypeCompatible($curType, $newType))) {
                    $this->tcr[$this->nLine]["err"].= sprintf("cannot change attribute %s type definition from %s to %s", $this->structAttr->id, $curType, $newType);
                }
                // modification of target is forbidden
                if (($data[0] == "PARAM") && ($oattr->usefor != 'Q')) {
                    $this->tcr[$this->nLine]["err"].= sprintf("cannot change attribute declaration to PARAM for %s", $this->structAttr->id);
                } elseif (($data[0] == "ATTR") && ($oattr->usefor == 'Q')) {
                    $this->tcr[$this->nLine]["err"].= sprintf("cannot change attribute declaration to ATTR for %s", $this->structAttr->id);
                }
            }
            
            if (!$this->tcr[$this->nLine]["err"]) {
                if ($data[0] == "PARAM") $oattr->usefor = 'Q'; // parameters
                elseif ($data[0] == "OPTION") $oattr->usefor = 'O'; // options
                else $oattr->usefor = 'N'; // normal
                $oattr->docid = $this->doc->id;
                $oattr->id = trim(strtolower($this->structAttr->id));
                
                $oattr->frameid = trim(strtolower($this->structAttr->setid));
                $oattr->labeltext = $this->structAttr->label;
                
                $oattr->title = ($this->structAttr->istitle == "Y") ? "Y" : "N";
                
                $oattr->abstract = ($this->structAttr->isabstract == "Y") ? "Y" : "N";
                if ($modattr) $oattr->abstract = $this->structAttr->isabstract;
                
                $oattr->type = trim($this->structAttr->type);
                
                $oattr->ordered = $this->structAttr->order;
                $oattr->visibility = $this->structAttr->visibility;
                $oattr->needed = ($this->structAttr->isneeded == "Y") ? "Y" : "N";
                if ($modattr) {
                    $oattr->title = $this->structAttr->istitle;
                    $oattr->needed = $this->structAttr->isneeded;
                }
                $oattr->link = $this->structAttr->link;
                $oattr->phpfile = $this->structAttr->phpfile;
                if ($this->structAttr->elink) $oattr->elink = $this->structAttr->elink;
                else $oattr->elink = '';
                if ($this->structAttr->constraint) $oattr->phpconstraint = $this->structAttr->constraint;
                else $oattr->phpconstraint = '';
                if ($this->structAttr->options) $oattr->options = $this->structAttr->options;
                else $oattr->options = '';
                
                if (((($this->structAttr->phpfile != "") && ($this->structAttr->phpfile != "-")) || (($this->structAttr->type != "enum") && ($this->structAttr->type != "enumlist"))) || ($oattr->phpfunc == "") || (strpos($oattr->options, "system=yes") !== false)) {
                    // don't modify  enum possibilities if exists and non system
                    $oattr->phpfunc = $this->structAttr->phpfunc;
                    if ($oattr->type == "enum") {
                        if (strlen($this->structAttr->phpfile) < 2) {
                            // don't record if enum comes from function
                            $reset = (strpos($oattr->options, "system=yes") !== false);
                            $this->recordEnum($this->doc->id, $oattr->id, $this->structAttr->phpfunc, $reset);
                            //$oattr->phpfunc = "-";
                            
                        }
                    }
                }
                if ($oattr->ordered && !is_numeric($oattr->ordered)) {
                    $oattr->options.= ($oattr->options) ? "|" : "";
                    $oattr->options.= sprintf("relativeOrder=%s", $oattr->ordered);
                    $oattr->ordered = $this->nLine;
                }
                if ($oattr->isAffected()) $err = $oattr->Modify();
                else $err = $oattr->Add();
                $this->addImportedAttribute($this->doc->id, $oattr);
                
                $this->tcr[$this->nLine]["err"].= $err;
            }
        }
        if ($this->tcr[$this->nLine]["err"]) $this->tcr[$this->nLine]["action"] = "ignored";
    }
    /**
     * @param int $famid family identifier
     * @param string $attrid attribute identifier
     * @param string $phpfunc enum flat description
     * @param bool $reset set to true to delete old items before recorded
     * @return string error message
     */
    public static function recordEnum($famid, $attrid, $phpfunc, $reset = false)
    {
        static $oe = null;
        
        $err = '';
        if ($oe === null) $oe = new DocEnum();
        $enums = array();
        EnumAttributeTools::flatEnumNotationToEnumArray($phpfunc, $enums);
        $oe->famid = $famid;
        $oe->attrid = $attrid;
        $oe->eorder = 0;
        if ($reset) {
            $sql = sprintf("delete from docenum where famid='%s' and attrid='%s'", pg_escape_string($famid) , pg_escape_string($attrid));
            simpleQuery('', $sql);
        }
        
        foreach ($enums as $itemKey => $itemLabel) {
            $oe->label = $itemLabel;
            $oe->eorder++;
            $antiItemKey = str_replace("\\.", "--dot--", $itemKey);
            if (strpos($antiItemKey, '.') !== false) {
                $tkeys = explode(".", $itemKey);
                $oe->key = array_pop($tkeys);
                $oe->parentkey = array_pop($tkeys);
            } else {
                
                $oe->key = str_replace("\\.", ".", $itemKey);
                
                $oe->parentkey = '';
            }
            $err = '';
            if ($oe->exists()) {
                // $err=$oe->add();
                // " skipped [$itemKey]";
                
            } else {
                // " added  [$itemKey]";
                $err.= $oe->add();
            }
        }
        return $err;
    }
    /**
     * analyze IATTR
     * @param array $data line of description file
     */
    protected function doIattr(array $data)
    {
        if (!$this->doc) return;
        // import attribute definition from another family
        $err = '';
        $fiid = $data[3];
        if (!is_numeric($fiid)) $fiid = getFamIdFromName($this->dbaccess, $fiid);
        $fi = new_Doc($this->dbaccess, $fiid);
        if ($fi->isAffected()) {
            $fa = $fi->getAttribute($data[1]);
            if ($fa) {
                $oattri = new DocAttr($this->dbaccess, array(
                    $fiid,
                    strtolower($data[1])
                ));
                $oattr = new DocAttr($this->dbaccess, array(
                    $this->doc->id,
                    strtolower($data[1])
                ));
                $oattri->docid = $this->doc->id;
                $this->tcr[$this->nLine]["msg"] = sprintf(_("copy attribute %s from %s") , $data[1], $data[3]);
                if (!$this->analyze) {
                    if ($oattr->isAffected()) {
                        $err = $oattri->modify();
                    } else {
                        $oattri->id = strtolower($data[1]);
                        $err = $oattri->add();
                    }
                    $this->tcr[$this->nLine]["err"] = $err;
                }
                
                if (($err == "") && (strtolower(get_class($fa)) == "fieldsetattribute")) {
                    $frameid = $fa->id;
                    // import attributes included in fieldset
                    foreach ($fi->attributes->attr as $k => $v) {
                        if (strtolower(get_class($v)) == "normalattribute") {
                            
                            if (($v->fieldSet->id == $frameid) || ($v->fieldSet->fieldSet->id == $frameid)) {
                                $this->tcr[$this->nLine]["msg"].= "\n" . sprintf(_("copy attribute %s from %s") , $v->id, $data[3]);
                                $oattri = new DocAttr($this->dbaccess, array(
                                    $fiid,
                                    $v->id
                                ));
                                $oattr = new DocAttr($this->dbaccess, array(
                                    $this->doc->id,
                                    $v->id
                                ));
                                $oattri->docid = $this->doc->id;
                                if (!$this->analyze) {
                                    if ($oattr->isAffected()) {
                                        $err = $oattri->modify();
                                    } else {
                                        $oattri->id = $v->id;
                                        $err = $oattri->add();
                                    }
                                    $this->tcr[$this->nLine]["err"].= $err;
                                }
                            }
                        }
                    }
                }
            }
        }
        if ($this->tcr[$this->nLine]["err"]) $this->tcr[$this->nLine]["action"] = "ignored";
    }
    /**
     * analyze PROP
     * @param array $data line of description file
     */
    protected function doProp($data)
    {
        $check = new CheckProp();
        $this->tcr[$this->nLine]["err"] = $check->check($data, $this->doc)->getErrors();
        if ($this->tcr[$this->nLine]["err"] && $this->analyze) {
            $this->tcr[$this->nLine]["msg"] = sprintf(_("Element can't be perfectly analyze, some error might occur or be corrected when importing"));
            $this->tcr[$this->nLine]["action"] = "warning";
            return;
        }
        if ($this->tcr[$this->nLine]["err"]) {
            $this->tcr[$this->nLine]["action"] = "ignored";
            return;
        }
        
        $propName = $check->propName;
        $values = $check->parameters;
        
        if ($this->analyze) {
            return;
        }
        
        foreach ($values as $value) {
            $pName = $value['name'];
            $pValue = $value['value'];
            if (!$this->doc->setPropertyParameter($propName, $pName, $pValue)) {
                $this->tcr[$this->nLine]["err"].= sprintf(_("error storing configuration property (%s, %s, %s)") , $propName, $pName, $pValue);
                return;
            }
        }
        if ($this->tcr[$this->nLine]["err"]) $this->tcr[$this->nLine]["action"] = "ignored";
    }
    protected function addImportedAttribute($famId, DocAttr & $oa)
    {
        if (!isset($this->importedAttribute[$famId])) {
            $this->importedAttribute[$famId] = array();
        }
        $this->importedAttribute[$famId][$oa->id] = $oa;
    }
    public function getImportedAttribute($famId, $attrId)
    {
        if (isset($this->importedAttribute[$famId][$attrId])) {
            return $this->importedAttribute[$famId][$attrId];
        }
        return false;
    }
}

