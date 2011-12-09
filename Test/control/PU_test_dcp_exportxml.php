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
    private static $dom;
    static function getCommonImportFile()
    {
        return array(
            "PU_data_dcp_exportfamily.ods",
            "PU_data_dcp_exportrelation.ods"
        );
    }
    /**
     * @return \DOMDocument
     */
    private function getExportedDom()
    {
        if (!$this->dom) {
            $s = new \SearchDoc(self::$dbaccess, "TST_EXPORTFAM1");
            //print_r( $s->search());
            $export = new \exportXmlFolder();
            try {
                $export->setOutputFormat(\exportXmlFolder::xmlFormat);
                $export->useIdentificator(false);
                $export->exportFromSearch($s);
                $this->dom = new \DOMDocument();
                $this->dom->load($export->getOutputFile());
                
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
     * @dataProvider dataDocumentFiles
     */
    public function testExportRelation($docName, $attrName, $expectedValue)
    {
        $dom = $this->getExportedDom();
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
            
            $value = $attr->getAttribute('name');
            $this->assertEquals($expectedValue[$ka], $value, sprintf("incorrect value for attribute %s in %s document", $attrName, $docName));
            $ka++;
        }
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
}
?>