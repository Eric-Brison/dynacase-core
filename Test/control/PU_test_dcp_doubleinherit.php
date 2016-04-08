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

class TestDoubleInherit extends TestCaseDcpCommonFamily
{
    /**
     * import some documents
     * @static
     * @return string
     */
    protected static function getCommonImportFile()
    {
        return array(
            "PU_data_dcp_doubleinherit.ods"
        );
    }
    /**
     * @dataProvider dataRefresh
     * @param string $docName
     * @param string $expectValue
     * @return \Doc
     */
    public function testRefresh($docName, $expectValue)
    {
        $d = new_doc(self::$dbaccess, $docName);
        $this->assertTrue($d->isAlive() , sprintf("document %s not alive", $docName));
        
        $this->assertEquals($expectValue, $d->refresh());
    }
    
    public function dataRefresh()
    {
        return array(
            array(
                "TST_DOUBLEINHA",
                "A"
            ) ,
            array(
                "TST_DOUBLEINHB",
                "B"
            )
        );
    }
    /**
     * @dataProvider dataGetAReference
     * @param string $docName
     * @param string $expectValue
     * @return \Doc
     */
    public function testGetAReference($docName, $expectValue)
    {
        /**
         * @var \_TSTCOMMONINHERIT $d
         */
        $d = new_doc(self::$dbaccess, $docName);
        $this->assertTrue($d->isAlive() , sprintf("document %s not alive", $docName));
        $this->assertEquals($expectValue, $d->getAReference());
    }
    
    public function dataGetAReference()
    {
        return array(
            array(
                "TST_DOUBLEINHA",
                "X/879"
            ) ,
            array(
                "TST_DOUBLEINHB",
                "Y/456"
            )
        );
    }
}
?>