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
        $testExtractFolder = uniqid(getTmpDir() . "/testexportextractimage");
        SetHttpVar("wfile", "Y");
        SetHttpVar("eformat", "I");
        SetHttpVar("app", "FDL");
        
        exportfld(self::getAction() , $folderId, $famid, $testFolder);
        
        $testarchivefile = $testFolder . "/fdl.zip";
        $err = extractTar($testarchivefile, $testExtractFolder);
        $this->assertEmpty($err, sprintf("Unexpected error while extracting archive '%s': %s", $testarchivefile, $err));
        
        $output = array();
        exec(sprintf("ls -R %s", escapeshellarg($testExtractFolder)) , $output);
        foreach ($needles as $needle) {
            $this->assertContains($needle, $output, sprintf("file %s not found in export archive", $needle));
        }
        
        remove_dir($testFolder);
    }
    /**
     * Test that exported documents have no param columns
     * @param string|int $folderId
     * @param array $expectDoc
     * @param string $separator
     * @param string $enclosure
     * @param array $expectedProfil
     * @dataProvider dataExportFolder
     */
    public function testExportFolder($folderId, array $expectDoc, $separator, $enclosure, array $expectedProfil)
    {
        include_once ('FDL/exportfld.php');
        
        SetHttpVar("wfile", "N");
        SetHttpVar("wprof", ($expectedProfil ? "Y" : "N"));
        SetHttpVar("eformat", "I");
        SetHttpVar("app", "FDL");
        SetHttpVar("csv-enclosure", $enclosure);
        SetHttpVar("csv-separator", $separator);
        
        $exportOutput = uniqid(getTmpDir() . "/testExport") . ".csv";
        
        exportfld(self::getAction() , $folderId, 0, $exportOutput);
        
        $this->assertTrue(file_exists($exportOutput) , sprintf('Export File "%s" nor create', $exportOutput));
        
        $exportDocName = array();
        $h = fopen($exportOutput, "r");
        
        while (($data = fgetcsv($h, 0, $separator, $enclosure)) !== FALSE) {
            if (isset($data[0]) && $data[0] === "DOC") {
                $docName = $data[2];
                $exportDocName[] = $docName;
                if (isset($expectDoc[$docName])) {
                    $expecDoc = $expectDoc[$docName];
                    foreach ($expecDoc as $k => $v) {
                        $this->assertEquals($v, $data[$k], sprintf("Invalid value for %s column : [%s] : see %s", $k, implode("][", $data) , $exportOutput));
                    }
                }
            }
            if (isset($data[0]) && $data[0] === "PROFIL") {
                
                $docName = $data[1];
                if (isset($expectedProfil[$docName])) {
                    $prof = $expectedProfil[$docName];
                    
                    foreach ($prof as $k => $v) {
                        $this->assertEquals($v, $data[$k], sprintf("Invalid profil value for %s column : [%s]: see %s", $k, implode("][", $data) , $exportOutput));
                    }
                }
            }
        }
        fclose($h);
        
        $this->assertEquals(array_keys($expectDoc) , $exportDocName, "No same exported documents: See $exportOutput");
        //unlink($exportOutput);
        
    }
    /**
     * Test that exported documents have no param columns
     * @param string|int $familyId
     * @param array $expectData
     * @param string $separator
     * @param string $enclosure
     * @dataProvider dataExportFamily
     */
    public function testExportamily($familyId, array $expectData, $separator, $enclosure)
    {
        include_once ('FDL/exportfld.php');
        
        SetHttpVar("wfile", "N");
        SetHttpVar("wprof", "Y");
        SetHttpVar("eformat", "I");
        SetHttpVar("app", "FDL");
        SetHttpVar("csv-enclosure", $enclosure);
        SetHttpVar("csv-separator", $separator);
        /**
         * @var \Dir $tmpFolder
         */
        $tmpFolder = createTmpDoc(self::$dbaccess, "DIR");
        $err = $tmpFolder->add();
        $err.= $tmpFolder->insertDocument($familyId);
        
        $this->assertEmpty($err, "Error when create family folder");
        $exportOutput = uniqid(getTmpDir() . "/testExport") . ".csv";
        
        exportfld(self::getAction() , $tmpFolder->id, 0, $exportOutput);
        
        $this->assertTrue(file_exists($exportOutput) , sprintf('Export File "%s" nor create', $exportOutput));

        $h = fopen($exportOutput, "r");
        
        $keys = array();
        while (($data = fgetcsv($h, 0, $separator, $enclosure)) !== FALSE) {
            if (!empty($data[0])) {
                $keys[] = $data[0];
                if (isset($expectData[$data[0]])) {
                    foreach ($expectData[$data[0]] as $k => $v) {
                        $this->assertEquals($v, $data[$k], sprintf("Invalid value for %s column : [%s] : see %s", $k, implode("][", $data) , $exportOutput));
                    }
                }
            }
        }
        fclose($h);
        foreach ($expectData as $key => $v) {
            $this->assertTrue(in_array($key, $keys) , "Missing key $key see $exportOutput");
        }
        //unlink($exportOutput);
        
    }

    /**
     * Test that exported documents have no param columns
     * @param array $data test specification
     * @throws \Exception
     * @dataProvider dataExportNoParam
     */
    public function testExportNoParam($data)
    {
        include_once ('FDL/exportfld.php');
        
        foreach (array(
            'export:doc',
            'expect:no:order'
        ) as $key) {
            if (!isset($data[$key])) {
                throw new \Exception(sprintf("Missing key '%s' in test data."));
            }
        }
        /* doc */
        $doc = new_Doc(self::$dbaccess, $data['export:doc']);
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
        
        $ed = new \Dcp\ExportDocument();
        $ed->csvExport($doc, $ef, $fout, $wprof, $wfile, $wident, $wutf8, $nopref, $eformat);
        
        fclose($fout);
        
        $out = file_get_contents($tmpfile);
        $lines = preg_split("/\n/", $out);
        foreach ($lines as & $line) {
            if (!preg_match('/^ORDER;/', $line)) {
                continue;
            }
            foreach ($data['expect:no:order'] as $column) {
                $match = preg_match(sprintf('/;%s;/', preg_quote($column, '/')) , $line);
                $this->assertTrue(($match <= 0) , sprintf("Found param '%s' in ORDER line '%s'.", $column, $line));
            }
        }
        unset($line);
        
        unlink($tmpfile);
    }
    
    public function dataExportFamily()
    {
        return array(
            array(
                "family" => "TST_EXPORT_PARAM",
                "expectedData" => array(
                    "BEGIN" => array(
                        1 => '',
                        2 => '',
                        3 => '',
                        4 => '',
                        5 => "TST_EXPORT_PARAM"
                    ) ,
                    "PROFID" => array(
                        1 => "TST_PROFFAMEXPORT"
                    ) ,
                    "CPROFID" => array(
                        1 => "TST_PROFEXPORT"
                    ) ,
                    "END" => array()
                ) ,
                "separator" => ";",
                "enclosure" => '"'
            ) ,
            array(
                "family" => "TST_EXPORT_PARAM",
                "expectedData" => array(
                    "BEGIN" => array(
                        1 => '',
                        2 => '',
                        3 => '',
                        4 => '',
                        5 => "TST_EXPORT_PARAM"
                    ) ,
                    "PROFID" => array(
                        1 => "TST_PROFFAMEXPORT"
                    ) ,
                    "CPROFID" => array(
                        1 => "TST_PROFEXPORT"
                    )
                ) ,
                "separator" => ",",
                "enclosure" => "'"
            )
        );
    }
    
    public function dataExportFolder()
    {
        return array(
            array(
                "folderId" => "TST_EXPORT_FOLDER",
                "expectedDocument" => array(
                    "TST_EXPORT_PARAM_01" => array(
                        4 => "L'un et l'autre",
                        5 => 1
                    ) ,
                    "TST_EXPORT_PARAM_02" => array(
                        4 => "Déja vu\nUne impression",
                        5 => 2
                    ) ,
                    "TST_EXPORT_PARAM_03" => array(
                        4 => 'Dicton "Qui élargit son cœur, rétrécit sa bouche"',
                        5 => 3
                    )
                ) ,
                "separator" => ";",
                "enclosure" => '"',
                "profil" => array()
            ) ,
            array(
                "folderId" => "TST_EXPORT_FOLDER",
                "expectedDocument" => array(
                    "TST_EXPORT_PARAM_01" => array(
                        4 => "L'un et l'autre"
                    ) ,
                    "TST_EXPORT_PARAM_02" => array(
                        4 => "Déja vu\nUne impression"
                    ) ,
                    "TST_EXPORT_PARAM_03" => array(
                        4 => 'Dicton "Qui élargit son cœur, rétrécit sa bouche"'
                    )
                ) ,
                "separator" => ",",
                "enclosure" => "'",
                "profil" => array()
            ) ,
            array(
                "folderId" => "TST_EXPORT_FOLDER",
                "expectedDocument" => array(
                    "TST_EXPORT_PARAM_01" => array(
                        4 => "L'un et l'autre"
                    ) ,
                    "TST_PROFEXPORT" => array(
                        4 => "Profil de test, d'exportation"
                    ) ,
                    "TST_EXPORT_PARAM_02" => array(
                        4 => "Déja vu\nUne impression"
                    ) ,
                    "TST_EXPORT_PARAM_03" => array(
                        4 => 'Dicton "Qui élargit son cœur, rétrécit sa bouche"'
                    )
                ) ,
                "separator" => ",",
                "enclosure" => "'",
                "profil" => array(
                    "TST_PROFEXPORT" => array(
                        4 => "view=GADMIN",
                        5 => "view=GDEFAULT"
                    )
                )
            )
        );
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
                array(
                    "export:doc" => "TST_EXPORT_PARAM_01",
                    "expect:no:order" => array(
                        "a_param_text"
                    )
                )
            )
        );
    }
}
?>