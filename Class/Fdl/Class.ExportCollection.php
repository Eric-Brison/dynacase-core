<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp;
class ExportCollection
{
    /**
     * @var bool Indicate if Profile must be exported also
     */
    protected $exportProfil = false;
    
    protected $exportFiles = false;
    
    protected $exportDocumentNumericIdentiers = false;
    
    const utf8Encoding = "utf-8";
    const latinEncoding = "iso8859-15";
    protected $outputFileEncoding = self::utf8Encoding;
    
    protected $useUserColumnParameter = false;
    
    const csvRawOutputFormat = "I";
    const csvRawOnlyDataOutputFormat = "R";
    const csvDisplayValueOutputFormat = "F";
    const xmlArchiveOutputFormat = "X";
    const xmlFileOutputFormat = "Y";
    
    protected $outputFormat = self::csvRawOutputFormat;
    protected $verifyAttributeAccess = false;
    
    protected $cvsSeparator = ";";
    protected $cvsEnclosure = "";
    protected $profileAccountType = ExportDocument::useAclAccountType;
    
    protected $outputFilePath = '';
    /**
     * @var string status use to show progress
     */
    protected $exportStatusId = '';
    /**
     * @var \DocCollection $collectionDocument
     */
    protected $collectionDocument = null;
    /**
     * @var \DocumentList
     */
    protected $documentList = null;
    /**
     * @var resource output file handler
     */
    protected $outHandler = null;
    protected $moreDocuments = array();
    protected $familyData = array();
    /**
     * If true, attribute with "I" visibility are not returned
     * @param boolean $verifyAttributeAccess
     */
    public function setVerifyAttributeAccess($verifyAttributeAccess)
    {
        $this->verifyAttributeAccess = $verifyAttributeAccess;
    }
    /**
     * @param string $cvsEnclosure
     */
    public function setCvsEnclosure($cvsEnclosure)
    {
        $this->cvsEnclosure = $cvsEnclosure;
    }
    /**
     * Use document collection (folder or searcch) to export documents
     * @see ExportCollection::setDocumentlist
     * @param \DocCollection $collectionDocument
     */
    public function setCollectionDocument(\DocCollection $collectionDocument)
    {
        $this->collectionDocument = $collectionDocument;
        $this->documentList = $this->collectionDocument->getDocumentList();
    }
    /**
     * File to record result of export (must be writable)
     * The file will be overwrite if exists
     * @param string $outputFilePath
     */
    public function setOutputFilePath($outputFilePath)
    {
        $this->outputFilePath = $outputFilePath;
    }
    /**
     * Set identifier key to write status
     * @see ExportCollection::setStatus
     * @param string $exportStatusId
     */
    public function setExportStatusId($exportStatusId)
    {
        $this->exportStatusId = $exportStatusId;
    }
    /**
     * Type of acl reference when export profile
     * @param string $profileAccountType
     */
    public function setProfileAccountType($profileAccountType)
    {
        $this->profileAccountType = $profileAccountType;
    }
    /**
     * Get output file name
     * @see ExportCollection::setOutputFilePath
     * @return string
     */
    public function getOutputFilePath()
    {
        return $this->outputFilePath;
    }
    /**
     * Set CSV separator character
     * @param string $cvsSeparator
     * @throws Exception
     */
    public function setCvsSeparator($cvsSeparator)
    {
        if (strlen($cvsSeparator) !== 1) {
            throw new Exception("EXPC0016", $cvsSeparator);
        }
        $this->cvsSeparator = $cvsSeparator;
    }
    /**
     * Only in csv, and no profil, export  numeric identifier if no logical name
     * Else  identifier is not exported
     * @param boolean $exportDocumentNumericIdentiers
     */
    public function setExportDocumentNumericIdentiers($exportDocumentNumericIdentiers)
    {
        $this->exportDocumentNumericIdentiers = $exportDocumentNumericIdentiers;
    }
    /**
     * Indicate if export also attached files
     * @param boolean $exportFiles
     */
    public function setExportFiles($exportFiles)
    {
        $this->exportFiles = $exportFiles;
    }
    /**
     * Indicate if Profile data must be exported also
     * @param boolean $exportProfil
     */
    public function setExportProfil($exportProfil)
    {
        $this->exportProfil = $exportProfil;
    }
    /**
     * Set encoding format default is UTF-8
     * @param string $outputFileEncding
     * @throws Exception
     */
    public function setOutputFileEncoding($outputFileEncding)
    {
        $allowedFormat = array(
            self::utf8Encoding,
            self::latinEncoding
        );
        if (!in_array($outputFileEncding, $allowedFormat)) {
            throw new Exception("EXPC0015", $outputFileEncding, implode(",", $allowedFormat));
        }
        $this->outputFileEncoding = $outputFileEncding;
    }
    /**
     * Indicate file output format
     * @param string $outputFormat
     * @throws Exception
     */
    public function setOutputFormat($outputFormat)
    {
        $allowedFormat = array(
            self::csvRawOutputFormat,
            self::csvRawOnlyDataOutputFormat,
            self::csvDisplayValueOutputFormat,
            self::xmlArchiveOutputFormat,
            self::xmlFileOutputFormat
        );
        if (!in_array($outputFormat, $allowedFormat)) {
            throw new Exception("EXPC0001", $outputFormat, implode(",", $allowedFormat));
        }
        $this->outputFormat = $outputFormat;
    }
    /**
     * Only for csv mode. Set true if need to export only column set by applicative parameter "FREEDOM_EXPORTCOLS"
     * @param boolean $useUserColumnParameter
     */
    public function setUseUserColumnParameter($useUserColumnParameter)
    {
        $this->useUserColumnParameter = $useUserColumnParameter;
    }
    /**
     * Export Document LIst
     * @see ExportCollection::setDocumentlist
     * @throws Exception
     */
    public function export()
    {
        if (empty($this->outputFilePath)) {
            throw new Exception("EXPC0002");
        }
        if (file_exists($this->outputFilePath) && !is_writable($this->outputFilePath)) {
            throw new Exception("EXPC0003", $this->outputFilePath);
        }
        if ($this->documentList === null) {
            throw new Exception("EXPC0004");
        }
        
        switch ($this->outputFormat) {
            case self::csvRawOutputFormat:
            case self::csvRawOnlyDataOutputFormat:
            case self::csvDisplayValueOutputFormat:
                $this->exportCsv();
                break;

            case self::xmlArchiveOutputFormat:
            case self::xmlFileOutputFormat:
                $this->exportCollectionXml();
                break;
        }
    }
    /**
     * Set documentList to export
     * @param \DocumentList $documentList
     */
    public function setDocumentlist(\DocumentList $documentList)
    {
        $this->documentList = $documentList;
    }
    /**
     * Export to csv file
     * @throws Exception
     */
    protected function exportCsv()
    {
        $dl = $this->documentList;
        $exportDoc = new \Dcp\ExportDocument();
        $exportDoc->setCsvEnclosure($this->cvsEnclosure);
        $exportDoc->setCsvSeparator($this->cvsSeparator);
        $exportDoc->setExportAccountType($this->profileAccountType);
        $exportDoc->setVerifyAttributeAccess($this->verifyAttributeAccess);
        $outDir = '';
        if ($this->exportFiles) {
            $outDir = tempnam(getTmpDir() , 'exportFolder');
            if (is_file($outDir)) {
                unlink($outDir);
            }
            mkdir($outDir);
            $fdlcsv = $outDir . "/fdl.csv";
            $this->outHandler = fopen($fdlcsv, "w");
            if (!$this->outHandler) {
                throw new Exception("EXPC0012", $fdlcsv);
            }
        } else {
            $this->outHandler = fopen($this->outputFilePath, "w");
            if (!$this->outHandler) {
                throw new Exception("EXPC0005", $this->outputFilePath);
            }
        }
        
        $c = 0;
        $rc = count($dl);
        if ($this->exportProfil) {
            foreach ($dl as $doc) {
                $this->recordStatus(sprintf(_("Record documents %d/%d") , $c, $rc));
                if ($doc->doctype === "C") {
                    /**
                     * @var \DocFam $doc
                     */
                    $c++;
                    if ($c % 20 == 0) {
                        $this->recordStatus(sprintf(_("Record documents %d/%d") , $c, $rc));
                    }
                    $this->csvFamilyExport($doc);
                }
            }
            $this->writeFamilies();
        }
        $fileInfos = array();
        foreach ($dl as $doc) {
            
            if ($doc->doctype !== "C") {
                $c++;
                if ($c % 20 == 0) {
                    
                    $this->recordStatus(sprintf(_("Record documents %d/%d") , $c, $rc));
                }
                $exportDoc->csvExport($doc, $fileInfos, $this->outHandler, $this->exportProfil, $this->exportFiles, $this->exportDocumentNumericIdentiers, ($this->outputFileEncoding === self::utf8Encoding) , !$this->useUserColumnParameter, $this->outputFormat);
            }
        }
        fclose($this->outHandler);
        
        if ($this->exportFiles) {
            $this->zipFiles($outDir, $fileInfos);
        }
    }
    /**
     * Write message to session var. Used by interfaces to see progress
     * @param string $msg
     * @param bool $endStatus
     */
    public function recordStatus($msg, $endStatus = false)
    {
        if ($this->exportStatusId) {
            global $action;
            $warnings = array();
            if ($endStatus) {
                $warnings = $action->parent->getWarningMsg();
                if (count($warnings) > 0) {
                    array_unshift($warnings, _("Export:Warnings"));
                }
                
                $action->parent->clearWarningMsg();
            }
            $action->register($this->exportStatusId, array(
                "status" => $msg,
                "warnings" => $warnings,
                "end" => $endStatus
            ));
        }
    }
    /**
     * Record document relative to family
     * @param string $documentId
     */
    protected function addDocumentToExport($documentId)
    {
        $this->moreDocuments[$documentId] = true;
    }
    /**
     * Record family data which are write by writeFamilies
     * @see ExportCollection::writeFamilies
     * @param \DocFam $family
     */
    protected function csvFamilyExport(\DocFam $family)
    {
        
        $wname = "";
        $cvname = "";
        $cpname = "";
        $fpname = "";
        
        $tmoredoc = array();
        $dbaccess = getDbAccess();
        if ($family->profid != $family->id) {
            $fp = getTDoc($dbaccess, $family->profid);
            $tmoredoc[$fp["id"]] = "famprof";
            $this->addDocumentToExport($fp["id"]);
            if ($fp["name"] != "") $fpname = $fp["name"];
            else $fpname = $fp["id"];
        } else {
        }
        if ($family->cprofid) {
            $cp = getTDoc($dbaccess, $family->cprofid);
            if ($cp["name"] != "") $cpname = $cp["name"];
            else $cpname = $cp["id"];
            $tmoredoc[$cp["id"]] = "cprofid";
            $this->addDocumentToExport($cp["id"]);
        }
        if ($family->ccvid > 0) {
            $cv = getTDoc($dbaccess, $family->ccvid);
            if ($cv["name"] != "") $cvname = $cv["name"];
            else $cvname = $cv["id"];
            $tmskid = $family->rawValueToArray($cv["cv_mskid"]);
            
            foreach ($tmskid as $kmsk => $imsk) {
                if ($imsk != "") {
                    $msk = getTDoc($dbaccess, $imsk);
                    if ($msk) {
                        $tmoredoc[$msk["id"]] = "mask";
                        $this->addDocumentToExport($msk["id"]);
                    }
                }
            }
            
            $tmoredoc[$cv["id"]] = "cv";
            $this->addDocumentToExport($cv["id"]);
        }
        
        if ($family->wid > 0) {
            $wdoc = \new_doc($dbaccess, $family->wid);
            if ($wdoc->name != "") $wname = $wdoc->name;
            else $wname = $wdoc->id;
            $tattr = $wdoc->getAttributes();
            foreach ($tattr as $ka => $oa) {
                if ($oa->type == "docid") {
                    $tdid = $wdoc->getMultipleRawValues($ka);
                    foreach ($tdid as $did) {
                        if ($did != "") {
                            $m = getTDoc($dbaccess, $did);
                            if ($m) {
                                if ($m["doctype"] !== "C") {
                                    $tmoredoc[$m["initid"]] = "wrel";
                                    $this->addDocumentToExport($m["initid"]);
                                    
                                    if (!empty($m["cv_mskid"])) {
                                        $tmskid = $family->rawValueToArray($m["cv_mskid"]);
                                        foreach ($tmskid as $kmsk => $imsk) {
                                            if ($imsk != "") {
                                                $msk = getTDoc($dbaccess, $imsk);
                                                if ($msk) {
                                                    $tmoredoc[$msk["id"]] = "wmask";
                                                    $this->addDocumentToExport($msk["initid"]);
                                                }
                                            }
                                        }
                                    }
                                    if (!empty($m["tm_tmail"])) {
                                        $tmskid = $family->rawValueToArray(str_replace('<BR>', "\n", $m["tm_tmail"]));
                                        foreach ($tmskid as $kmsk => $imsk) {
                                            if ($imsk != "") {
                                                $msk = getTDoc($dbaccess, $imsk);
                                                if ($msk) {
                                                    $tmoredoc[$msk["id"]] = "tmask";
                                                    $this->addDocumentToExport($msk["initid"]);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            $tmoredoc[$family->wid] = "wid";
            $this->addDocumentToExport($family->wid);
        }
        
        if ($cvname || $wname || $cpname || $fpname) {
            $this->familyData[$family->id][] = array(
                "BEGIN",
                "",
                "",
                "",
                "",
                $family->name
            );
            
            if ($fpname) {
                $this->familyData[$family->id][] = array(
                    "PROFID",
                    $fpname
                );
            }
            if ($cvname) {
                $this->familyData[$family->id][] = array(
                    "CVID",
                    $cvname
                );
            }
            if ($wname) {
                $this->familyData[$family->id][] = array(
                    "WID",
                    $wname
                );
            }
            if ($family->cprofid) {
                $this->familyData[$family->id][] = array(
                    "CPROFID",
                    $cpname
                );
            }
            $this->familyData[$family->id][] = array(
                "END"
            );
        }
    }
    /**
     * write family data
     */
    protected function writeFamilies()
    {
        
        $exportDoc = new \Dcp\ExportDocument();
        $exportDoc->setCsvEnclosure($this->cvsEnclosure);
        $exportDoc->setCsvSeparator($this->cvsSeparator);
        
        $more = new \DocumentList();
        $more->addDocumentIdentifiers(array_keys($this->moreDocuments));
        $searchDl = $more->getSearchDocument();
        $searchDl->setOrder("fromid, name, id");
        foreach ($more as $adoc) {
            
            $exportDoc->csvExport($adoc, $fileInfos, $this->outHandler, false, $this->exportFiles, $this->exportDocumentNumericIdentiers, ($this->outputFileEncoding === self::utf8Encoding) , !$this->useUserColumnParameter, $this->outputFormat);
        }
        foreach ($more as $adoc) {
            
            $exportDoc->exportProfil($this->outHandler, $adoc->id);
        }
        
        foreach ($this->familyData as $famid => $aRow) {
            $family = \new_doc("", $famid);
            if ($family->profid == $family->id) {
                $exportDoc->exportProfil($this->outHandler, $family->id);
            }
        }
        
        foreach ($this->familyData as $famid => $aRow) {
            
            foreach ($aRow as $data) \Dcp\WriteCsv::fput($this->outHandler, $data);
        }
    }
    protected function zipFiles($directory, array $infos)
    {
        // Copy file from vault
        foreach ($infos as $info) {
            $source = $info["path"];
            $ddir = $directory . '/' . $info["ldir"];
            if (!is_dir($ddir)) mkdir($ddir);
            $dest = $ddir . '/' . $info["fname"];
            if (!copy($source, $dest)) {
                throw new Exception("EXPC0014", $source, $dest);
            }
        }
        
        $zipfile = $this->outputFilePath . ".zip";
        $cmd = sprintf("cd %s && zip -r %s -- * > /dev/null && mv %s %s", escapeshellarg($directory) , escapeshellarg($zipfile) , escapeshellarg($zipfile) , escapeshellarg($this->outputFilePath));
        system($cmd, $ret);
        if (!is_file($this->outputFilePath)) {
            
            throw new Exception("EXPC0012", $this->outputFilePath);
        }
    }
    protected function exportCollectionXml()
    {
        $dl = $this->documentList;
        
        $foutdir = uniqid(getTmpDir() . "/exportxml");
        if (!mkdir($foutdir)) {
            throw new Exception("EXPC0006", $foutdir);
        }
        
        VerifyAttributeAccess::clearCache();
        
        $exd = new \Dcp\ExportXmlDocument();
        
        $exd->setExportFiles($this->exportFiles);
        $exd->setExportDocumentNumericIdentiers($this->exportDocumentNumericIdentiers);
        $exd->setStructureAttributes(true);
        $exd->setIncludeSchemaReference(true);
        $exd->setVerifyAttributeAccess($this->verifyAttributeAccess);
        
        $c = 0;
        $rc = count($dl);
        /**
         * @var \Doc $doc
         */
        foreach ($dl as $doc) {
            $c++;
            if ($c % 20 == 0) {
                $this->recordStatus(sprintf(_("Record documents %d/%d") , $c, $rc));
            }
            
            $ftitle = $this->cleanFileName($doc->getTitle());
            $suffix = sprintf("{%d}.xml", $doc->id);
            $maxBytesLen = MAX_FILENAME_LEN - strlen($suffix);
            $fname = sprintf("%s/%s%s", $foutdir, mb_strcut($ftitle, 0, $maxBytesLen, 'UTF-8') , $suffix);
            
            $exd->setDocument($doc);
            $exd->writeTo($fname);
        }
        
        if ($this->outputFormat === self::xmlFileOutputFormat) {
            $this->catXml($foutdir);
        } elseif ($this->outputFormat === self::xmlArchiveOutputFormat) {
            $this->zipXml($foutdir);
        }
        system(sprintf("rm -fr %s", escapeshellarg($foutdir)));
    }
    /**
     * Replace special character for a file name
     * @param string $fileName
     * @return string
     */
    protected static function cleanFileName($fileName)
    {
        return str_replace(array(
            '/',
            '\\',
            '?',
            '*',
            "'",
            ':',
            " "
        ) , array(
            '-',
            '-',
            '-',
            '-',
            '-',
            '_'
        ) , $fileName);
    }
    /**
     * zip Xml files included in directory
     * @param string $directory
     * @throws Exception
     */
    protected function zipXml($directory)
    {
        
        $zipfile = $this->outputFilePath . ".zip";
        $cmd = sprintf("cd %s && zip -r %s -- * > /dev/null && mv %s %s", escapeshellarg($directory) , escapeshellarg($zipfile) , escapeshellarg($zipfile) , escapeshellarg($this->outputFilePath));
        
        system($cmd, $ret);
        if (!is_file($this->outputFilePath)) {
            
            throw new Exception("EXPC0012", $this->outputFilePath);
        }
    }
    /**
     * concatenate Xml files included in directory into single XML file
     * @param string $directory
     * @throws Exception
     */
    protected function catXml($directory)
    {
        
        $fout = fopen($this->outputFilePath, "w");
        if (!$fout) {
            throw new Exception("EXPC0005", $this->outputFilePath);
        }
        $xml_head = <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<documents date="%s" author="%s" name="%s">

EOF;
        if ($this->collectionDocument) {
            $exportname = $this->collectionDocument->getTitle();
        } else {
            $exportname = sprintf("Custom '\"export");
        }
        $xml_head = sprintf($xml_head, htmlspecialchars(strftime("%FT%T")) , htmlspecialchars(\Account::getDisplayName(getCurrentUser()->id) , ENT_QUOTES) , htmlspecialchars($exportname, ENT_QUOTES));
        
        $ret = fwrite($fout, $xml_head);
        if ($ret === false) {
            throw new Exception("EXPC0005", $this->outputFilePath);
        }
        fflush($fout);
        /* chdir into dir containing the XML files
         * and concatenate them into the output file
        */
        $cwd = getcwd();
        $ret = chdir($directory);
        if ($ret === false) {
            //  exportExit($action, sprintf("%s (Error chdir to '%s')", _("Xml file cannot be created") , htmlspecialchars($foutdir)));
            throw new Exception("EXPC0008", $this->outputFilePath);
        }
        
        if (count($this->documentList) > 0) {
            $cmd = sprintf("cat -- *xml | grep -v '<?xml version=\"1.0\" encoding=\"UTF-8\"?>' >> %s", escapeshellarg($this->outputFilePath));
            system($cmd, $ret);
        }
        
        $ret = chdir($cwd);
        if ($ret === false) {
            // exportExit($action, sprintf("%s (Error chdir to '%s')", _("Xml file cannot be created") , htmlspecialchars($cwd)));
            throw new Exception("EXPC0009", $cwd);
        }
        /* Print XML footer */
        $ret = fseek($fout, 0, SEEK_END);
        if ($ret === - 1) {
            //exportExit($action, sprintf("%s (Error fseek '%s')", _("Xml file cannot be created") , htmlspecialchars($xmlfile)));
            throw new Exception("EXPC0010", $this->outputFilePath);
        }
        
        $xml_footer = "</documents>";
        $ret = fwrite($fout, $xml_footer);
        if ($ret === - 1) {
            throw new Exception("EXPC0011", $this->outputFilePath);
        }
        fclose($fout);
    }
}
