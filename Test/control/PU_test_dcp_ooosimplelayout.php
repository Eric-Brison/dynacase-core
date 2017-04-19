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

require_once 'PU_testcase_dcp_document.php';

class TestOooSimpleLayout extends TestCaseDcpDocument
{
    protected static $outputDir;
    
    protected function tearDown()
    {
    }
    
    protected function setUp()
    {
    }
    
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        // self::$outputDir = uniqid(getTmpDir() . "/oootest-");
        self::$outputDir = (getTmpDir() . "/oootest");
        if (!is_dir(self::$outputDir)) mkdir(self::$outputDir);
        self::connectUser();
        self::beginTransaction();
        
        self::importDocument("PU_data_dcp_oooSimpleLayout.ods");
        self::importDocument("PU_data_dcp_oooSimpleLayout.xml");
        // replace real img reference into document testing
        $img = new_doc(self::$dbaccess, "TST_HTMLIMAGE");
        self::assertTrue($img->isAlive() , "no found reference image");
        
        $htmlImage = new_doc(self::$dbaccess, "TST_OOOS5");
        self::assertTrue($htmlImage->isAlive() , "no found reference html");
        $imgInfo = $img->getFileInfo($img->getRawValue("img_file"));
        $htmlImage->setValue("tst_html", str_replace("1234567", $imgInfo["id_file"], $htmlImage->getRawValue("tst_html")));
        $htmlImage->modify();
    }
    
    public static function tearDownAfterClass()
    {
        self::rollbackTransaction();
        TestSuiteDcp::addMessage(sprintf("Results for %s in file://%s", __CLASS__, self::$outputDir));
    }
    
    protected function saveFileResult($file, $name)
    {
        copy($file, self::$outputDir . '/' . $name);
    }
    
    protected function extractFile($docName, $template, $extractName)
    {
        $doc = new_doc(self::$dbaccess, $docName);
        $this->assertTrue($doc->isAlive() , sprintf("document %s is not alive", $docName));
        
        $file = $doc->viewDoc("DCPTEST:" . $template . ":B");
        $this->assertTrue(file_exists($file) , "fail layout $template");
        
        $copyFileName = sprintf("%s_%s.odt", str_replace('.odt', '', $template) , $docName);
        $this->saveFileResult($file, $copyFileName);
        
        $cmd = sprintf('cd %s;rm -f %s;unzip -q %s %s', self::$outputDir, $extractName, $copyFileName, $extractName);
        
        system($cmd, $status);
        $this->assertEquals(0, $status);
        $extractFile = sprintf('%s/%s', self::$outputDir, $extractName);
        return $extractFile;
    }
    
    protected function XPathTesting(\DOMDocument $dom, array $expectedValues)
    {
        
        $xp = new \DOMXpath($dom);
        foreach ($expectedValues as $pathValue) {
            list($path, $value) = $pathValue;
            $entries = $xp->query($path);
            $found = 0;
            $foundValues = array();
            foreach ($entries as $entry) {
                if ($entry->nodeValue == $value) $found++;
                $foundValues[] = $entry->nodeValue;
            }
            
            $this->assertGreaterThan(0, $found, sprintf("Item \"%s\" not found in %s path, found \n\t%s\n", print_r($value, true) , $path, implode("\n\t", $foundValues)));
        }
    }
    /**
     * @dataProvider dataContent
     * @param string $docName
     * @param string $template
     * @param array $expectedValues
     */
    public function testContent($docName, $template, array $expectedValues)
    {
        
        $contentFile = $this->extractFile($docName, $template, 'content.xml');
        $dom = new \DOMDocument();
        $dom->load($contentFile);
        $this->XPathTesting($dom, $expectedValues);
    }
    /**
     * @dataProvider dataMeta
     * @param string $docName
     * @param string $template
     * @param array $expectedValues
     */
    public function testMeta($docName, $template, array $expectedValues)
    {
        
        $metaFile = $this->extractFile($docName, $template, 'meta.xml');
        $dom = new \DOMDocument();
        $dom->load($metaFile);
        $this->XPathTesting($dom, $expectedValues);
    }
    
    public function dataMeta()
    {
        return array(
            array(
                "TST_OOOS1",
                "PU_dcp_data_simple1.odt",
                array(
                    array(
                        "office:meta//dc:title",
                        "First Test"
                    ) ,
                    array(
                        "office:meta//meta:keyword",
                        "40.00 %"
                    )
                )
            ) ,
            array(
                "TST_OOOS2",
                "PU_dcp_data_simple1.odt",
                array(
                    array(
                        "office:meta//dc:title",
                        "Second Test"
                    ) ,
                    array(
                        "office:meta//meta:keyword",
                        "3.14 %"
                    )
                )
            )
        );
    }
    
    public function dataContent()
    {
        return array(
            array(
                "TST_OOOS1",
                "PU_dcp_data_simple1.odt",
                array(
                    array(
                        "office:body/office:text/text:p/text:span",
                        "First Test"
                    ) ,
                    array(
                        "office:body/office:text/text:p/text:span",
                        "40.00 %"
                    ) ,
                    array(
                        "office:body/office:text/text:p/text:span",
                        "rouge"
                    ) ,
                    array(
                        "office:body/office:text/text:section//text:span",
                        "Bold"
                    ) ,
                    array(
                        "office:body/office:text/text:section//text:span[@text:style-name='Tbold']",
                        "Bold"
                    )
                )
            ) ,
            array(
                "TST_OOOS2",
                "PU_dcp_data_simple1.odt",
                array(
                    array(
                        "office:body/office:text/text:p/text:span",
                        "Second Test"
                    ) ,
                    array(
                        "office:body/office:text/text:p/text:span",
                        "3.14 %"
                    ) ,
                    array(
                        "office:body/office:text/text:p/text:span",
                        "jaune"
                    ) ,
                    array(
                        "office:body/office:text/text:section//text:span",
                        "Italique"
                    ) ,
                    array(
                        "office:body/office:text/text:section//text:span[@text:style-name='Titalics']",
                        "Italique"
                    ) ,
                    array(
                        "office:body//table:table/table:table-row[1]/table:table-cell[1]//text:p/text:span",
                        "Html colonne"
                    ) , // first row
                    array(
                        "office:body//table:table/table:table-row[1]/table:table-cell[2]//text:p/text:span",
                        "Texte colonne"
                    ) ,
                    array(
                        "office:body//table:table/table:table-row[2]/table:table-cell[1]//text:section/text:p/text:span[@text:style-name='Tbold']",
                        "Bold one"
                    ) ,
                    array(
                        "office:body//table:table/table:table-row[3]/table:table-cell[1]//text:section/text:p/text:span[@text:style-name='Titalics']",
                        "Italique two"
                    ) , // second row (after header row)
                    array(
                        "office:body//table:table/table:table-row[4]/table:table-cell[1]//text:section/text:p/text:span[@text:style-name='Punderline']",
                        "Underline three"
                    ) ,
                    array(
                        "office:body//table:table/table:table-row[5]/table:table-cell[1]//text:section/text:p/text:span[@text:style-name='Tbold']",
                        "bold four"
                    ) ,
                    array(
                        "office:body//table:table/table:table-row[5]/table:table-cell[1]//text:section/text:p/text:span[@text:style-name='Titalics']",
                        "italic four"
                    ) ,
                    array(
                        "office:body//table:table/table:table-row[5]/table:table-cell[1]//text:section/text:p/text:span[@text:style-name='Punderline']",
                        "underline four"
                    ) ,
                    array(
                        "office:body//table:table/table:table-row[2]/table:table-cell[2]//text:p/text:span",
                        "Column one"
                    ) ,
                    array(
                        "office:body//table:table/table:table-row[3]/table:table-cell[2]//text:p/text:span",
                        "Column two"
                    ) ,
                    array(
                        "office:body//table:table/table:table-row[4]/table:table-cell[2]//text:p/text:span",
                        "Column 'three"
                    ) ,
                    array(
                        "office:body//table:table/table:table-row[5]/table:table-cell[2]//text:p/text:span",
                        "Column <four>"
                    )
                )
            ) ,
            array(
                "TST_OOOS3",
                "PU_dcp_data_simple1.odt",
                array(
                    array(
                        "office:body/office:text/text:p/text:span",
                        '$Third Test$'
                    ) ,
                    array(
                        "office:body/office:text/text:p/text:span",
                        "42.00 %"
                    ) ,
                    array(
                        "office:body/office:text/text:p/text:span",
                        "rouge"
                    ) ,
                    array(
                        "office:body/office:text/text:section//text:span",
                        "Bold"
                    ) ,
                    array(
                        "office:body/office:text/text:section//text:span[@text:style-name='Tbold']",
                        "Bold"
                    )
                )
            ) ,
            array(
                "TST_OOOS4",
                "PU_dcp_data_simple1.odt",
                array(
                    array(
                        "office:body//table:table/table:table-row[1]/table:table-cell[1]//text:p/text:span",
                        "Html colonne"
                    ) ,
                    array(
                        "office:body//table:table/table:table-row[2]/table:table-cell[1]//text:section/text:p",
                        "C'est un test <<\"hivers\" & \"été\">>"
                    )
                )
            ) ,
            array(
                "TST_OOOS5",
                "PU_dcp_data_simple1.odt",
                array(
                    array(
                        "office:body/office:text/text:section/text:p",
                        "Texte"
                    ) ,
                    array(
                        "office:body/office:text/text:section/text:p/draw:frame[@draw:name='htmlgraphic']",
                        ""
                    ) ,
                    array(
                        "office:body/office:text/text:section/text:p/draw:frame[@svg:width='20mm']",
                        ""
                    ) ,
                    array(
                        "office:body/office:text/text:section/text:p/draw:frame[@svg:height='30mm']",
                        ""
                    ) ,
                    array(
                        "office:body/office:text/text:section/text:p/draw:frame[@draw:name='htmlgraphic']/draw:image[contains(@xlink:href,'Pictures/dcp')]",
                        ""
                    )
                )
            ) ,
            array(
                "TST_OOOS6",
                "PU_dcp_data_simple1.odt",
                array(
                    array(
                        "office:body/office:text/text:section/text:p",
                        "Débutet fin."
                    ) ,
                    array(
                        "office:body/office:text/text:section/text:p/draw:frame[@draw:name='htmlgraphic']",
                        ""
                    ) ,
                    array(
                        "office:body/office:text/text:section/text:p/draw:frame[@svg:width='48mm']",
                        ""
                    ) ,
                    array(
                        "office:body/office:text/text:section/text:p/draw:frame[@svg:height='35mm']",
                        ""
                    ) ,
                    array(
                        "office:body/office:text/text:section/text:p/draw:frame[@draw:name='htmlgraphic']/draw:image[contains(@xlink:href,'Pictures/dcp')]",
                        ""
                    )
                )
            ) ,
            array(
                "TST_OOOS7",
                "PU_dcp_data_simple1.odt",
                array(
                    
                    array(
                        "office:body/office:text/text:section//table:table[1]/table:table-row[1]//table:table-cell/text:p/draw:frame[@draw:name='htmlgraphic']",
                        ""
                    ) ,
                    array(
                        "office:body/office:text/text:section//table:table[1]/table:table-row[2]//table:table-cell/text:p/draw:frame[@draw:name='htmlgraphic']",
                        ""
                    ) ,
                    array(
                        "office:body/office:text/text:section/table:table[1]/table:table-row[1]//table:table-cell/text:p/text:span[@text:style-name='Tbold']",
                        "Gras"
                    )
                )
            ) ,
            array(
                "TST_OOOS8",
                "PU_dcp_data_simple1.odt",
                array(
                    
                    array(
                        "office:body/office:text/text:section//table:table[1]/table:table-row[1]//table:table-cell[1]/text:p/text:span[@text:style-name='Tbold']",
                        "GrasP"
                    ) ,
                    array(
                        "office:body/office:text/text:section//table:table[1]/table:table-row[1]//table:table-cell[2]/text:p/text:span[@text:style-name='Tbold']",
                        "GrasO"
                    ) ,
                    array(
                        "office:body/office:text/text:section//table:table[1]/table:table-row[1]//table:table-cell[1]/text:p/text:span[@text:style-name='Titalics']",
                        "ItaliqueP"
                    ) ,
                    array(
                        "office:body/office:text/text:section//table:table[1]/table:table-row[1]//table:table-cell[2]/text:p/text:span[@text:style-name='Titalics']",
                        "ItaliqueO"
                    ) ,
                    array(
                        "office:body/office:text/text:section//table:table[1]/table:table-row[1]//table:table-cell[3]/text:p",
                        "Single text 'voilà'"
                    ) ,
                    array(
                        "office:body/office:text/text:section//table:table[1]/table:table-row[2]//table:table-cell[1]/text:p",
                        "Balise ignorée"
                    ) ,
                    array(
                        "office:body/office:text/text:section//table:table[1]/table:table-row[2]//table:table-cell[2]/text:p/text:span[@text:style-name='Punderline']",
                        "Souligné"
                    ) ,
                    array(
                        "office:body/office:text/text:section//table:table[1]/table:table-row[2]//table:table-cell[3]/text:p",
                        "Paragraphe normal."
                    ) ,
                    array(
                        "office:body/office:text/text:section//table:table[1]/table:table-row[2]//table:table-cell[4]/text:p",
                        "Là"
                    ) ,
                    array(
                        "office:body/office:text/text:section//table:table[1]/table:table-row[3]//table:table-cell[1]/text:p/text:span[@text:style-name='Tsub']",
                        "Delta"
                    ) ,
                    array(
                        "office:body/office:text/text:section//table:table[1]/table:table-row[3]//table:table-cell[1]/text:p/text:span[@text:style-name='Tbold']",
                        "Gros"
                    ) ,
                    array(
                        "office:body/office:text/text:section//table:table[1]/table:table-row[3]//table:table-cell[1]/text:p/text:span[@text:style-name='Titalics']",
                        "mince"
                    ) ,
                    array(
                        "office:body/office:text/text:section//table:table[1]/table:table-row[3]//table:table-cell[2]/text:p/text:span[@text:style-name='Tsuper']",
                        "2"
                    ) ,
                    array(
                        "office:body/office:text/text:section//table:table[1]/table:table-row[3]//table:table-cell[3]/text:p/text:span[@text:style-name='Tsub']",
                        "d"
                    ) ,
                    array(
                        "office:body/office:text/text:section//table:table[1]/table:table-row[3]//table:table-cell[4]/text:p/text:span[@text:style-name='Tsub']",
                        "g"
                    ) ,
                    array(
                        "office:body/office:text/text:section//table:table[1]/table:table-row[3]//table:table-cell[4]/text:p/text:span[@text:style-name='Tsuper']",
                        "f"
                    ) ,
                    array(
                        "office:body/office:text/text:section//table:table[1]/table:table-row[3]//table:table-cell[2]/text:p",
                        "Y2S"
                    ) ,
                    array(
                        "office:body/office:text/text:section//table:table[1]/table:table-row[3]//table:table-cell[3]/text:p",
                        "Wd"
                    ) ,
                    array(
                        "office:body/office:text/text:section//table:table[1]/table:table-row[3]//table:table-cell[4]/text:p",
                        "Gg, Ff"
                    ) ,
                    array(
                        "office:body/office:text/text:section//table:table[1]/table:table-row[3]//table:table-cell[1]/text:p",
                        "XDelta Très Gros et mince"
                    ) ,
                    array(
                        "office:body/office:text/text:section//table:table[1]/table:table-row[1]//table:table-cell[4]/text:p/text:a",
                        "Goto"
                    ) ,
                    array(
                        "office:body/office:text/text:section//table:table[1]//table:table-column[@table:number-columns-repeated='4']",
                        ""
                    ) ,
                    array(
                        "office:body/office:text/text:section//table:table[1]/table:table-header-rows//table:table-cell[1]/text:p/text:span[@text:style-name='Tbold']",
                        "HGrasP"
                    ) ,
                    array(
                        "office:body/office:text/text:section//table:table[1]/table:table-header-rows//table:table-cell[2]/text:p/text:span[@text:style-name='Tbold']",
                        "HGrasO"
                    ) ,
                    array(
                        "office:body/office:text/text:section//table:table[1]/table:table-header-rows//table:table-cell[1]/text:p/text:span[@text:style-name='Titalics']",
                        "HItaliqueP"
                    ) ,
                    array(
                        "office:body/office:text/text:section//table:table[1]/table:table-header-rows//table:table-cell[2]/text:p/text:span[@text:style-name='Titalics']",
                        "HItaliqueO"
                    ) ,
                    array(
                        "office:body/office:text/text:section//table:table[1]/table:table-header-rows//table:table-cell[3]/text:p",
                        "Hingle text 'voilà'"
                    ) ,
                    array(
                        "office:body/office:text/text:section//table:table[1]/table:table-header-rows//table:table-cell[4]/text:p/text:a",
                        "Hoto"
                    ) ,
                )
            )
        );
    }
}
?>