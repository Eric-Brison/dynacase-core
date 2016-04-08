<?php
/*
 * @author Anakeen
 * @package Dcp\Pu
*/

namespace Dcp\Pu;

require_once 'PU_testcase_dcp.php';

class TestExportRevision extends TestCaseDcpCommonFamily
{
    static function getCommonImportFile()
    {
        return array(
            "PU_data_dcp_exportfamilyrevision.ods"
        );
    }
    protected $famName = "tst_export_revision";
    
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        
        $d4 = new_doc(self::$dbaccess, "TST_EREV4");
        $d5 = new_doc(self::$dbaccess, "TST_EREV5");
        $d6 = new_doc(self::$dbaccess, "TST_EREV6");
        
        $d1 = new_doc(self::$dbaccess, "TST_EREV1");
        $d1->revise();
        $d1->setValue(\Dcp\AttributeIdentifiers\tst_export_revision::tst_title, "Référence n°2");
        $d1->store();
        
        $d4->setValue(\Dcp\AttributeIdentifiers\tst_export_revision::tst_doc_fixed, $d1->id);
        
        $d2 = new_doc(self::$dbaccess, "TST_EREV2");
        $d2->revise();
        $d2->setValue(\Dcp\AttributeIdentifiers\tst_export_revision::tst_title, "Identifiant n°2");
        $d2->store();
        
        $d6->setValue(\Dcp\AttributeIdentifiers\tst_export_revision::tst_doc_fixed, $d2->id);
        $d5->setValue(\Dcp\AttributeIdentifiers\tst_export_revision::tst_doc_state, $d2->id);
        
        $d2->revise();
        $d2->setValue(\Dcp\AttributeIdentifiers\tst_export_revision::tst_title, "Identifiant n°3");
        $d2->store();
        
        $d5->setValue(\Dcp\AttributeIdentifiers\tst_export_revision::tst_doc_fixed, $d2->id);
        
        $d3 = new_doc(self::$dbaccess, "TST_EREV3");
        $d3->revise();
        $d5->setValue(\Dcp\AttributeIdentifiers\tst_export_revision::tst_doc_state, $d3->id);
        $d3->setValue(\Dcp\AttributeIdentifiers\tst_export_revision::tst_title, "Révision n°2");
        $d3->store();
        $d3->revise();
        $d3->setValue(\Dcp\AttributeIdentifiers\tst_export_revision::tst_title, "Révision n°3");
        $d3->store();
        
        $d6->setValue(\Dcp\AttributeIdentifiers\tst_export_revision::tst_doc_state, $d3->id);
        
        $d3->revise();
        $d3->setValue(\Dcp\AttributeIdentifiers\tst_export_revision::tst_title, "Révision n°4");
        $d3->store();
        
        $d6->setValue(\Dcp\AttributeIdentifiers\tst_export_revision::tst_doc_fixed, $d3->id);
        
        $d4->store();
        $d5->store();
        $d6->store();
    }
    /**
     * @param $separator
     * @param $enclosure
     * @param array $expectedData
     * @dataProvider dataExportCsv
     */
    public function testExportRevisionCsv($separator, $enclosure, array $expectedData)
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
     * @param $separator
     * @param $enclosure
     * @param array $expectedData
     * @dataProvider dataExportRevisionXml
     */
    public function testExportRevisionXml(array $expectedData)
    {
        $outFile = tempnam(getTmpDir() , 'tstexport');
        $s = new \SearchDoc(self::$dbaccess, $this->famName);
        $s->setObjectReturn();
        $s->search();
        
        $this->assertEmpty($s->searchError() , sprintf("Error in search %s", print_r($s->getSearchInfo() , true)));
        
        $ec = new \Dcp\ExportCollection();
        
        $ec->setOutputFormat(\Dcp\ExportCollection::xmlFileOutputFormat);
        $ec->setOutputFilePath($outFile);
        $ec->setDocumentlist($s->getDocumentList());
        $ec->export();
        
        $this->assertTrue(filesize($outFile) > 0, sprintf("\"%s\" file not produced", $outFile));
        
        $dom = new \DOMDocument();
        $dom->load($outFile);
        
        $this->XPathTesting($dom, $expectedData);
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
                $revName = "";
                if (preg_match("/([A-Z_0-9]+)#([0-9]+)/", $value, $reg)) {
                    simpleQuery("", sprintf("select id from docread where name='%s' and revision=%d", pg_escape_string($reg[1]) , $reg[2]) , $value, true, true);
                    simpleQuery("", sprintf("select name, revision from docread where id=%d", $resultData[$docName][$index]) , $revName, false, true);
                }
                
                if (strpos($value, "*") === false) {
                    $this->assertEquals($value, $resultData[$docName][$index], sprintf("%s  (index %s : %s) : %s \n %s \n %s", $docName, $index, $docValues[$index], print_r($revName, true) , print_r($resultData, true) , $outFile));
                } else {
                    $this->assertEquals(preg_match('/' . $value . '/', $resultData[$docName][$index]) , 1, sprintf("expected \"%s\" %s  (index %s) : %s \n %s", $docValues[$index], $docName, $index, print_r($resultData, true) , $outFile));
                }
            }
        }
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
    public function dataExportRevisionXml()
    {
        return array(
            array(
                array(
                    $this->famName . "[@name = \"TST_EREV1\"]/tst_frame/tst_int" => "1",
                    $this->famName . "[@name = \"TST_EREV2\"]/tst_frame/tst_int" => "2",
                    $this->famName . "[@name = \"TST_EREV3\"]/tst_frame/tst_int" => "3",
                    $this->famName . "[@name = \"TST_EREV4\"]/tst_frame/tst_doc_fixed[@name= \"TST_EREV1\"][@revision = \"1\"]" => "Référence n°2",
                    $this->famName . "[@name = \"TST_EREV5\"]/tst_frame/tst_doc_fixed[@name= \"TST_EREV2\"][@revision = \"2\"]" => "Identifiant n°3",
                    $this->famName . "[@name = \"TST_EREV6\"]/tst_frame/tst_doc_fixed[@name= \"TST_EREV3\"][@revision = \"3\"]" => "Révision n°4",
                    $this->famName . "[@name = \"TST_EREV4\"]/tst_frame/tst_doc_state[@name= \"TST_EREV1\"][@revision = \"state:first\"]" => "Référence n°1",
                    $this->famName . "[@name = \"TST_EREV5\"]/tst_frame/tst_doc_state[@name= \"TST_EREV3\"][@revision = \"state:first\"]" => "Révision n°2",
                    $this->famName . "[@name = \"TST_EREV6\"]/tst_frame/tst_doc_state[@name= \"TST_EREV3\"][@revision = \"state:first\"]" => "Révision n°3",
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
                    "TST_EREV1" => array(
                        4 => "Référence n°2",
                        5 => "1"
                    ) ,
                    "TST_EREV2" => array(
                        4 => "Identifiant n°3",
                        5 => "2"
                    ) ,
                    "TST_EREV3" => array(
                        4 => "Révision n°4",
                        5 => "3"
                    ) ,
                    "TST_EREV4" => array(
                        4 => "Un",
                        5 => "1",
                        6 => "TST_EREV1",
                        7 => "TST_EREV1#1",
                        8 => "TST_EREV1#0"
                    ) ,
                    "TST_EREV5" => array(
                        4 => "A",
                        5 => "2",
                        6 => "TST_EREV2",
                        7 => "TST_EREV2#2",
                        8 => "TST_EREV3#1"
                    ) ,
                    "TST_EREV6" => array(
                        4 => "Hello",
                        5 => "3",
                        6 => "TST_EREV3",
                        7 => "TST_EREV3#3",
                        8 => "TST_EREV3#2"
                    )
                )
            )
        );
    }
}
?>