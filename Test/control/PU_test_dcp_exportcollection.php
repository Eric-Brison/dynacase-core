<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Pu;

require_once 'PU_testcase_dcp_commonfamily.php';
class TestExportCollection extends TestCaseDcpCommonFamily
{
    static function getCommonImportFile()
    {
        return array(
            "PU_data_dcp_exportcollection.ods",
            "PU_data_dcp_document_exportcollection.xml"
        );
    }
    
    protected $famName = "tst_expcoll1";
    /**
     * @param $separator
     * @param $enclosure
     * @param array $expectedData
     * @dataProvider dataExportCsv
     */
    public function testExportRawCsv($separator, $enclosure, array $expectedData)
    {
        $outFile = tempnam(getTmpDir() , 'tstexport');
        $s = new \SearchDoc(self::$dbaccess, $this->famName);
        $s->setObjectReturn();
        $s->search();
        
        $this->assertEmpty($s->searchError() , sprintf("Error in search %s", print_r($s->getSearchInfo() , true)));
        
        $ec = new \Dcp\ExportCollection();
        
        $ec->setCvsEnclosure($enclosure);
        $ec->setCvsSeparator($separator);
        $ec->setOutputFilePath($outFile);
        $ec->setDocumentlist($s->getDocumentList());
        $ec->export();
        
        $this->assertTrue(filesize($outFile) > 0, sprintf("\"%s\" file not produced", $outFile));
        
        $this->verifyCsvContains($outFile, $separator, $enclosure, $expectedData, 2);
    }
    /**
     * @param $format
     * @param $separator
     * @param $enclosure
     * @param array $expectedData
     * @dataProvider dataExportDisplayCsv
     */
    public function testExportDisplayCsv($format, $separator, $enclosure, array $expectedData)
    {
        $outFile = tempnam(getTmpDir() , 'tstexport');
        $s = new \SearchDoc(self::$dbaccess, $this->famName);
        $s->setObjectReturn();
        $s->search();
        
        $this->assertEmpty($s->searchError() , sprintf("Error in search %s", print_r($s->getSearchInfo() , true)));
        
        $ec = new \Dcp\ExportCollection();
        
        $ec->setCvsEnclosure($enclosure);
        $ec->setCvsSeparator($separator);
        $ec->setOutputFilePath($outFile);
        $ec->setOutputFormat($format);
        $ec->setDocumentlist($s->getDocumentList());
        $ec->export();
        
        $this->assertTrue(filesize($outFile) > 0, sprintf("\"%s\" file not produced", $outFile));
        $this->verifyCsvContains($outFile, $separator, $enclosure, $expectedData, 0);
    }
    
    protected function verifyCsvContains($outFile, $separator, $enclosure, $expectedData, $columnId)
    {
        $results = fopen($outFile, "r");
        $resultData = array();
        while (($data = fgetcsv($results, 1000, $separator, $enclosure)) !== FALSE) {
            $docName = $data[$columnId];
            $resultData[$docName] = $data;
        }
        fclose($results);
        foreach ($expectedData as $docName => $docValues) {
            $this->assertTrue(isset($resultData[$docName]) , sprintf("%s document not found : %s", $docName, print_r($resultData, true)));
            foreach ($docValues as $index => $value) {
                if (strpos($value, "*") === false) {
                    $this->assertEquals($value, $resultData[$docName][$index], sprintf("%s  (index %s) : %s \n %s", $docName, $index, print_r($resultData, true) , $outFile));
                } else {
                    $this->assertEquals(preg_match('/' . $value . '/', $resultData[$docName][$index]) , 1, sprintf("expected \"%s\" %s  (index %s) : %s \n %s", $value, $docName, $index, print_r($resultData, true) , $outFile));
                }
            }
        }
    }
    /**
     * @dataProvider dataExportXmlSingle
     */
    public function testExportXmlSingle(array $expectedData)
    {
        $outFile = tempnam(getTmpDir() , 'tstexport');
        $s = new \SearchDoc(self::$dbaccess, $this->famName);
        $s->setObjectReturn();
        $s->search();
        
        $this->assertEmpty($s->searchError() , sprintf("Error in search %s", print_r($s->getSearchInfo() , true)));
        
        $ec = new \Dcp\ExportCollection();
        
        $ec->setOutputFilePath($outFile);
        $ec->setOutputFormat(\Dcp\ExportCollection::xmlFileOutputFormat);
        $ec->setDocumentlist($s->getDocumentList());
        $ec->export();
        
        $this->assertTrue(filesize($outFile) > 0, sprintf("\"%s\" file not produced", $outFile));
        
        $dom = new \DOMDocument();
        $dom->load($outFile);
        
        $this->XPathTesting($dom, $expectedData);
    }
    /**
     * @dataProvider dataExportXmlArchive
     */
    public function testExportXmlArchive($file, array $xmlPathes)
    {
        $outFile = tempnam(getTmpDir() , 'tstexport');
        $s = new \SearchDoc(self::$dbaccess, $this->famName);
        $s->setObjectReturn();
        $s->search();
        
        $this->assertEmpty($s->searchError() , sprintf("Error in search %s", print_r($s->getSearchInfo() , true)));
        
        $ec = new \Dcp\ExportCollection();
        
        $ec->setOutputFilePath($outFile);
        $ec->setOutputFormat(\Dcp\ExportCollection::xmlArchiveOutputFormat);
        $ec->setDocumentlist($s->getDocumentList());
        $ec->export();
        
        $this->assertTrue(filesize($outFile) > 0, sprintf("\"%s\" file not produced", $outFile));
        
        $outDir = tempnam(getTmpDir() , 'tstextract');
        if (is_file($outDir)) {
            unlink($outDir);
        }
        mkdir($outDir);
        $zip = new \ZipArchive;
        $res = $zip->open($outFile);
        
        $this->assertTrue($res, sprintf("\"%s\" cannot unarchive", $outFile));
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            if (preg_match("/" . $file . "/", basename($stat['name']))) {
                $file = $stat['name'];
                break;
            }
        }
        
        $zip->extractTo($outDir, array(
            $file
        ));
        $zip->close();
        
        $xmlFile = sprintf("%s/%s", $outDir, $file);
        $this->assertTrue(is_file($xmlFile) , sprintf("\"%s\" zip content not found", $xmlFile));
        
        $dom = new \DOMDocument();
        $dom->load($xmlFile);
        
        $this->XPathTesting($dom, $xmlPathes);
    }
    
    protected function XPathTesting(\DOMDocument $dom, array $expectedValues)
    {
        
        $xp = new \DOMXpath($dom);
        foreach ($expectedValues as $path => $value) {
            $entries = $xp->query($path);
            $found = 0;
            $foundValues = array();
            if (is_array($value)) {
                foreach ($entries as $entry) {
                    if ($entry->nodeValue == $value) {
                        $found++;
                    }
                    $foundValues[] = $entry->nodeValue;
                }
                $this->assertEquals($value, $foundValues, sprintf("Item \"%s\" not found in %s path, found \n\t%s\n", print_r($value, true) , $path, implode("\n\t", $foundValues)));
            } else {
                foreach ($entries as $entry) {
                    if ($entry->nodeValue == $value) $found++;
                    $foundValues[] = $entry->nodeValue;
                }
                $this->assertGreaterThan(0, $found, sprintf("Item \"%s\" not found in %s path, found \n\t%s\n", $value, $path, implode("\n\t", $foundValues)));
            }
        }
    }
    /**
     * @param $separator
     * @param $enclosure
     * @param array $expectedData
     * @dataProvider dataExportProfilCsv
     */
    public function testExportProfilCsv($separator, $enclosure, array $expectedData)
    {
        $outFile = tempnam(getTmpDir() , 'tstexport');
        $s = new \SearchDoc(self::$dbaccess, $this->famName);
        $s->setObjectReturn();
        $s->search();
        
        $this->assertEmpty($s->searchError() , sprintf("Error in search %s", print_r($s->getSearchInfo() , true)));
        
        $ec = new \Dcp\ExportCollection();
        
        $ec->setCvsEnclosure($enclosure);
        $ec->setCvsSeparator($separator);
        $ec->setOutputFilePath($outFile);
        $ec->setDocumentlist($s->getDocumentList());
        $ec->setExportProfil(true);
        $ec->export();
        $this->assertTrue(filesize($outFile) > 0, sprintf("\"%s\" file not produced", $outFile));
        $results = fopen($outFile, "r");
        $resultData = array();
        while (($data = fgetcsv($results, 1000, $separator, $enclosure)) !== FALSE) {
            if ($data[0] === "PROFIL") {
                $docName = $data[1];
                $resultData[$docName] = $data;
            }
        }
        fclose($results);
        foreach ($expectedData as $docName => $docValues) {
            $this->assertTrue(isset($resultData[$docName]) , sprintf("%s document not found : %s", $docName, print_r($resultData, true)));
            foreach ($docValues as $index => $value) {
                $this->assertEquals($value, $resultData[$docName][$index], sprintf("%s  (index %s) : %s", $docName, $index, print_r($resultData, true)));
            }
        }
    }
    /**
     * @param $separator
     * @param $enclosure
     * @param array $expectedData
     * @dataProvider dataExportFileCsv
     */
    public function testExportFileCsv($separator, $enclosure, $file, array $expectedData)
    {
        $outFile = tempnam(getTmpDir() , 'tstexportfile');
        $s = new \SearchDoc(self::$dbaccess, $this->famName);
        $s->setObjectReturn();
        $s->search();
        
        $this->assertEmpty($s->searchError() , sprintf("Error in search %s", print_r($s->getSearchInfo() , true)));
        
        $ec = new \Dcp\ExportCollection();
        
        $ec->setCvsEnclosure($enclosure);
        $ec->setCvsSeparator($separator);
        $ec->setOutputFilePath($outFile);
        $ec->setDocumentlist($s->getDocumentList());
        $ec->setExportFiles(true);
        $ec->export();
        $this->assertTrue(filesize($outFile) > 0, sprintf("\"%s\" file not produced", $outFile));
        
        $outDir = tempnam(getTmpDir() , 'tstextract');
        if (is_file($outDir)) {
            unlink($outDir);
        }
        mkdir($outDir);
        $zip = new \ZipArchive;
        $res = $zip->open($outFile);
        
        $this->assertTrue($res, sprintf("\"%s\" cannot unarchive", $outFile));
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            if (preg_match("/" . $file . "/", basename($stat['name']))) {
                $file = $stat['name'];
                break;
            }
        }
        
        $zip->extractTo($outDir, array(
            $file
        ));
        $zip->close();
        
        $contentFile = sprintf("%s/%s", $outDir, $file);
        $this->assertTrue(is_file($contentFile) , sprintf("\"%s\" zip content not found", $contentFile));
        
        $this->verifyCsvContains($contentFile, $separator, $enclosure, $expectedData, 2);
    }
    /**
     * @param $separator
     * @param $enclosure
     * @param array $expectedData
     * @dataProvider dataExportFamilyCsv
     */
    public function testExportFamilyCsv($separator, $enclosure, array $expectedData)
    {
        $outFile = tempnam(getTmpDir() , 'tstexport');
        $s = new \SearchDoc(self::$dbaccess, $this->famName);
        $s->setObjectReturn();
        $s->search();
        
        $this->assertEmpty($s->searchError() , sprintf("Error in search %s", print_r($s->getSearchInfo() , true)));
        
        $ec = new \Dcp\ExportCollection();
        
        $ec->setCvsEnclosure($enclosure);
        $ec->setCvsSeparator($separator);
        $ec->setOutputFilePath($outFile);
        $ec->setDocumentlist($s->getDocumentList());
        $ec->export();
        
        $this->assertTrue(filesize($outFile) > 0, sprintf("\"%s\" file not produced", $outFile));
        
        $this->verifyCsvContains($outFile, $separator, $enclosure, $expectedData, 2);
    }
    
    public function dataExportFamilyCsv()
    {
        return array(
            array(
                ";",
                '"',
                array(
                    "TST_EXPCOLL_DOC1" => array(
                        4 => "Titre 1",
                        5 => "1",
                        6 => "2014-02-23",
                        7 => "A",
                        
                        8 => "Un",
                        9 => "1.1",
                        10 => "Un long"
                    ) ,
                    "TST_EXPCOLL_DOC2" => array(
                        4 => "Titre 2",
                        5 => "2",
                        6 => "2014-12-24",
                        7 => "B",
                        8 => "Deux",
                        9 => "2.2",
                        10 => "Deux long",
                        11 => "TST_EXPCOLL_DOC1"
                    ) ,
                    "TST_EXPCOLL_DOC3" => array(
                        4 => "Titre 3",
                        11 => "TST_EXPCOLL_DOC1\nTST_EXPCOLL_DOC2"
                    )
                )
            )
        );
    }
    public function dataExportFileCsv()
    {
        return array(
            array(
                ";",
                '"',
                "fdl.csv",
                array(
                    "TST_EXPCOLL_DOC1" => array(
                        4 => "Titre 1",
                        5 => "1",
                        6 => "2014-02-23",
                        7 => "A",
                        
                        8 => "Un",
                        11 => "1.1",
                        12 => "Un long"
                    ) ,
                    "TST_EXPCOLL_DOC2" => array(
                        4 => "Titre 2",
                        5 => "2",
                        6 => "2014-12-24",
                        7 => "B",
                        8 => "Deux",
                        11 => "2.2",
                        12 => "Deux long",
                        13 => "TST_EXPCOLL_DOC1"
                    ) ,
                    "TST_EXPCOLL_DOC3" => array(
                        4 => "Titre 3",
                        13 => "TST_EXPCOLL_DOC1\nTST_EXPCOLL_DOC2"
                    ) ,
                    "TST_EXPCOLL_DOC6" => array(
                        4 => "Titre 6",
                        9 => ".*\\/Hello.txt",
                        10 => ".*\\/red.png"
                    )
                )
            )
        );
    }
    public function dataExportProfilCsv()
    {
        return array(
            array(
                ",",
                '"',
                array(
                    "TST_PRF_EXPCOLL" => array(
                        2 => ":useAccount",
                        4 => "view=uexpcoll1",
                        5 => "edit=uexpcoll2"
                    ) ,
                    "TST_EXPCOLL_DOC1" => array(
                        2 => "TST_PRF_EXPCOLL"
                    ) ,
                    "TST_EXPCOLL_DOC2" => array(
                        2 => "TST_PRF_EXPCOLL"
                    ) ,
                    "TST_EXPCOLL_DOC3" => array(
                        2 => "TST_PRF_EXPCOLL"
                    ) ,
                    "TST_EXPCOLL_DOC4" => array(
                        2 => "TST_PRF_EXPCOLL"
                    ) ,
                    "TST_EXPCOLL_DOC5" => array(
                        2 => "TST_PRF_EXPCOLL"
                    ) ,
                    "TST_EXPCOLL_DOC6" => array(
                        2 => "TST_PRF_EXPCOLL"
                    )
                )
            )
        );
    }
    public function dataExportXmlArchive()
    {
        return array(
            array(
                "Titre1.*.xml",
                array(
                    "tst_frame1/tst_title" => "Titre 1",
                    "tst_frame1/tst_number" => "1",
                    "tst_frame1/tst_date" => "2014-02-23"
                )
            ) ,
            array(
                "Titre2.*.xml",
                array(
                    "tst_frame1/tst_number" => "2",
                    "tst_frame1/tst_date" => "2014-12-24"
                )
            ) ,
            array(
                "Titre3.*.xml",
                array(
                    "tst_frame1/tst_number" => "3",
                    "tst_tab_i/tst_frame2/tst_longtext" => "Trois long",
                    // "tst_tab_i/tst_frame2/tst_array/tst_othertexts" => "Une deuxième",
                    "tst_tab_i/tst_frame2/tst_array/tst_othertexts" => array(
                        "Une ligne<BR>avec retour",
                        "Une deuxième"
                    )
                )
            )
        );
    }
    public function dataExportXmlSingle()
    {
        return array(
            array(
                array(
                    $this->famName . "[@name = \"TST_EXPCOLL_DOC1\"]/tst_frame1/tst_number" => "1",
                    $this->famName . "[@name = \"TST_EXPCOLL_DOC1\"]/tst_frame1/tst_date" => "2014-02-23",
                    $this->famName . "[@name = \"TST_EXPCOLL_DOC2\"]/tst_frame1/tst_number" => "2"
                )
            )
        );
    }
    public function dataExportDisplayCsv()
    {
        return array(
            array(
                \Dcp\ExportCollection::csvDisplayValueOutputFormat,
                ";",
                '"',
                array(
                    "Titre 1" => array(
                        0 => "Titre 1",
                        1 => "1",
                        2 => "23/02/2014",
                        3 => "La",
                        4 => "Un",
                        5 => "1.1",
                        6 => "Un long"
                    ) ,
                    "Titre 2" => array(
                        0 => "Titre 2",
                        1 => "2",
                        2 => "24/12/2014",
                        3 => "Si",
                        4 => "Deux",
                        5 => "2.2",
                        6 => "Deux long",
                        7 => "Titre 1"
                    ) ,
                    "Titre 3" => array(
                        0 => "Titre 3",
                        7 => "Titre 1\nTitre 2",
                        8 => "Une ligne\navec retour\nUne deuxième"
                    )
                )
            ) ,
            array(
                \Dcp\ExportCollection::csvDisplayValueOutputFormat,
                ",",
                '"',
                array(
                    "Titre 1" => array(
                        0 => "Titre 1",
                        1 => "1",
                        2 => "23/02/2014",
                        3 => "La",
                        4 => "Un",
                        5 => "1.1",
                        6 => "Un long"
                    ) ,
                    "Titre 2" => array(
                        0 => "Titre 2",
                        1 => "2",
                        2 => "24/12/2014",
                        3 => "Si",
                        4 => "Deux",
                        5 => "2.2",
                        6 => "Deux long"
                    )
                )
            ) ,
            array(
                \Dcp\ExportCollection::csvRawOnlyDataOutputFormat,
                ";",
                '"',
                array(
                    "Titre 1" => array(
                        0 => "Titre 1",
                        1 => "1",
                        2 => "2014-02-23",
                        3 => "A",
                        4 => "Un",
                        5 => "1.1",
                        6 => "Un long"
                    ) ,
                    "Titre 2" => array(
                        0 => "Titre 2",
                        1 => "2",
                        2 => "2014-12-24",
                        3 => "B",
                        4 => "Deux",
                        5 => "2.2",
                        6 => "Deux long"
                    ) ,
                    "Titre 3" => array(
                        0 => "Titre 3",
                        7 => "TST_EXPCOLL_DOC1\nTST_EXPCOLL_DOC2",
                        8 => "Une ligne<BR>avec retour\nUne deuxième"
                    )
                )
            )
        );
    }
    public function dataExportCsv()
    {
        return array(
            array(
                ";",
                '"',
                array(
                    "TST_EXPCOLL_DOC1" => array(
                        4 => "Titre 1",
                        5 => "1",
                        6 => "2014-02-23",
                        7 => "A",
                        
                        8 => "Un",
                        9 => "1.1",
                        10 => "Un long"
                    ) ,
                    "TST_EXPCOLL_DOC2" => array(
                        4 => "Titre 2",
                        5 => "2",
                        6 => "2014-12-24",
                        7 => "B",
                        8 => "Deux",
                        9 => "2.2",
                        10 => "Deux long",
                        11 => "TST_EXPCOLL_DOC1"
                    ) ,
                    "TST_EXPCOLL_DOC3" => array(
                        4 => "Titre 3",
                        11 => "TST_EXPCOLL_DOC1\nTST_EXPCOLL_DOC2"
                    )
                )
            ) ,
            array(
                ",",
                "'",
                array(
                    "TST_EXPCOLL_DOC1" => array(
                        4 => "Titre 1",
                        5 => "1",
                        6 => "2014-02-23",
                        7 => "A",
                        8 => "Un",
                        9 => "1.1",
                        10 => "Un long"
                    ) ,
                    "TST_EXPCOLL_DOC2" => array(
                        4 => "Titre 2",
                        5 => "2",
                        6 => "2014-12-24",
                        7 => "B",
                        8 => "Deux",
                        9 => "2.2",
                        10 => "Deux long"
                    )
                )
            )
        );
    }
}
