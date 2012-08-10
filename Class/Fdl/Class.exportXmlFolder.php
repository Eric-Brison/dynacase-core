<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Export documents in xml
 *
 * @author Anakeen 2000
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/import_file.php");
require_once 'FDL/exportxmlfld.php';

class exportXmlFolder
{
    const zipFormat = 'X';
    const xmlFormat = 'Y';
    private $dbaccess;
    private $exported = false;
    private $outputFile = '';
    private $useIdentificator = false;
    /**
     * @var string output format X or Y
     */
    private $format = self::xmlFormat;
    
    public function __construct()
    {
        $this->dbaccess = getDbAccess();
    }
    /**
     * export format xml or zip
     * @param string $xy
     * @throws Dcp\Exception
     */
    public function setOutputFormat($xy)
    {
        if ($xy == self::zipFormat || $xy == self::xmlFormat) {
            $this->format = $xy;
        } else {
            throw new Dcp\Exception(sprintf("format must be %s or %s") , self::zipFormat, self::xmlFormat);
        }
    }
    /**
     * export (or not) system document identificator
     * @param bool $exportIdentificator export option
     */
    public function useIdentificator($exportIdentificator = true)
    {
        $this->useIdentificator = $exportIdentificator;
    }
    /**
     * return exported file name
     * @return string file path
     */
    public function getOutputFile()
    {
        return $this->outputFile;
    }
    private function setOutputFile($outputFile = '')
    {
        if (!$outputFile) {
            $ext = 'nop';
            if ($this->format == self::xmlFormat) $ext = "xml";
            else if ($this->format == self::zipFormat) $ext = 'zip';
            $this->outputFile = uniqid(getTmpDir() . "/export") . ".$ext";
        } else {
            $this->outputFile = $outputFile;
        }
        return $this->outputFile;
    }
    /**
     * return content of xml file to be use only with xml format
     * @return string
     * @throws Dcp\Exception
     */
    public function getXmlContent()
    {
        if (!$this->exported) {
            throw new Dcp\Exception(sprintf(_("nothing to export. Do export before")));
        }
        if ($this->format != self::xmlFormat) {
            throw new Dcp\Exception(sprintf(_("not in XML format")));
        }
        return file_get_contents($this->outputFile);
    }
    /**
     * export documents from search object
     * @param SearchDoc $search search to export
     * @param string $outputFile path to output file
     * @return void
     */
    public function exportFromSearch(SearchDoc & $search, $outputFile = '')
    {
        global $action;
        $this->setOutputFile($outputFile);
        exportxmlfld($action, $folder = "0", $famid = "", $search, $this->outputFile, $this->format, $this->useIdentificator ? 'Y' : 'N');
    }
    /**
     * export documents from search object
     * @param string $folderId collection identificator
     * @param string $outputFile path to output file
     * @return void
     */
    public function exportFromFolder($folderId, $outputFile = '')
    {
        global $action;
        $this->setOutputFile($outputFile);
        exportxmlfld($action, $folderId, $famid = "", null, $this->outputFile, $this->format, $this->useIdentificator ? 'Y' : 'N');
    }
    /**
     *
     * export documents from selection object
     * @param Fdl_DocumentSelection $selection
     * @param string $outputFile
     */
    public function exportFromSelection(Fdl_DocumentSelection $selection, $outputFile = '')
    {
        global $action;
        $this->setOutputFile($outputFile);
        exportxmlfld($action, $folder = 0, $famid = "", null, $this->outputFile, $this->format, $this->useIdentificator ? 'Y' : 'N', $selection);
    }
}
?>
