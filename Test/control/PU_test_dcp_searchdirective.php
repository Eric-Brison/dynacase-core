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
class TestSearchDirective extends TestCaseDcpCommonFamily
{
    /**
     * import TST_FULLSERACHFAM1 family and some documents
     * @static
     * @return string
     */
    protected static function getCommonImportFile()
    {
        return "PU_data_dcp_fullsearchfamily1.ods";
    }
    
    protected $famName = 'TST_FULLSEARCHFAM1';
    /**
     * @dataProvider dataGeneralFilter
     */
    public function testGeneralFilter($filter, array $expectedDocName)
    {
        $s = new \SearchDoc(self::$dbaccess, $this->famName);
        if ($filter) $s->addGeneralFilter($filter);
        $s->setObjectReturn();
        $s->search();
        
        $err = $s->getError();
        $this->assertEmpty($err, "search error : $err");
        $dl = $s->getDocumentList();
        //print_r($s->getSearchInfo());
        $this->assertEquals(count($expectedDocName) , $s->count() , "not correct count " . $this->getFilterResult($dl));
        $index = 0;
        /**
         * @var \Doc $doc
         */
        foreach ($dl as $doc) {
            $this->assertEquals($expectedDocName[$index], $doc->name);
            $index++;
        }
    }
    /**
     * @dataProvider dataSpellGeneralFilter
     */
    public function testSpellGeneralFilter($filter, array $expectedDocName)
    {
        $s = new \SearchDoc(self::$dbaccess, $this->famName);
        if ($filter) $s->addGeneralFilter($filter, true);
        $s->setObjectReturn();
        $s->search();
        
        $err = $s->getError();
        $this->assertEmpty($err, "search error : $err");
        $dl = $s->getDocumentList();
        //print_r($s->getSearchInfo());
        $this->assertEquals(count($expectedDocName) , $s->count() , "not correct count " . $this->getFilterResult($dl));
        $index = 0;
        /**
         * @var \Doc $doc
         */
        foreach ($dl as $doc) {
            $this->assertEquals($expectedDocName[$index], $doc->name);
            $index++;
        }
    }
    
    private function getFilterResult(\DocumentList $dl)
    {
        $names = array();
        /**
         * @var \Doc $doc
         */
        foreach ($dl as $doc) {
            $names[] = $doc->name;
        }
        return implode(",", $names);
    }
    
    public function dataSpellGeneralFilter()
    {
        return array(
            array(
                "téléfone",
                array(
                    "TST_FULL2",
                    "TST_FULL1"
                )
            ) ,
            
            array(
                "téléfone maizon",
                array(
                    "TST_FULL2"
                )
            ) ,
            array(
                "téléfone méson",
                array(
                    "TST_FULL2"
                )
            )
        );
    }
    public function dataGeneralFilter()
    {
        return array(
            array(
                "",
                array(
                    "TST_FULL3",
                    "TST_FULL4",
                    "TST_FULL5",
                    "TST_FULL6",
                    "TST_FULL8",
                    "TST_FULL7",
                    "TST_FULL2",
                    "TST_FULL1"
                )
            ) ,
            array(
                "téléphone",
                array(
                    "TST_FULL2",
                    "TST_FULL1"
                )
            ) ,
            array(
                "téléphones portables",
                array(
                    "TST_FULL1"
                )
            ) ,
            array(
                "téléphones AND portables",
                array(
                    "TST_FULL1"
                )
            ) ,
            array(
                "téléphones fixes",
                array(
                    "TST_FULL2"
                )
            ) ,
            array(
                "cheval",
                array(
                    "TST_FULL3"
                )
            ) ,
            array(
                "rouge",
                array(
                    "TST_FULL3",
                    "TST_FULL4",
                    "TST_FULL5",
                    "TST_FULL6",
                    "TST_FULL8",
                    "TST_FULL7"
                )
            ) ,
            array(
                "portable OR fixe",
                array(
                    "TST_FULL2",
                    "TST_FULL1"
                )
            ) ,
            array(
                "portàble OR fixe OR cheval",
                array(
                    "TST_FULL3",
                    "TST_FULL2",
                    "TST_FULL1"
                )
            ) ,
            array(
                "téléphone OR (singe    AND rouge)",
                array(
                    "TST_FULL2",
                    "TST_FULL1",
                    "TST_FULL6"
                )
            ) ,
        );
    }
}
?>