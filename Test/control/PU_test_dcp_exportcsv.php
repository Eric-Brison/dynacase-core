<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package Dcp\Pu
*/

namespace Dcp\Pu;

require_once 'PU_testcase_dcp.php';

class TestExportCsv extends TestCaseDcpCommonFamily
{
    static function getCommonImportFile()
    {
        return array(
            "PU_data_dcp_exportfamilycsv.ods",
            "PU_data_dcp_exportdoccsv.ods",
            "PU_data_dcp_exportdocimage.ods"
        );
    }
    /**
     * Test that exported documents have no param columns
     * @param array $archiveFile
     * @param $needles
     * @throws \Dcp\Exception
     * @dataProvider dataExportImage
     */
    public function testExportImage($archiveFile, $needles)
    {
        include_once ('FDL/exportfld.php');
        include_once ('Lib.FileDir.php');
        
        $oImport = new \ImportDocument();
        $oImport->importDocuments(self::getAction() , $archiveFile, false, true);
        $err = $oImport->getErrorMessage();
        if ($err) throw new \Dcp\Exception($err);
        
        $folderId = "TEXT_FOLDER_EXPORT_IMAGE";
        $famid = "TST_EXPORT_IMAGE";
        $testFolder = uniqid(getTmpDir() . "/testexportimage");
        $testExtarctFolder = uniqid(getTmpDir() . "/testexportextractimage");
        SetHttpVar("wfile", "Y");
        SetHttpVar("eformat", "I");
        
        exportfld(self::getAction() , $folderId, $famid, $testFolder);
        
        $testarchivefile = $testFolder . "/fdl.zip";
        extractTar($testarchivefile, $testExtarctFolder);
        
        $output = array();
        exec(sprintf("ls -R %s", escapeshellarg($testExtarctFolder)) , $output);
        foreach ($needles as $needle) {
            $this->assertContains($needle, $output, sprintf("file %s not found in export archive", $needle));
        }
        
        remove_dir($testFolder);
    }
    /**
     * Test that exported documents have no param columns
     * @param array $data test specification
     * @dataProvider dataExportNoParam
     */
    public function testExportNoParam($docName, $noOrder)
    {
        include_once ('FDL/exportfld.php');
        /* doc */
        $doc = new_Doc(self::$dbaccess, $docName);
        if (!$doc->isAlive()) {
            throw new \Exception(sprintf("Could not get document with id '%s'.", $data['export:doc']));
        }
        /* fout */
        $tmpfile = tempnam(getTmpDir() , 'TST_EXPORT_PARAM');
        if ($tmpfile === false) {
            throw new \Exception(sprintf("Could not create temporary file in '%s'.", getTmpDir()));
        }
        $fout = fopen($tmpfile, 'w');
        if ($fout === false) {
            throw new \Exception(sprintf("Could not create temporary file '%s'.", $tmpfile));
        }
        /* ef */
        $ef = array();
        /* wprof */
        $wprof = false;
        /* wfile */
        $wfile = false;
        /* wident */
        $wident = true;
        /* wutf8 */
        $wutf8 = true;
        /* nopref */
        $nopref = true;
        /* eformat */
        $eformat = 'I';
        
        exportonedoc($doc, $ef, $fout, $wprof, $wfile, $wident, $wutf8, $nopref, $eformat);
        
        fclose($fout);
        
        $out = file_get_contents($tmpfile);
        $lines = preg_split("/\n/", $out);
        foreach ($lines as & $line) {
            if (!preg_match('/^ORDER;/', $line)) {
                continue;
            }
            foreach ($noOrder as $column) {
                $match = preg_match(sprintf('/;%s;/', preg_quote($column, '/')) , $line);
                $this->assertTrue(($match <= 0) , sprintf("Found param '%s' in ORDER line '%s'.", $column, $line));
            }
        }
        unset($line);
        
        unlink($tmpfile);
    }
    
    public function dataExportImage()
    {
        return array(
            array(
                "./DCPTEST/PU_dcp_data_exportcsvimage.zip",
                array(
                    "PU_data_dcp_exportdocimageexample.png",
                    "PU_data_dcp_exportdocimage.ods"
                )
            )
        );
    }
    
    public function dataExportNoParam()
    {
        return array(
            array(
                "export:doc" => "TST_EXPORT_PARAM_01",
                "expect:no:order" => array(
                    "a_param_text"
                )
            )
        );
    }
}
?>