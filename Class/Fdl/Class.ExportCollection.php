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
    const latinEncding = "iso8859-15";
    protected $outputFileEncding = self::utf8Encoding;
    
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
    public function setOutputFileEncding($outputFileEncding)
    {
        $this->outputFileEncding = $outputFileEncding;
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
        if (!is_writable($this->outputFilePath)) {
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

            case self::xmlFileOutputFormat:
                $this->exportSingleXml();
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
        $fout = fopen($this->outputFilePath, "w");
        if (!$fout) {
            throw new Exception("EXPC0005", $this->outputFilePath);
        }
        foreach ($dl as $doc) {
            $exportDoc->cvsExport($doc, $ef, $fout, $this->exportProfil, $this->exportFiles, $this->exportDocumentNumericIdentiers, ($this->outputFileEncding === self::utf8Encoding) , $this->useUserColumnParameter, $this->outputFormat);
        }
        fclose($fout);
    }
    protected function exportSingleXml()
    {
        $dl = $this->documentList;
        $exportDoc = new \Dcp\ExportDocument();
        $exportDoc->setCsvEnclosure($this->cvsEnclosure);
        $exportDoc->setCsvSeparator($this->cvsSeparator);
        
        $foutdir = uniqid(getTmpDir() . "/exportxml");
        if (!mkdir($foutdir)) {
            throw new Exception("EXPC0006", $foutdir);
        }
        /**
         * @var \Doc $doc
         */
        foreach ($dl as $doc) {
            $ftitle = $this->cleanFileName($doc->getTitle());
            $suffix = sprintf("{%d}.xml", $doc->id);
            $maxBytesLen = MAX_FILENAME_LEN - strlen($suffix);
            $fname = sprintf("%s/%s%s", $foutdir, mb_strcut($ftitle, 0, $maxBytesLen, 'UTF-8') , $suffix);
            
            $doc->exportXml($xml, $this->exportFiles, $fname, $this->exportDocumentNumericIdentiers, $notUseNsdeclaration = false, $exportAttribute = array());
        }
        
        if ($this->outputFormat === self::xmlFileOutputFormat) {
            $this->catXml($foutdir);
        }
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
