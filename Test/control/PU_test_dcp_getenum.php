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

class TestGetEnum extends TestCaseDcpCommonFamily
{
    public static function getCommonImportFile()
    {
        setLanguage("fr_FR");
        return "PU_data_dcp_enumfamily1.ods";
    }
    /**
     * @dataProvider dataGetEnum
     */
    public function testExecuteGetEnum($famid, $attrid, array $expectedKeys, array $expectedLabel)
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
            
            $this->assertEquals($label, $oa->getEnumLabel($key) , sprintf("enum single label mismatch: key %s\n%s", $key, print_r($enum, true)));
        }
    }
    /**
     * @dataProvider dataGetEnumLabel
     */
    public function testGetEnumLabel($famid, $attrid, array $expectedKeys, array $expectedLabel)
    {
        $a = _("TST_ENUMFAM1#tst_enuma#a");
        $a = _("TST_ENUMFAM1#tst_enum2#0");
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
    /**
     * @dataProvider dataInheritedGetEnum
     *
     * @param $familyIdList
     * @param $attrId
     * @param $expectedEnum
     */
    public function testInheritedGetEnum($familyIdList, $attrId, $expectedEnum)
    {
        /**
         * @var \DocFam $fam
         * @var \NormalAttribute $attr
         */
        foreach ($familyIdList as $familyId) {
            $fam = new_doc(self::$dbaccess, $familyId);
            $attr = $fam->getAttribute($attrId);
            $enum = $attr->getEnum();
            $this->assertTrue(is_array($enum) , sprintf("getEnum() on '%s' from '%s' is not an array (found type '%s').", $attrId, $fam->name, gettype($enum)));
            $this->assertTrue(count($expectedEnum) === count($enum) , sprintf("Count mismatch for getEnum() on '%s' from '%s': found '%d' while expecting '%d'.", $attrId, $fam->name, count($enum) , count($expectedEnum)));
            $diff = array_diff_key($expectedEnum, $enum);
            $this->assertTrue(count($diff) === 0, sprintf("getEnum() on '%s' from '%s' returned unexpected keys.", $attrId, $fam->name));
        }
    }
    /**
     * @dataProvider dataInheritedAddEnum
     *
     * @param $addEnums
     * @param $expectedEnums
     */
    public function testInheritedAddEnum($addEnums, $expectedEnums)
    {
        /**
         * @var \DocFam $fam
         * @var \NormalAttribute $attr
         */
        foreach ($addEnums as $familyId => $attrs) {
            $fam = new_doc(self::$dbaccess, $familyId);
            foreach ($attrs as $attrId => $enums) {
                $attr = $fam->getAttribute($attrId);
                foreach ($enums as $enumKey => $enumValue) {
                    $err = $attr->addEnum(self::$dbaccess, $enumKey, $enumValue);
                    $this->assertEmpty($err, sprintf("addEnum() returned unexpected error: %s", $err));
                }
            }
        }
        foreach ($expectedEnums as $familyId => $attrs) {
            $fam = new_doc(self::$dbaccess, $familyId);
            foreach ($attrs as $attrId => $expectedEnum) {
                $attr = $fam->getAttribute($attrId);
                $enum = $attr->getEnum();
                $this->assertTrue(is_array($enum) , sprintf("getEnum() on '%s' from '%s' is not an array (found type '%s').", $attrId, $fam->name, gettype($enum)));
                $this->assertTrue(count($expectedEnum) === count($enum) , sprintf("Count mismatch for getEnum() on '%s' from '%s': found '%d' while expecting '%d'.", $attrId, $fam->name, count($enum) , count($expectedEnum)));
                $diff = array_diff_key($expectedEnum, $enum);
                $this->assertTrue(count($diff) === 0, sprintf("getEnum() on '%s' from '%s' returned unexpected keys.", $attrId, $fam->name));
            }
        }
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
                    "Lettre A", // translated A in i18n catalog
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
                    "Zéfiro",
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
                    "Zéfiro",
                    "ZéroUn",
                    "ZéroDeux",
                    "Un",
                    "UnUn",
                    "UnDeux"
                )
            ),
            array(

                'family' => 'TST_ENUMFAM1',
                'attrid' => 'TST_ENUMNA',
                'keys' => array(
                    "a",
                    "a.ab",
                    "a.ac",
                    "b",
                    "b.ba",
                    "b.bc"
                ) ,
                'labels' => array(
                    "A",
                    "AB",
                    "AC",
                    "B",
                    "BAB",
                    "BBC"
                )
            ),
            array(

                'family' => 'TST_ENUMFAM1',
                'attrid' => 'TST_ENUMNUMERIC',
                'keys' => array(
                    "0\\.1",
                    "1\\.2",
                    "3\\.14"
                ) ,
                'labels' => array(
                    "Zéro virgule un",
                    "Un point 2",
                    "Pi"
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
                    "Lettre A", // translated A in i18n catalog
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
                    "Zéfiro",
                    "Zéfiro/ZéroUn",
                    "Zéfiro/ZéroDeux",
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
                    "Zéfiro",
                    "Zéfiro/ZéroUn",
                    "Zéfiro/ZéroDeux",
                    "Un",
                    "Un/UnUn",
                    "Un/UnDeux"
                )
            ),
            array(
                'family' => 'TST_ENUMFAM1',
                'attrid' => 'TST_ENUMNUMERIC',
                'keys' => array(
                    "0.1",
                    "1.2",
                    "3.14"
                ) ,
                'labels' => array(
                    "Zéro virgule un",
                    "Un point 2",
                    "Pi"
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
                    "0" => "Zéfiro",
                    "01" => "Zéfiro/ZéroUn",
                    "02" => "Zéfiro/ZéroDeux",
                    "1" => "Un",
                    "11" => "Un/UnUn",
                    "12" => "Un/UnDeux"
                )
            ),

            array(
                'family' => 'TST_ENUMFAM1',
                'attrid' => 'TST_ENUMNUMERIC',
                'keysLabel' => array(
                    "0.1" => "Zéro virgule un",
                    "1.2" => "Un point 2",
                    "3.14" => "Pi",
                )
            )
        );
    }
    public function dataInheritedGetEnum()
    {
        return array(
            array(
                'families' => array(
                    'TST_INHERIT_GETENUM_A1',
                    'TST_INHERIT_GETENUM_B1',
                    'TST_INHERIT_GETENUM_C1'
                ) ,
                'attrid' => 'TST_ENUM1',
                'expectedEnum' => array(
                    'ZERO' => 'Zéro',
                    'ONE' => 'Un',
                    'TWO' => 'Deux'
                )
            ) ,
            array(
                'families' => array(
                    'TST_INHERIT_GETENUM_A2',
                    'TST_INHERIT_GETENUM_B2',
                    'TST_INHERIT_GETENUM_C2',
                    'TST_INHERIT_GETENUM_D2'
                ) ,
                'attrid' => 'TST_ENUM1',
                'expectedEnum' => array(
                    'ZERO' => 'Zéro',
                    'ONE' => 'Un',
                    'TWO' => 'Deux'
                )
            ) ,
            array(
                'families' => array(
                    'TST_INHERIT_GETENUM_A3',
                    'TST_INHERIT_GETENUM_B3',
                    'TST_INHERIT_GETENUM_C3',
                    'TST_INHERIT_GETENUM_D3'
                ) ,
                'attrid' => 'TST_ENUM1',
                'expectedEnum' => array(
                    'ZERO' => 'Zéro',
                    'ONE' => 'Un',
                    'TWO' => 'Deux'
                )
            )
        );
    }
    public function dataInheritedAddEnum()
    {
        return array(
            array(
                'addEnums' => array(
                    'TST_INHERIT_ADDENUM_B1' => array(
                        'TST_ENUM1' => array(
                            'THREE' => 'Trois'
                        )
                    ) ,
                    'TST_INHERIT_ADDENUM_C1' => array(
                        'TST_ENUM1' => array(
                            'FOUR' => 'Quatre'
                        )
                    ) ,
                    'TST_INHERIT_ADDENUM_D1' => array(
                        'TST_ENUM1' => array(
                            'FIVE' => 'Cinq'
                        )
                    ) ,
                ) ,
                'expectedEnums' => array(
                    'TST_INHERIT_ADDENUM_B1' => array(
                        'TST_ENUM1' => array(
                            'ZERO' => 'Zéro',
                            'ONE' => 'Un',
                            'TWO' => 'Two',
                            'THREE' => 'Trois',
                            'FOUR' => 'Quatre',
                            'FIVE' => 'Cinq'
                        )
                    ) ,
                    'TST_INHERIT_ADDENUM_C1' => array(
                        'TST_ENUM1' => array(
                            'ZERO' => 'Zéro',
                            'ONE' => 'Un',
                            'TWO' => 'Two',
                            'THREE' => 'Trois',
                            'FOUR' => 'Quatre',
                            'FIVE' => 'Cinq'
                        )
                    ) ,
                    'TST_INHERIT_ADDENUM_D1' => array(
                        'TST_ENUM1' => array(
                            'ZERO' => 'Zéro',
                            'ONE' => 'Un',
                            'TWO' => 'Two',
                            'THREE' => 'Trois',
                            'FOUR' => 'Quatre',
                            'FIVE' => 'Cinq'
                        )
                    ) ,
                )
            ) ,
            array(
                'addEnums' => array(
                    'TST_INHERIT_ADDENUM_B2' => array(
                        'TST_ENUM1' => array(
                            'THREE' => 'Trois'
                        )
                    ) ,
                    'TST_INHERIT_ADDENUM_C2' => array(
                        'TST_ENUM1' => array(
                            'FOUR' => 'Quatre'
                        )
                    ) ,
                    'TST_INHERIT_ADDENUM_D2' => array(
                        'TST_ENUM1' => array(
                            'FIVE' => 'Cinq'
                        )
                    ) ,
                ) ,
                'expectedEnums' => array(
                    'TST_INHERIT_ADDENUM_B2' => array(
                        'TST_ENUM1' => array(
                            'ZERO' => 'Zéro',
                            'ONE' => 'Un',
                            'TWO' => 'Two',
                            'THREE' => 'Trois',
                            'FOUR' => 'Quatre',
                            'FIVE' => 'Cinq'
                        )
                    ) ,
                    'TST_INHERIT_ADDENUM_C2' => array(
                        'TST_ENUM1' => array(
                            'ZERO' => 'Zéro',
                            'ONE' => 'Un',
                            'TWO' => 'Two',
                            'THREE' => 'Trois',
                            'FOUR' => 'Quatre',
                            'FIVE' => 'Cinq'
                        )
                    ) ,
                    'TST_INHERIT_ADDENUM_D2' => array(
                        'TST_ENUM1' => array(
                            'ZERO' => 'Zéro',
                            'ONE' => 'Un',
                            'TWO' => 'Two',
                            'THREE' => 'Trois',
                            'FOUR' => 'Quatre',
                            'FIVE' => 'Cinq'
                        )
                    ) ,
                )
            )
        );
    }
}
?>
