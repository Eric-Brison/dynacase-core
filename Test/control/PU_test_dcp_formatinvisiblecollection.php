<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Pu;
/**
 * @author Anakeen
 * @package Dcp\Pu
 */

require_once 'PU_testcase_dcp_commonfamily.php';
class TestFormatInvisibleCollection extends TestCaseDcpCommonFamily
{
    /**
     * import TST_FMTCOL
     * @static
     * @return string
     */
    protected static function getCommonImportFile()
    {
        return array(
            "PU_data_dcp_invisible.ods"
        );
    }
    
    protected $famName = 'tst_invisiblefamily1';
    /**
     * @dataProvider dataRenderInvisibleCollection
     */
    public function testRenderInvisibleCollection($login, $docName, array $expectedValues)
    {
        $this->sudo($login);
        $s = new \SearchDoc(self::$dbaccess, $this->famName);
        $s->setObjectReturn();
        $dl = $s->search()->getDocumentList();
        
        $fc = new \FormatCollection();
        $fc->useCollection($dl);
        $fc->relationNoAccessText = 'no grant';
        $fc->addProperty($fc::propName);
        foreach ($expectedValues as $aid => $value) {
            $fc->addAttribute($aid);
        }
        
        $r = $fc->render();
        foreach ($expectedValues as $aid => $value) {
            $this->assertTrue($this->getRenderValue($r, $docName, $aid) !== null, "$aid not found");
            $this->assertEquals($value, $this->getRenderValue($r, $docName, $aid)->displayValue, sprintf("%s [%s]<>\n%s", $aid, $value, print_r($this->getRenderValue($r, $docName, $aid) , true)));
        }
        $this->exitSudo();
    }
    /**
     * @dataProvider dataRenderNoVerifyAccessCollection
     */
    public function testRenderNoVerifyAccessCollection($login, $docName, array $expectedValues)
    {
        $this->sudo($login);
        $s = new \SearchDoc(self::$dbaccess, $this->famName);
        $s->setObjectReturn();
        $dl = $s->search()->getDocumentList();
        
        $fc = new \FormatCollection();
        $fc->setVerifyAttributeAccess(false);
        $fc->useCollection($dl);
        $fc->relationNoAccessText = 'no grant';
        $fc->addProperty($fc::propName);
        foreach ($expectedValues as $aid => $value) {
            $fc->addAttribute($aid);
        }
        
        $r = $fc->render();
        foreach ($expectedValues as $aid => $value) {
            $this->assertTrue($this->getRenderValue($r, $docName, $aid) !== null, "$aid not found");
            $this->assertEquals($value, $this->getRenderValue($r, $docName, $aid)->displayValue, sprintf("%s [%s]<>\n%s", $aid, $value, print_r($this->getRenderValue($r, $docName, $aid) , true)));
        }
        $this->exitSudo();
    }
    /**
     * @dataProvider dataExportNoVerifyAccessCollection
     */
    public function testExportNoVerifyAccessCollection($login, array $expectedValues)
    {
        $this->sudo($login);
        
        $outFile = tempnam(getTmpDir() , 'tstexport');
        $s = new \SearchDoc(self::$dbaccess, $this->famName);
        $s->setObjectReturn();
        $dl = $s->search()->getDocumentList();
        
        $ec = new \Dcp\ExportCollection();
        $ec->setVerifyAttributeAccess(false);
        $ec->setDocumentlist($dl);
        $separator = ',';
        $enclosure = '"';
        $ec->setCvsEnclosure($enclosure);
        $ec->setCvsSeparator($separator);
        $ec->setOutputFilePath($outFile);
        $ec->export();
        
        $this->verifyCsvContains($outFile, $separator, $enclosure, $expectedValues, 2);
        
        $this->exitSudo();
    }
    /**
     * @dataProvider dataExportVerifyAccessCollection
     */
    public function testExportVerifyAccessCollection($login, array $expectedValues)
    {
        $this->sudo($login);
        
        $outFile = tempnam(getTmpDir() , 'tstexport');
        $s = new \SearchDoc(self::$dbaccess, $this->famName);
        $s->setObjectReturn();
        $dl = $s->search()->getDocumentList();
        
        $ec = new \Dcp\ExportCollection();
        $ec->setVerifyAttributeAccess(true);
        $ec->setDocumentlist($dl);
        $separator = ',';
        $enclosure = '"';
        $ec->setCvsEnclosure($enclosure);
        $ec->setCvsSeparator($separator);
        $ec->setOutputFilePath($outFile);
        $ec->export();
        
        $this->verifyCsvContains($outFile, $separator, $enclosure, $expectedValues, 2);
        
        $this->exitSudo();
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
    public function testExportXmlSingle($login, array $expectedData)
    {
        $this->sudo($login);
        $outFile = tempnam(getTmpDir() , 'tstexport');
        $s = new \SearchDoc(self::$dbaccess, $this->famName);
        $s->setObjectReturn();
        $s->search();
        
        $this->assertEmpty($s->searchError() , sprintf("Error in search %s", print_r($s->getSearchInfo() , true)));
        
        $ec = new \Dcp\ExportCollection();
        
        $ec->setOutputFilePath($outFile);
        $ec->setOutputFormat(\Dcp\ExportCollection::xmlFileOutputFormat);
        $ec->setDocumentlist($s->getDocumentList());
        $ec->setVerifyAttributeAccess(true);
        $ec->export();
        
        $this->assertTrue(filesize($outFile) > 0, sprintf("\"%s\" file not produced", $outFile));
        
        $dom = new \DOMDocument();
        $dom->load($outFile);
        
        $this->XPathTesting($dom, $expectedData);
        $this->exitSudo();
    }
    protected function XPathTesting(\DOMDocument $dom, array $expectedValues)
    {
        
        $xp = new \DOMXpath($dom);
        foreach ($expectedValues as $path => $value) {
            $entries = $xp->query($path);
            $found = 0;
            $foundValues = array();
            
            foreach ($entries as $entry) {
                if ($entry->nodeValue == $value) $found++;
                $foundValues[] = $entry->nodeValue;
            }
            $this->assertGreaterThan(0, $found, sprintf("Item \"%s\" not found in %s path, found \n\t%s\n", $value, $path, implode("\n\t", $foundValues)));
        }
    }
    /**
     * @param array $r
     * @param $docName
     * @param $attrName
     * @return \StandardAttributeValue
     */
    private function getRenderValue(array $r, $docName, $attrName)
    {
        foreach ($r as $format) {
            if ($format["properties"]["name"] == $docName) {
                if ($format["attributes"][$attrName] === null) {
                    return new \StandardAttributeValue("", null);
                } else {
                    return $format["attributes"][$attrName];
                }
            }
        }
        return null;
    }
    
    public function dataExportXmlSingle()
    {
        return array(
            array(
                "uinvisible_1",
                array(
                    $this->famName . '[@name = "TST_INVISIBLE_DOC1"]/tst_frame1/tst_number' => "1",
                    $this->famName . '[@name = "TST_INVISIBLE_DOC1"]/tst_frame1/tst_text[@granted = "false"]' => "",
                    $this->famName . '[@name = "TST_INVISIBLE_DOC1"]/tst_tab_i/tst_frame2/tst_decimal[@granted = "false"]' => "",
                    
                    $this->famName . '[@name = "TST_INVISIBLE_DOC2"]/tst_frame1/tst_number[@granted = "false"]' => "",
                    $this->famName . '[@name = "TST_INVISIBLE_DOC2"]/tst_frame1/tst_text[@granted = "false"]' => "",
                    $this->famName . '[@name = "TST_INVISIBLE_DOC2"]/tst_tab_i/tst_frame2/tst_decimal' => "2.2",
                    
                    $this->famName . '[@name = "TST_INVISIBLE_DOC3"]/tst_frame1/tst_number' => "3",
                    $this->famName . '[@name = "TST_INVISIBLE_DOC3"]/tst_frame1/tst_text[@granted = "false"]' => "",
                    $this->famName . '[@name = "TST_INVISIBLE_DOC3"]/tst_tab_i/tst_frame2/tst_decimal[@granted = "false"]' => "",
                )
            ) ,
            array(
                "uinvisible_2",
                array(
                    
                    $this->famName . '[@name = "TST_INVISIBLE_DOC3"]/tst_frame1/tst_number[@granted = "false"]' => "",
                    $this->famName . '[@name = "TST_INVISIBLE_DOC3"]/tst_frame1/tst_text[@granted = "false"]' => "",
                    $this->famName . '[@name = "TST_INVISIBLE_DOC3"]/tst_tab_i/tst_frame2/tst_decimal' => "3.3",
                )
            )
        );
    }
    
    public function dataExportVerifyAccessCollection()
    {
        return array(
            array(
                "uinvisible_1",
                array(
                    "TST_INVISIBLE_DOC1" => array(
                        4 => "Titre 1", // title
                        5 => "1", // number
                        6 => \FormatCollection::noAccessText, // text
                        7 => \FormatCollection::noAccessText, // decimal
                        8 => \FormatCollection::noAccessText, // longtext
                        9 => \FormatCollection::noAccessText
                        // docid
                        
                    ) ,
                    "TST_INVISIBLE_DOC2" => array(
                        4 => "Titre 2",
                        5 => \FormatCollection::noAccessText,
                        6 => \FormatCollection::noAccessText,
                        7 => "2.2",
                        8 => "Deux long",
                        9 => "TST_INVISIBLE_DOC1"
                    ) ,
                    "TST_INVISIBLE_DOC3" => array(
                        4 => "Titre 3",
                        5 => "3", // number
                        6 => \FormatCollection::noAccessText, // text
                        7 => \FormatCollection::noAccessText, // decimal
                        8 => \FormatCollection::noAccessText
                        // longtext
                        
                    )
                )
            )
        );
    }
    public function dataExportNoVerifyAccessCollection()
    {
        return array(
            array(
                "uinvisible_1",
                array(
                    "TST_INVISIBLE_DOC1" => array(
                        4 => "Titre 1",
                        5 => "1",
                        6 => "Un",
                        7 => "1.1",
                        8 => "Un long"
                    ) ,
                    "TST_INVISIBLE_DOC2" => array(
                        4 => "Titre 2",
                        5 => "2",
                        6 => "Deux",
                        7 => "2.2",
                        8 => "Deux long",
                        9 => "TST_INVISIBLE_DOC1"
                    ) ,
                    "TST_INVISIBLE_DOC3" => array(
                        4 => "Titre 3",
                        9 => "TST_INVISIBLE_DOC1\nTST_INVISIBLE_DOC2"
                    )
                )
            )
        );
    }
    
    public function dataRenderInvisibleCollection()
    {
        return array(
            array( // MASK TST_INVMASK_E1
                "uinvisible_1",
                "TST_INVISIBLE_DOC1",
                array(
                    "tst_title" => "Titre 1",
                    "tst_text" => \FormatCollection::noAccessText,
                    "tst_number" => "1",
                    "tst_longtext" => \FormatCollection::noAccessText,
                    "tst_decimal" => \FormatCollection::noAccessText,
                    "tst_docid" => \FormatCollection::noAccessText
                )
            ) ,
            array( // MASK TST_INVMASK_E2
                "uinvisible_1",
                "TST_INVISIBLE_DOC2",
                array(
                    "tst_title" => "Titre 2",
                    "tst_text" => \FormatCollection::noAccessText,
                    "tst_number" => \FormatCollection::noAccessText,
                    "tst_longtext" => "Deux long",
                    "tst_decimal" => "2,2"
                )
            ) ,
            array( // MASK TST_INVMASK_E1
                "uinvisible_1",
                "TST_INVISIBLE_DOC3",
                array(
                    "tst_title" => "Titre 3",
                    "tst_text" => \FormatCollection::noAccessText,
                    "tst_number" => "3",
                    "tst_longtext" => \FormatCollection::noAccessText,
                    "tst_decimal" => \FormatCollection::noAccessText
                )
            ) ,
            array( // MASK TST_INVMASK_E1
                "uinvisible_1",
                "TST_INVISIBLE_DOC4",
                array(
                    "tst_title" => "Titre 4",
                    "tst_text" => \FormatCollection::noAccessText,
                    "tst_number" => "4",
                    "tst_longtext" => \FormatCollection::noAccessText,
                    "tst_decimal" => \FormatCollection::noAccessText
                )
            ) ,
            array( // MASK TST_INVMASK_E1
                "uinvisible_1",
                "TST_INVISIBLE_DOC5",
                array(
                    "tst_title" => "Titre 5",
                    "tst_text" => \FormatCollection::noAccessText,
                    "tst_number" => \FormatCollection::noAccessText,
                    "tst_longtext" => \FormatCollection::noAccessText,
                    "tst_decimal" => \FormatCollection::noAccessText
                )
            ) ,
            array( // MASK TST_INVMASK_E2
                "uinvisible_2",
                "TST_INVISIBLE_DOC3",
                array(
                    "tst_title" => "Titre 3",
                    "tst_text" => \FormatCollection::noAccessText,
                    "tst_number" => \FormatCollection::noAccessText,
                    "tst_longtext" => "Trois long",
                    "tst_decimal" => "3,3"
                )
            ) ,
            array( // MASK TST_INVMASK_E2
                "uinvisible_2",
                "TST_INVISIBLE_DOC4",
                array(
                    "tst_title" => "Titre 4",
                    "tst_text" => \FormatCollection::noAccessText,
                    "tst_number" => \FormatCollection::noAccessText,
                    "tst_longtext" => "Quatre long",
                    "tst_decimal" => "4,4"
                )
            ) ,
            array( // MASK TST_INVMASK_E1
                "uinvisible_3",
                "TST_INVISIBLE_DOC3",
                array(
                    "tst_title" => "Titre 3",
                    "tst_text" => \FormatCollection::noAccessText,
                    "tst_number" => "3",
                    "tst_longtext" => \FormatCollection::noAccessText,
                    "tst_decimal" => \FormatCollection::noAccessText
                )
            ) ,
            array( // MASK TST_INVMASK_E4
                "uinvisible_3",
                "TST_INVISIBLE_DOC4",
                array(
                    "tst_title" => "Titre 4",
                    "tst_text" => "Quatre",
                    "tst_number" => "4",
                    "tst_longtext" => "Quatre long",
                    "tst_decimal" => "4,4"
                )
            ) ,
            array( // no mask
                "uinvisible_4",
                "TST_INVISIBLE_DOC3",
                array(
                    "tst_title" => "Titre 3",
                    "tst_text" => \FormatCollection::noAccessText,
                    "tst_number" => \FormatCollection::noAccessText,
                    "tst_longtext" => \FormatCollection::noAccessText,
                    "tst_decimal" => \FormatCollection::noAccessText
                )
            ) ,
            array( // MASK TST_INVMASK_E4
                "uinvisible_4",
                "TST_INVISIBLE_DOC4",
                array(
                    "tst_title" => "Titre 4",
                    "tst_text" => "Quatre",
                    "tst_number" => "4",
                    "tst_longtext" => "Quatre long",
                    "tst_decimal" => "4,4"
                )
            )
        );
    }
    
    public function dataRenderNoVerifyAccessCollection()
    {
        return array(
            array( // MASK TST_INVMASK_E1
                "uinvisible_1",
                "TST_INVISIBLE_DOC1",
                array(
                    "tst_title" => "Titre 1",
                    "tst_text" => "Un",
                    "tst_number" => "1",
                    "tst_longtext" => "Un long",
                    "tst_decimal" => "1,1"
                )
            ) ,
            array( // MASK TST_INVMASK_E2
                "uinvisible_1",
                "TST_INVISIBLE_DOC2",
                array(
                    "tst_title" => "Titre 2",
                    "tst_text" => "Deux",
                    "tst_number" => "2",
                    "tst_longtext" => "Deux long",
                    "tst_decimal" => "2,2"
                )
            ) ,
            array( // MASK TST_INVMASK_E1
                "uinvisible_1",
                "TST_INVISIBLE_DOC3",
                array(
                    "tst_title" => "Titre 3",
                    "tst_text" => "Trois",
                    "tst_number" => "3",
                    "tst_longtext" => "Trois long",
                    "tst_decimal" => "3,3"
                )
            ) ,
            array( // MASK TST_INVMASK_E1
                "uinvisible_1",
                "TST_INVISIBLE_DOC4",
                array(
                    "tst_title" => "Titre 4",
                    "tst_text" => "Quatre",
                    "tst_number" => "4",
                    "tst_longtext" => "Quatre long",
                    "tst_decimal" => "4,4"
                )
            ) ,
            array( // MASK TST_INVMASK_E1
                "uinvisible_1",
                "TST_INVISIBLE_DOC5",
                array(
                    "tst_title" => "Titre 5",
                    "tst_text" => "Cinq",
                    "tst_number" => "5",
                    "tst_longtext" => "Cinq long",
                    "tst_decimal" => "5,5"
                )
            ) ,
            array( // MASK TST_INVMASK_E2
                "uinvisible_2",
                "TST_INVISIBLE_DOC3",
                array(
                    "tst_title" => "Titre 3",
                    "tst_text" => "Trois",
                    "tst_number" => "3",
                    "tst_longtext" => "Trois long",
                    "tst_decimal" => "3,3"
                )
            ) ,
            array( // MASK TST_INVMASK_E2
                "uinvisible_2",
                "TST_INVISIBLE_DOC4",
                array(
                    "tst_title" => "Titre 4",
                    "tst_text" => "Quatre",
                    "tst_number" => "4",
                    "tst_longtext" => "Quatre long",
                    "tst_decimal" => "4,4"
                )
            ) ,
            array( // MASK TST_INVMASK_E1
                "uinvisible_3",
                "TST_INVISIBLE_DOC3",
                array(
                    "tst_title" => "Titre 3",
                    "tst_text" => "Trois",
                    "tst_number" => "3",
                    "tst_longtext" => "Trois long",
                    "tst_decimal" => "3,3"
                )
            ) ,
            array( // MASK TST_INVMASK_E4
                "uinvisible_3",
                "TST_INVISIBLE_DOC4",
                array(
                    "tst_title" => "Titre 4",
                    "tst_text" => "Quatre",
                    "tst_number" => "4",
                    "tst_longtext" => "Quatre long",
                    "tst_decimal" => "4,4"
                )
            ) ,
            array( // no mask
                "uinvisible_4",
                "TST_INVISIBLE_DOC3",
                array(
                    "tst_title" => "Titre 3",
                    "tst_text" => "Trois",
                    "tst_number" => "3",
                    "tst_longtext" => "Trois long",
                    "tst_decimal" => "3,3"
                )
            ) ,
            array( // MASK TST_INVMASK_E4
                "uinvisible_4",
                "TST_INVISIBLE_DOC4",
                array(
                    "tst_title" => "Titre 4",
                    "tst_text" => "Quatre",
                    "tst_number" => "4",
                    "tst_longtext" => "Quatre long",
                    "tst_decimal" => "4,4"
                )
            )
        );
    }
}
?>
