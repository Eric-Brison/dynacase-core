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

class TestGetEnum extends TestCaseDcpCommonFamily
{
    public static function getCommonImportFile()
    {
        return "PU_data_dcp_enumfamily1.ods";
    }
    /**
     * @dataProvider dataGetEnum
     */
    public function testGetEnum($famid, $attrid, array $expectedKeys, array $expectedLabel)
    {
        $fam = new_doc(self::$dbaccess, $famid);
        $this->assertTrue($fam->isAlive() , sprintf("family %s not found", $famid));
        /**
         * @var \NormalAttribute $oa
         */
        $oa = $fam->getAttribute($attrid);
        $this->assertNotEmpty($oa, sprintf("attribute %s not found", $attrid));
        
        $enum = $oa->getEnum();
        $keys = array_keys($enum);
        $labels = array_values($enum);
        
        $this->assertEquals($expectedKeys, $keys, "enum keys mismatch");
        $this->assertEquals($expectedLabel, $labels, "enum label mismatch");
    }
    /**
     * @dataProvider dataGetLabelOfEnum
     */
    public function testGetLabelOfEnum($famid, $attrid, array $expectedKeysLabel)
    {
        $fam = new_doc(self::$dbaccess, $famid);
        $this->assertTrue($fam->isAlive() , sprintf("family %s not found", $famid));
        /**
         * @var \NormalAttribute $oa
         */
        $oa = $fam->getAttribute($attrid);
        $this->assertNotEmpty($oa, sprintf("attribute %s not found", $attrid));
        
        $enum = $oa->getEnum();
        foreach ($expectedKeysLabel as $key => $label) {
            
            $this->assertEquals($label, $oa->getEnumLabel($key) , sprintf("enum single label mismatch: key %s", $key));
        }
    }
    /**
     * @dataProvider dataGetEnumLabel
     */
    public function testGetEnumLabel($famid, $attrid, array $expectedKeys, array $expectedLabel)
    {
        $fam = new_doc(self::$dbaccess, $famid);
        $this->assertTrue($fam->isAlive() , sprintf("family %s not found", $famid));
        /**
         * @var \NormalAttribute $oa
         */
        $oa = $fam->getAttribute($attrid);
        $this->assertNotEmpty($oa, sprintf("attribute %s not found", $attrid));
        
        $enum = $oa->getEnumLabel();
        $keys = array_keys($enum);
        $labels = array_values($enum);
        
        $this->assertEquals($expectedKeys, $keys, "enum keys mismatch");
        $this->assertEquals($expectedLabel, $labels, "enum label mismatch");
    }
    public function dataGetEnum()
    {
        return array(
            array(
                
                'family' => 'TST_ENUMFAM1',
                'attrid' => 'TST_ENUM1',
                'keys' => array(
                    "0",
                    "1",
                    "2"
                ) ,
                'labels' => array(
                    "Zéro",
                    "Un",
                    "Deux"
                )
            ) ,
            array(
                'family' => 'TST_ENUMFAM1',
                'attrid' => 'TST_ENUMA',
                'keys' => array(
                    "a",
                    "b",
                    "c"
                ) ,
                'labels' => array(
                    "A",
                    "B",
                    "C"
                )
            ) ,
            array(
                'family' => 'TST_ENUMFAM1',
                'attrid' => 'TST_ENUM2',
                'keys' => array(
                    "0",
                    "0.01",
                    "0.02",
                    "1",
                    "1.11",
                    "1.12"
                ) ,
                'labels' => array(
                    "Zéro",
                    "ZéroUn",
                    "ZéroDeux",
                    "Un",
                    "UnUn",
                    "UnDeux"
                )
            ) ,
            array(
                'family' => 'TST_ENUMFAM1',
                'attrid' => 'TST_ENUMAUTO2',
                'keys' => array(
                    "0",
                    "0.01",
                    "0.02",
                    "1",
                    "1.11",
                    "1.12"
                ) ,
                'labels' => array(
                    "Zéro",
                    "ZéroUn",
                    "ZéroDeux",
                    "Un",
                    "UnUn",
                    "UnDeux"
                )
            )
        );
    }
    public function dataGetEnumLabel()
    {
        return array(
            array(
                
                'family' => 'TST_ENUMFAM1',
                'attrid' => 'TST_ENUM1',
                'keys' => array(
                    "0",
                    "1",
                    "2"
                ) ,
                'labels' => array(
                    "Zéro",
                    "Un",
                    "Deux"
                )
            ) ,
            array(
                'family' => 'TST_ENUMFAM1',
                'attrid' => 'TST_ENUMA',
                'keys' => array(
                    "a",
                    "b",
                    "c"
                ) ,
                'labels' => array(
                    "A",
                    "B",
                    "C"
                )
            ) ,
            array(
                'family' => 'TST_ENUMFAM1',
                'attrid' => 'TST_ENUM2',
                'keys' => array(
                    "0",
                    "01",
                    "02",
                    "1",
                    "11",
                    "12"
                ) ,
                'labels' => array(
                    "Zéro",
                    "Zéro/ZéroUn",
                    "Zéro/ZéroDeux",
                    "Un",
                    "Un/UnUn",
                    "Un/UnDeux"
                )
            ) ,
            array(
                'family' => 'TST_ENUMFAM1',
                'attrid' => 'TST_ENUMAUTO2',
                'keys' => array(
                    "0",
                    "01",
                    "02",
                    "1",
                    "11",
                    "12"
                ) ,
                'labels' => array(
                    "Zéro",
                    "Zéro/ZéroUn",
                    "Zéro/ZéroDeux",
                    "Un",
                    "Un/UnUn",
                    "Un/UnDeux"
                )
            )
        );
    }
    public function dataGetLabelOfEnum()
    {
        return array(
            array(
                
                'family' => 'TST_ENUMFAM1',
                'attrid' => 'TST_ENUM1',
                'keysLabel' => array(
                    "0" => "Zéro",
                    "1" => "Un",
                    "2" => "Deux"
                )
            ) ,
            
            array(
                'family' => 'TST_ENUMFAM1',
                'attrid' => 'TST_ENUM2',
                'keysLabel' => array(
                    "0" => "Zéro",
                    "01" => "Zéro/ZéroUn",
                    "02" => "Zéro/ZéroDeux",
                    "1" => "Un",
                    "11" => "Un/UnUn",
                    "12" => "Un/UnDeux"
                )
            )
        );
    }
}
?>