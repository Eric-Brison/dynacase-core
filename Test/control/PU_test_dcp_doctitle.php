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

class TestGetDocTitle extends TestCaseDcpCommonFamily
{
    public $famName = "TST_TITLE";
    /**
     * import TST_TITLE family
     * @static
     * @return string
     */
    protected static function getCommonImportFile()
    {
        return "PU_data_dcp_getdoctitle.ods";
    }
    /**
     * @dataProvider dataGetDocTitleMultiple
     */
    public function testGetTitleMultiple($docName, $expectValue, $type)
    {
        $d = createDoc(self::$dbaccess, $this->famName);
        $this->assertTrue(is_object($d) , sprintf("cannot create %s document", $this->famName));
        
        if ($type == "docid") {
            $expectedValues = array();
            $expetedArray = explode(" ", $expectValue);
            foreach ($expetedArray as $expected) {
                $expectedValues[] = getIdFromName(self::$dbaccess, $expected);
            }
            $expectValue = implode(" ", $expectedValues);
        }
        
        $value = $d->getTitle(getIdFromName(self::$dbaccess, $docName));
        $this->assertEquals($expectValue, $value, "getTitle wrong value");
    }
    
    public function dataGetDocTitleMultiple()
    {
        
        return array(
            array(
                "TST_TITLE_3",
                "TST_TITLE_2 TST_TITLE_1 TST_TITLE_2 TST_TITLE_1",
                "docid"
            ) ,
            array(
                'TST_TITLE_2',
                "TST_TITLE_1",
                "docid"
            ) ,
            array(
                'TST_TITLE_1',
                "Y N Y N",
                "enum"
            )
        );
    }
}
