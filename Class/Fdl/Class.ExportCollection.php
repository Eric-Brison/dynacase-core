<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
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
    
    protected $cvsSeparator = ";";
    protected $cvsEnclosure = "";
    
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
     * @param string $cvsEnclosure
     */
    public function setCvsEnclosure($cvsEnclosure)
    {
        $this->cvsEnclosure = $cvsEnclosure;
    }
    /**
     * @param \DocCollection $collectionDocument
     */
    public function setCollectionDocument($collectionDocument)
    {
        $this->collectionDocument = $collectionDocument;
        $this->documentList = $this->collectionDocument->getDocumentList();
    }
    /**
     * File to record result of export (must be writable)
     * @param string $outputFilePath
     */
    public function setOutputFilePath($outputFilePath)
    {
        $this->outputFilePath = $outputFilePath;
    }
    /**
     * @param string $exportStatusId
     */
    public function setExportStatusId($exportStatusId)
    {
        $this->exportStatusId = $exportStatusId;
    }
    /**
     * @return string
     */
    public function getOutputFilePath()
    {
        return $this->outputFilePath;
    }
    /**
     * @param string $cvsSeparator
     */
    public function setCvsSeparator($cvsSeparator)
    {
        $this->cvsSeparator = $cvsSeparator;
    }
    /**
     * @param boolean $exportDocumentNumericIdentiers
     */
    public function setExportDocumentNumericIdentiers($exportDocumentNumericIdentiers)
    {
        $this->exportDocumentNumericIdentiers = $exportDocumentNumericIdentiers;
    }
    /**
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
     * @param string $outputFileEncding
     */
    public function setOutputFileEncoding($outputFileEncding)
    {
        $this->outputFileEncoding = $outputFileEncding;
    }
    /**
     * Indicate file output format :
     * @param string $outputFormat
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
     * @param boolean $useUserColumnParameter
     */
    public function setUseUserColumnParameter($useUserColumnParameter)
    {
        $this->useUserColumnParameter = $useUserColumnParameter;
    }
    
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
    
    public function setDocumentlist(\DocumentList $documentList)
    {
        $this->documentList = $documentList;
    }
    
    protected function exportCsv()
    {
        $dl = $this->documentList;
        $exportDoc = new \Dcp\ExportDocument();
        $exportDoc->setCsvEnclosure($this->cvsEnclosure);
        $exportDoc->setCsvSeparator($this->cvsSeparator);
        $outDir = '';
        if ($this->exportFiles) {
            $outDir = tempnam(getTmpDir() , 'exportFolder');
            if (is_file($outDir)) {
                unlink($outDir);
            }
            mkdir($outDir);
            $fdlcsv = $outDir . "/fdl.csv";
            $outHandler = fopen($fdlcsv, "w");
            if (!$outHandler) {
                throw new Exception("EXPC0012", $fdlcsv);
            }
        } else {
            $outHandler = fopen($this->outputFilePath, "w");
            if (!$outHandler) {
                throw new Exception("EXPC0005", $this->outputFilePath);
            }
        }
        
        $c = 0;
        $rc = count($dl);
        if ($this->exportProfil) {
            foreach ($dl as $doc) {
                $this->recordStatus(sprintf(_("Record documents %d/%d") , $c, $rc));
                if ($doc->doctype === "C") {
                    $c++;
                    if ($c % 20 == 0) {
                        $this->csvFamilyExport($doc);
                    }
                }
            }
        }
        $fileInfos = array();
        foreach ($dl as $doc) {
            
            if ($doc->doctype !== "C") {
                $c++;
                if ($c % 20 == 0) {
                    
                    $this->recordStatus(sprintf(_("Record documents %d/%d") , $c, $rc));
                }
                $exportDoc->csvExport($doc, $fileInfos, $outHandler, $this->exportProfil, $this->exportFiles, $this->exportDocumentNumericIdentiers, ($this->outputFileEncoding === self::utf8Encoding) , $this->useUserColumnParameter, $this->outputFormat);
            }
        }
        fclose($outHandler);
        
        if ($this->exportFiles) {
            $this->zipFiles($outDir, $fileInfos);
        }
    }
    
    public function recordStatus($msg, $endStatus = false)
    {
        if ($this->exportStatusId) {
            global $action;
            $action->register($this->exportStatusId, array(
                "status" => $msg,
                "end" => $endStatus
            ));
        }
    }
    /**
     * @param \DocFam $doc
     * @TODO
     */
    protected function csvFamilyExport(\DocFam $doc)
    {
        
        $wname = "";
        $cvname = "";
        $cpname = "";
        $fpname = "";
        
        if ($wprof) {
            if ($doc->profid != $doc->id) {
                $fp = getTDoc($dbaccess, $doc->profid);
                $tmoredoc[$fp["id"]] = $fp;
                if ($fp["name"] != "") $fpname = $fp["name"];
                else $fpname = $fp["id"];
            } else {
                exportProfil($fout, $dbaccess, $doc->profid);
            }
            if ($doc->cprofid) {
                $cp = getTDoc($dbaccess, $doc->cprofid);
                if ($cp["name"] != "") $cpname = $cp["name"];
                else $cpname = $cp["id"];
                $tmoredoc[$cp["id"]] = $cp;
            }
            if ($doc->ccvid > 0) {
                $cv = getTDoc($dbaccess, $doc->ccvid);
                if ($cv["name"] != "") $cvname = $cv["name"];
                else $cvname = $cv["id"];
                $tmskid = $doc->rawValueToArray($cv["cv_mskid"]);
                
                foreach ($tmskid as $kmsk => $imsk) {
                    if ($imsk != "") {
                        $msk = getTDoc($dbaccess, $imsk);
                        if ($msk) $tmoredoc[$msk["id"]] = $msk;
                    }
                }
                
                $tmoredoc[$cv["id"]] = $cv;
            }
            
            if ($doc->wid > 0) {
                $wdoc = new_doc($dbaccess, $doc->wid);
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
                                    $tmoredoc[$m["id"]] = $m;
                                    if (!empty($m["cv_mskid"])) {
                                        $tmskid = $doc->rawValueToArray($m["cv_mskid"]);
                                        foreach ($tmskid as $kmsk => $imsk) {
                                            if ($imsk != "") {
                                                $msk = getTDoc($dbaccess, $imsk);
                                                if ($msk) $tmoredoc[$msk["id"]] = $msk;
                                            }
                                        }
                                    }
                                    if (!empty($m["tm_tmail"])) {
                                        $tmskid = $doc->rawValueToArray(str_replace('<BR>', "\n", $m["tm_tmail"]));
                                        foreach ($tmskid as $kmsk => $imsk) {
                                            if ($imsk != "") {
                                                $msk = getTDoc($dbaccess, $imsk);
                                                if ($msk) $tmoredoc[$msk["id"]] = $msk;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                $tmoredoc[$doc->wid] = getTDoc($dbaccess, $doc->wid);
            }
            if ($cvname || $wname || $cpname || $fpname) {
                $famData[] = array(
                    "BEGIN",
                    "",
                    "",
                    "",
                    "",
                    $doc->name
                );
                
                if ($fpname) {
                    $famData[] = array(
                        "PROFID",
                        $fpname
                    );
                }
                if ($cvname) {
                    $famData[] = array(
                        "CVID",
                        $cvname
                    );
                }
                if ($wname) {
                    $famData[] = array(
                        "WID",
                        $wname
                    );
                }
                if ($doc->cprofid) {
                    $famData[] = array(
                        "CPROFID",
                        $cpname
                    );
                }
                $famData[] = array(
                    "END"
                );
            }
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
        if (is_file($this->outputFilePath)) {
            // @TODO
            //recordStatus($action, $exportId, _("Export done") , true);
            
        } else {
            throw new Exception("EXPC0012", $this->outputFilePath);
        }
    }
    protected function exportCollectionXml()
    {
        $dl = $this->documentList;
        $exportDoc = new \Dcp\ExportDocument();
        $exportDoc->setCsvEnclosure($this->cvsEnclosure);
        $exportDoc->setCsvSeparator($this->cvsSeparator);
        
        $foutdir = uniqid(getTmpDir() . "/exportxml");
        if (!mkdir($foutdir)) {
            throw new Exception("EXPC0006", $foutdir);
        }
        $c = 0;
        /**
         * @var \Doc $doc
         */
        foreach ($dl as $doc) {
            $c++;
            if ($c % 20 == 0) {
                // @TODO
                //recordStatus($action, $exportId, sprintf(_("Record documents %d/%d") , $c, $rc));
                
            }
            
            $ftitle = $this->cleanFileName($doc->getTitle());
            $suffix = sprintf("{%d}.xml", $doc->id);
            $maxBytesLen = MAX_FILENAME_LEN - strlen($suffix);
            $fname = sprintf("%s/%s%s", $foutdir, mb_strcut($ftitle, 0, $maxBytesLen, 'UTF-8') , $suffix);
            
            $doc->exportXml($xml, $this->exportFiles, $fname, $this->exportDocumentNumericIdentiers, $notUseNsdeclaration = false, $exportAttribute = array());
        }
        
        if ($this->outputFormat === self::xmlFileOutputFormat) {
            $this->catXml($foutdir);
        } elseif ($this->outputFormat === self::xmlArchiveOutputFormat) {
            $this->zipXml($foutdir);
        }
        system(sprintf("rm -fr %s", escapeshellarg($foutdir)));
    }
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
    
    protected function zipXml($directory)
    {
        
        $zipfile = $this->outputFilePath . ".zip";
        $cmd = sprintf("cd %s && zip -r %s -- * > /dev/null && mv %s %s", escapeshellarg($directory) , escapeshellarg($zipfile) , escapeshellarg($zipfile) , escapeshellarg($this->outputFilePath));
        
        system($cmd, $ret);
        if (is_file($this->outputFilePath)) {
            // @TODO
            //recordStatus($action, $exportId, _("Export done") , true);
            
        } else {
            throw new Exception("EXPC0012", $this->outputFilePath);
        }
    }
    
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
