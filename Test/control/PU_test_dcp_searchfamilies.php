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
/**
 * test some SearchDoc option like generalFilter
 */
class TestSearchFamilies extends TestCaseDcpCommonFamily
{
    /**
     * import TST_FULLSERACHFAM1 family and some documents
     * @static
     * @return string
     */
    protected static function getCommonImportFile()
    {
        return "PU_data_dcp_searchfamilies.ods";
    }
    /**
     * @dataProvider dataProperties
     */
    public function testProperties($filterName, array $expectedProperties)
    {
        $s = new \SearchDoc(self::$dbaccess);
        if ($filterName) {
            $s->addFilter("name = '%s'", $filterName);
        }
        $s->setObjectReturn(true);
        $s->search();
        
        $err = $s->getError();
        $this->assertEmpty($err, "search error : $err");
        $dl = $s->getDocumentList();
        // print_r($s->getSearchInfo());
        $this->assertEquals(1, $s->count() , "Family $filterName not found - " . print_r($s->getSearchInfo() , true));
        
        $index = 0;
        
        $doc = $s->getNextDoc();
        /**
         * @var \Doc $doc
         */
        foreach ($expectedProperties as $propId => $expectedValue) {
            $propValue = $doc->getPropertyValue($propId);
            if ($propValue === false) {
                $propValue = $doc->getRawValue($propId);
            }
            if (abs($propValue) > 0) {
                if (!is_numeric($expectedValue)) {
                $propValueName = getNameFromId(self::$dbaccess, abs($propValue));
                if ($propValueName != '') {
                    $propValue = $propValueName;
                }
            }
            }
            $this->assertEquals($expectedValue, $propValue, sprintf("Not the good property for \"$propId\""));
        }
    }
    
    public function dataProperties()
    {
        return array(
            array(
                'famName' => "TST_SFAM1",
                "props" => array(
                    "name" => "TST_SFAM1",
                    "profid" => "TSTFAM_ADMIN_1",
                    "cprofid" => "TSTFAM_ADMIN_EDIT1",
                    "ccvid" => "TSTFAM_CV_1",
                    "schar" => "S"
                )
            ) ,
            array(
                'famName' => "TST_SFAM2",
                "props" => array(
                    "name" => "TST_SFAM2",
                    "profid" => "TSTFAM_ADMIN_2",
                    "cprofid" => "TSTFAM_ADMIN_EDIT2",
                    "ccvid" => "TSTFAM_CV_2",
                    "schar" => "",
                    "maxrev" => 34
                )
            )
        );
    }
}
?>