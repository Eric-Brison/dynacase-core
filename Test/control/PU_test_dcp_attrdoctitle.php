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

class TestAttributeDoctitle extends TestCaseDcpCommonFamily
{
    /**
     * import TST_DEFAULTFAMILY1 family
     * @static
     * @return string
     */
    protected static function getCommonImportFile()
    {
        return "PU_data_dcp_doctitleauto.ods";
    }
    /**
     * @dataProvider dataDocTitle
     */
    public function testTitle($docid, $attrid, $expectedValue)
    {
        
        $d = new_doc(self::$dbaccess, $docid);
        $this->assertTrue($d->isAlive() , sprintf("cannot get \"%s\" document", $docid));
        
        $oa = $d->getAttribute($attrid);
        $this->assertNotEmpty($oa, sprintf("attribute %s not found in %s family", $attrid, $d->fromname));
        $value = $d->getRawValue($oa->id);
        $this->assertEquals($expectedValue, $value, sprintf("not the expected value attribute %s", $attrid));
    }
    
    public function dataDocTitle()
    {
        return array(
            array(
                "TST_DT1",
                "TST_TITLE",
                "Référence n°1"
            ) ,
            
            array(
                "TST_DT2",
                "TST_TITLE",
                "Référence n°2"
            ) ,
            
            array(
                "TST_DT1",
                "TST_A1_TITLE",
                "Rôle n°1"
            ) ,
            
            array(
                "TST_DT2",
                "TST_A1_TITLE",
                "Rôle n°2"
            ) ,
            
            array(
                "TST_DT3",
                "TST_T1",
                "Référence n°1"
            ) ,
            
            array(
                "TST_DT3",
                "TST_DOC2_TITLE",
                "Référence n°2"
            ) ,
            
            array(
                "TST_DT4",
                "TST_DOCS1_TITLE",
                "Référence n°1\nRéférence n°2\nRéférence n°3"
            ) ,
            
            array(
                "TST_DT4",
                "TST2_AS1_TITLE",
                "Rôle n°1\nRôle n°2"
            )
        );
    }
}
?>
