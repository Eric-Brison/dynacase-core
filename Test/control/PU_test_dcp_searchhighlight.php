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

require_once 'PU_testcase_dcp_commonfamily.php';
/**
 * test some SearchDoc option like generalFilter
 */
class TestSearchHighlight extends TestCaseDcpCommonFamily
{
    /**
     * import TST_FULLSERACHFAM1 family and some documents
     * @static
     * @return string
     */
    protected static function getCommonImportFile()
    {
        return "PU_data_dcp_highsearchfamily1.ods";
    }
    
    protected $famName = 'TST_HIGHSEARCHFAM1';
    /**
     * @dataProvider dataFullHighlight
     */
    public function testFullHighlight($filter, array $expectedHighlight)
    {
        $s = new \SearchDoc(self::$dbaccess, $this->famName);
        if ($filter) $s->addGeneralFilter($filter);
        $s->setObjectReturn();
        $s->search();
        
        $err = $s->getError();
        $this->assertEmpty($err, "search error : $err");
        $dl = $s->getDocumentList();
        // print_r($s->getSearchInfo());
        $index = 0;
        /**
         * @var \Doc $doc
         */
        foreach ($dl as $doc) {
            
            $ht = $dl->getSearchDocument()->getHighLightText($doc);
            foreach ($expectedHighlight as $high) {
                $this->assertContains($high, $ht, sprintf("highlight error"));
            }
            $index++;
        }
    }
    
    public function dataFullHighlight()
    {
        return array(
            array(
                "téléphone",
                array(
                    "<b>téléphone</b>"
                )
            ) ,
            
            array(
                "téléphone maison",
                array(
                    "<b>maison</b>",
                    "<b>téléphone</b>"
                )
            ) ,
            array(
                "maison",
                array(
                    "<b>maison</b>"
                )
            ) ,
            
            array(
                "espèce cheval",
                array(
                    "<b>espèce</b>",
                    "<b>chevaux</b>"
                )
            ) ,
            array(
                "espèce chien",
                array(
                    "<b>espèces</b>",
                    "<b>chien</b>"
                )
            )
        );
    }
}
?>