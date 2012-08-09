<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace PU;
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package DCP
 */

require_once 'PU_testcase_dcp.php';

class TestExportXml extends TestCaseDcpCommonFamily
{
    /**
     * @var \DOMDocument
     */
    private $dom;
    static function getCommonImportFile()
    {
        return array(
            "PU_data_dcp_exportfamily.ods",
            "PU_data_dcp_exportrelation.ods",
            "PU_data_dcp_exporttitlelimits.ods"
        );
    }
    /**
     * @return \DOMDocument
     */
    private function getExportedSelectionDom()
    {
        if (!$this->dom) {
            $selNames = array(
                "TST_REL1",
                "TST_REL2",
                "TST_REL3",
                "TST_REL4"
            );
            $config = array();
            foreach ($selNames as $name) {
                $id = getIdFromName(self::$dbaccess, $name);
                if ($id) $config["selectionItems"][] = $id;
            }
            
            $s = new \Fdl_DocumentSelection($config);
            //print_r( $s->search());
            $export = new \exportXmlFolder();
            try {
                $export->setOutputFormat(\exportXmlFolder::xmlFormat);
                $export->useIdentificator(false);
                $export->exportFromSelection($s);
                $this->dom = new \DOMDocument();
                $this->dom->load($export->getOutputFile());
                @unlink($export->getOutputFile());
                return $this->dom;
            }
            catch(\Exception $e) {
                print $e->getMessage();
            }
            return null;
        } else {
            return $this->dom;
        }
    }
    /**
     * @return \DOMDocument
     */
    private function getExportedSearchDom()
    {
        if (!$this->dom) {
            $s = new \SearchDoc(self::$dbaccess, "TST_EXPORTFAM1");
            //print_r( $s->search());
            $export = new \exportXmlFolder();
            
            $export->setOutputFormat(\exportXmlFolder::xmlFormat);
            $export->useIdentificator(false);
            $export->exportFromSearch($s);
            $this->dom = new \DOMDocument();
            $this->dom->load($export->getOutputFile());
            @unlink($export->getOutputFile());
            return $this->dom;
        } else {
            return $this->dom;
        }
    }
    /**
     * @dataProvider dataDocumentFiles
     */
    public function testExportRelation($docName, $attrName, $expectedValue)
    {
        
        $dom = $this->getExportedSearchDom();
        $this->domTestExportValue($dom, $docName, $attrName, 'name', $expectedValue);
    }
    /**
     * @dataProvider dataDocumentFiles
     */
    public function testExportSelectionRelation($docName, $attrName, $expectedValue)
    {
        
        $dom = $this->getExportedSelectionDom();
        $this->domTestExportValue($dom, $docName, $attrName, 'name', $expectedValue);
    }
    /**
     * @dataProvider dataValues
     */
    public function testExportSimpleValue($docName, $attrName, $expectedValue)
    {
        
        $dom = $this->getExportedSearchDom();
        $this->domTestExportValue($dom, $docName, $attrName, 'content', $expectedValue);
    }
    private function domTestExportValue(\DOMDocument $dom, $docName, $attrName, $domAttr, $expectedValue)
    {
        
        $docs = $dom->getElementsByTagName("tst_exportfam1");
        /**
         * \DOMElement $xmldoc
         */
        $xmldoc = null;
        foreach ($docs as $doc) {
            /**
             * @var \DOMElement $doc
             */
            
            if ($doc->getAttribute('name') == $docName) {
                $xmldoc = $doc;
                break;
            }
        }
        
        $this->assertNotEmpty($xmldoc, sprintf("document %s not found in xml", $docName));
        /**
         * @var \DOMNodeList $attrs
         */
        $attrs = $xmldoc->getElementsByTagName($attrName);
        if (!is_array($expectedValue)) $expectedValue = array(
            $expectedValue
        );
        $this->assertEquals(count($expectedValue) , $attrs->length, sprintf("attribute %s not found in %s document", $attrName, $docName));
        
        $ka = 0;
        foreach ($attrs as $attr) {
            /**
             * @var \DOMElement $attr
             */
            if ($domAttr == 'content') {
                $value = $attr->nodeValue;
            } else {
                $value = $attr->getAttribute($domAttr);
            }
            $this->assertTrue($expectedValue[$ka] === $value, sprintf("incorrect value for attribute %s in %s document", $attrName, $docName));
            $ka++;
        }
    }
    /**
     * @dataProvider dataExportTitleLimits
     */
    function testExportTitleLimits($folderId)
    {
        $export = new \exportXmlFolder();
        $catchedMessage = '';
        try {
            $export->setOutputFormat(\exportXmlFolder::xmlFormat);
            $export->useIdentificator(false);
            $export->exportFromFolder($folderId);
            $this->dom = new \DOMDocument();
            $this->dom->load($export->getOutputFile());
            @unlink($export->getOutputFile());
        }
        catch(\Exception $e) {
            $catchedMessage = $e->getMessage();
        }
        $this->assertNotNull($this->dom->documentElement, sprintf("Invalid XML export for folder '%s': %s", $folderId, ($catchedMessage != '') ? $catchedMessage : '<no-error-message>'));
    }
    
    public function dataDocumentFiles()
    {
        return array(
            array(
                "TST_REL1",
                "tst_relone",
                "TST_OUTREL1"
            ) ,
            array(
                "TST_REL2",
                "tst_relmul",
                "TST_OUTREL1,TST_OUTREL2,TST_OUTREL3"
            ) ,
            array(
                "TST_REL3",
                "tst_colrelone",
                array(
                    "TST_OUTREL1",
                    "TST_OUTREL2",
                    "TST_OUTREL3"
                )
            ) ,
            array(
                "TST_REL4",
                "tst_colrelmul",
                array(
                    "TST_OUTREL1",
                    "TST_OUTREL1,TST_OUTREL3",
                    "TST_OUTREL1,TST_OUTREL2,TST_OUTREL3"
                )
            )
        );
    }
    
    public function dataValues()
    {
        return array(
            array(
                "TST_NUM1",
                "tst_number",
                "1"
            ) ,
            array(
                "TST_NUM0",
                "tst_number",
                "0"
            ) ,
            array(
                "TST_DATE1",
                "tst_date",
                "2012-02-20"
            ) ,
            array(
                "TST_DATE1",
                "tst_number",
                ""
            )
        );
    }
    
    public function dataExportTitleLimits()
    {
        return array(
            array(
                "TST_EXPORTTITLELIMITS_DIR"
            )
        );
    }
}
?>