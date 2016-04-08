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

class TestDocEnum extends TestCaseDcpCommonFamily
{
    const familyName = "TST_DOCENUM";
    /**
     * import TST_DOCENUM family
     * @static
     * @return string
     */
    protected static function getCommonImportFile()
    {
        return array(
            "PU_data_dcp_docenum_family.ods"
        );
    }
    
    public static function tearDownAfterClass()
    {
        $langs = array(
            "fr_FR",
            "en_US"
        );
        foreach ($langs as $lang) {
            $moFile = \DocEnum::getMoFilename(self::familyName, $lang);
            unlink($moFile);
        }
        \LibSystem::reloadLocaleCache();
        parent::tearDownAfterClass();
    }
    /**
     * @dataProvider dataDocEnumAdd
     */
    public function testDocEnumAdd($attrid, $key, $label, $absOrder, $expectedOrder)
    {
        $s = new \EnumStructure();
        $s->key = $key;
        $s->label = $label;
        $s->absoluteOrder = $absOrder;
        
        \DocEnum::addEnum(self::familyName, $attrid, $s);
        
        $this->verifyEnumProperties($attrid, $key, $label, $expectedOrder);
    }
    /**
     * @dataProvider dataDocEnumMod
     */
    public function testDocEnumMod($attrid, $key, $label, $absOrder, $expectedOrder)
    {
        $s = new \EnumStructure();
        $s->key = $key;
        $s->label = $label;
        $s->absoluteOrder = $absOrder;
        
        \DocEnum::modifyEnum(self::familyName, $attrid, $s);
        
        $this->verifyEnumProperties($attrid, $key, $label, $expectedOrder);
    }
    /**
     * @dataProvider dataDocAddRelativeOrder
     */
    public function testDocEnumAddRelativeOrder($attrid, $key, $label, $relativeOrder, $expectedOrder)
    {
        $s = new \EnumStructure();
        $s->key = $key;
        $s->label = $label;
        $s->orderBeforeThan = $relativeOrder;
        
        \DocEnum::addEnum(self::familyName, $attrid, $s);
        
        $this->verifyEnumProperties($attrid, $key, $label, $expectedOrder);
    }
    /**
     * @dataProvider dataDocEnumDisabled
     */
    public function testDocEnumDisabled($attrid, array $disabledKeys, $expectedEnums)
    {
        $fam = new_doc(self::$dbaccess, self::familyName);
        
        foreach ($disabledKeys as $key) {
            $oe = new \DocEnum("", array(
                $fam->id,
                $attrid,
                $key
            ));
            
            $this->assertTrue($oe->isAffected() , "enum  $attrid : $key not found");
            $s = new \EnumStructure();
            $s->key = $oe->key;
            $s->label = $oe->label;
            $s->absoluteOrder = $oe->eorder;
            $s->disabled = true;
            
            \DocEnum::modifyEnum(self::familyName, $attrid, $s);
        }
        /**
         * @var \NormalAttribute $oa
         */
        $oa = $fam->getAttribute($attrid);
        $oa->resetEnum();
        $enums = $oa->getEnumLabel(null, false);
        $enumKey = array_keys($enums);
        $diff = array_diff($enumKey, $expectedEnums);
        
        $this->assertEmpty($diff, sprintf("getEnumLabel:: not expected visible \nExpect:\n %s,\nHas\n %s", print_r($expectedEnums, true) , print_r($enumKey, true)));
        
        $this->assertEquals(count($expectedEnums) , count($enumKey) , sprintf("getEnumLabel:: not expected enum count %s, %s", print_r($expectedEnums, true) , print_r($enums, true)));
        
        $oa->resetEnum();
        $enums = $oa->getEnum(false);
        $enums2 = $oa->getEnum(false);
        $this->assertEquals($enums, $enums2, sprintf("getEnum::not same with cache %s, %s", print_r($enums, true) , print_r($enums2, true)));
        
        $enumKey = array_keys($enums);
        $diff = array_diff($enumKey, $expectedEnums);
        
        $this->assertEmpty($diff, sprintf("getEnum:: not expected visible \nExpect:\n %s,\nHas\n %s", print_r($expectedEnums, true) , print_r($enumKey, true)));
        
        $this->assertEquals(count($expectedEnums) , count($enumKey) , sprintf("getEnum::not expected enum count %s, %s", print_r($expectedEnums, true) , print_r($enums, true)));
    }
    /**
     * @dataProvider dataDocModRelativeOrder
     */
    public function testDocEnumModRelativeOrder($attrid, $key, $label, $relativeOrder, $expectedOrder)
    {
        $s = new \EnumStructure();
        $s->key = $key;
        $s->label = $label;
        $s->orderBeforeThan = $relativeOrder;
        
        \DocEnum::modifyEnum(self::familyName, $attrid, $s);
        
        $this->verifyEnumProperties($attrid, $key, $label, $expectedOrder);
    }
    private function verifyEnumProperties($attrid, $key, $expectedLabel, $expectedOrder)
    {
        $f = new_Doc("", self::familyName);
        /**
         * @var \NormalAttribute $oa
         */
        $oa = $f->getAttribute($attrid);
        $oa->resetEnum();
        $elabel = $oa->getEnumLabel($key);
        $this->assertTrue($elabel !== null, "Enum not inserted");
        $this->assertEquals($expectedLabel, $elabel, "Enum not inserted");
        
        $de = new \DocEnum("", array(
            $f->id,
            $attrid,
            $key
        ));
        $this->assertTrue($de->isAffected() , "Enum record not found");
        $this->assertEquals($expectedOrder, $de->eorder, "Enum order not the good one");
    }
    /**
     * @dataProvider dataDocEnumAddLocale
     */
    public function testDocEnumAddLocale($attrid, $key, $label, array $locale)
    {
        $s = new \EnumStructure();
        $s->key = $key;
        $s->label = $label;
        
        foreach ($locale as $lang => $localeLabel) {
            $s->localeLabel[] = new \EnumLocale($lang, $localeLabel);
        }
        
        \DocEnum::addEnum(self::familyName, $attrid, $s);
        \LibSystem::reloadLocaleCache();
        
        $f = new_Doc("", self::familyName);
        /**
         * @var \NormalAttribute $oa
         */
        $oa = $f->getAttribute($attrid);
        $oa->resetEnum();
        $label = $oa->getEnumLabel($key);
        $this->assertTrue($label !== null, "Enum not inserted");
        
        foreach ($locale as $lang => $localeLabel) {
            setLanguage($lang);
            $oa->resetEnum();
            $eLabel = $oa->getEnumLabel($key);
            $this->assertEquals($localeLabel, $eLabel, sprintf("not good %s mabel", $lang));
        }
    }
    
    public function dataDocEnumAdd()
    {
        return array(
            array(
                "attrid" => "tst_enum1",
                "key" => 4,
                "label" => "quatro",
                "order" => 4,
                "expectedOrder" => 4
            ) ,
            array(
                "attrid" => "tst_enum1",
                "key" => 5,
                "label" => "cinqo",
                "order" => 1,
                "expectedOrder" => 1
            ) ,
            array(
                "attrid" => "tst_enum1",
                "key" => 50,
                "label" => "mucho",
                "order" => 50,
                "expectedOrder" => 5
            ) ,
            array(
                "attrid" => "tst_enum1",
                "key" => 500,
                "label" => "mucho mucho",
                "order" => 0,
                "expectedOrder" => 5
            ) ,
            array(
                "attrid" => "tst_enum1",
                "key" => 400,
                "label" => "mucho mucho",
                "order" => - 1,
                "expectedOrder" => 5
            )
        );
    }
    public function dataDocEnumMod()
    {
        return array(
            array(
                "attrid" => "tst_enum1",
                "key" => 1,
                "label" => "one more",
                "order" => 4,
                "expectedOrder" => 3
            ) ,
            array(
                "attrid" => "tst_enum1",
                "key" => 1,
                "label" => "one more",
                "order" => 0,
                "expectedOrder" => 4
            )
        );
    }
    public function dataDocAddRelativeOrder()
    {
        return array(
            array(
                "attrid" => "tst_enuma",
                "key" => "a1",
                "label" => "one more",
                "before" => "b",
                "expectedOrder" => 2
            ) ,
            array(
                "attrid" => "tst_enuma",
                "key" => "a0",
                "label" => "one more",
                "before" => "a",
                "expectedOrder" => 1
            ) ,
            array(
                "attrid" => "tst_enuma",
                "key" => "a0",
                "label" => "one more",
                "before" => "d",
                "expectedOrder" => 4
            ) ,
            array(
                "attrid" => "tst_enuma",
                "key" => "a0",
                "label" => "one more",
                "before" => "",
                "expectedOrder" => 5
            )
        );
    }
    public function dataDocModRelativeOrder()
    {
        return array(
            array(
                "attrid" => "tst_enuma",
                "key" => "b",
                "label" => "one more",
                "before" => "b",
                "expectedOrder" => 2
            ) ,
            array(
                "attrid" => "tst_enuma",
                "key" => "d",
                "label" => "one more",
                "before" => "a",
                "expectedOrder" => 1
            ) ,
            array(
                "attrid" => "tst_enuma",
                "key" => "a",
                "label" => "one more",
                "before" => "",
                "expectedOrder" => 4
            )
        );
    }
    
    public function dataDocEnumDisabled()
    {
        return array(
            array(
                "attrid" => "tst_enum1",
                "disable" => array(
                    "1"
                ) ,
                "expect" => array(
                    " ",
                    "0",
                    "2"
                )
            ) ,
            array(
                "attrid" => "tst_enum1",
                "disable" => array() ,
                "expect" => array(
                    " ",
                    "0",
                    "1",
                    "2"
                )
            ) ,
            array(
                "attrid" => "tst_enum1",
                "disable" => array(
                    " ",
                    "0",
                    "1",
                    "2"
                ) ,
                "expect" => array()
            ) ,
            array(
                "attrid" => "tst_enum1",
                "disable" => array(
                    " ",
                    "0",
                    "1"
                ) ,
                "expect" => array(
                    "2"
                )
            ) ,
            array(
                "attrid" => "tst_enuma",
                "disable" => array(
                    "b",
                    "d"
                ) ,
                "expect" => array(
                    "a",
                    "c"
                )
            )
        );
    }
    public function dataDocEnumAddLocale()
    {
        return array(
            array(
                "attrid" => "tst_enum1",
                "key" => 4,
                "label" => "quatro",
                "locale" => array(
                    "en_US" => "four",
                    "fr_FR" => "quatre"
                )
            )
        );
    }
}
