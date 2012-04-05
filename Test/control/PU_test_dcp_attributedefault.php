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

class TestAttributeDefault extends TestCaseDcpCommonFamily
{
    /**
     * import TST_DEFAULTFAMILY1 family
     * @static
     * @return string
     */
    protected static function getCommonImportFile()
    {
        return "PU_data_dcp_familydefault.ods";
    }
    
    protected $famName = 'TST_DEFAULTFAMILY1';
    /**
     * @dataProvider dataDefaultValues
     */
    public function testDefaultValue($attrid, $expectedvalue)
    {
        static $d = null;
        if ($d === null) {
            $d = createDoc(self::$dbaccess, $this->famName);
            $this->assertTrue(is_object($d) , sprintf("cannot create %s document", $this->famName));
        }
        $oa = $d->getAttribute($attrid);
        $this->assertNotEmpty($oa, sprintf("attribute %s not found in %s family", $attrid, $this->famName));
        $value = $d->getValue($oa->id);
        $this->assertEquals($expectedvalue, $value, sprintf("not the expected default value attribute %s", $attrid));
        
        return $d;
    }
    /**
     * @dataProvider dataDefaultParamValues
     */
    public function testDefaultParamValue($attrid, $expectedvalue)
    {
        static $d = null;
        if ($d === null) {
            $d = createDoc(self::$dbaccess, $this->famName, false, false);
            $this->assertTrue(is_object($d) , sprintf("cannot create %s1 document", $this->famName));
        }
        $oa = $d->getAttribute($attrid);
        $this->assertNotEmpty($oa, sprintf("attribute %s not found in %s family", $attrid, $this->famName));
        $value = $d->getParamValue($oa->id);
        $this->assertEquals($expectedvalue, $value, sprintf("not the expected default value attribute %s", $attrid));
        
        return $d;
    }
    /**
     * @dataProvider dataDefaultInheritedValues
     */
    public function testDefaultInheritedValue($famid, array $expectedvalues, array $expectedParams)
    {
        $d = createDoc(self::$dbaccess, $famid);
        $this->assertTrue(is_object($d) , sprintf("cannot create %s document", $famid));
        
        foreach ($expectedvalues as $attrid => $expectedValue) {
            $oa = $d->getAttribute($attrid);
            $this->assertNotEmpty($oa, sprintf("attribute %s not found in %s family", $attrid, $famid));
            $value = $d->getValue($oa->id);
            
            $this->assertEquals($expectedValue, $value, sprintf("not the expected default value attribute %s", $attrid));
        }
        foreach ($expectedParams as $attrid => $expectedValue) {
            $oa = $d->getAttribute($attrid);
            $this->assertNotEmpty($oa, sprintf("parameter %s not found in %s family", $attrid, $famid));
            $value = $d->getParamValue($oa->id);
            $this->assertEquals($expectedValue, $value, sprintf("not the expected default value parameter %s", $attrid));
        }
        return $d;
    }
    /**
     * @dataProvider dataDefaultInherited
     */
    public function testDefaultInherited($famid, array $expectedvalues, array $expectedParams)
    {
        /**
         * @var  \DocFam $d
         */
        $d = new_Doc(self::$dbaccess, $famid);
        $this->assertTrue(is_object($d) , sprintf("cannot get %s family", $famid));
        
        foreach ($expectedvalues as $attrid => $expectedValue) {
            
            $value = $d->getDefValue($attrid);
            $this->assertEquals($expectedValue, $value, sprintf("not the expected default value attribute %s", $attrid));
        }
        foreach ($expectedParams as $attrid => $expectedValue) {
            
            $value = $d->getParamValue($attrid);
            $this->assertEquals($expectedValue, $value, sprintf("not the expected default value parameter %s", $attrid));
        }
        return $d;
    }
    
    public function dataDefaultInherited()
    {
        return array(
            array(
                "TST_DEFAULTFAMILY2",
                array(
                    "TST_TITLE" => "First",
                    "TST_NUMBER1" => "::isOne()",
                    "TST_NUMBER2" => "::oneMore(TST_NUMBER1)",
                    "TST_NUMBER3" => "::oneMore(2)"
                ) ,
                array(
                    'TST_P1' => 'PFirst',
                    "TST_P2" => "10",
                    "TST_P3" => "::oneMore(TST_P2)"
                )
            ) ,
            array(
                "TST_DEFAULTFAMILY3",
                array(
                    "TST_TITLE" => "Second",
                    "TST_NUMBER1" => "::isOne()",
                    "TST_NUMBER2" => "::simpleAdd(12,TST_NUMBER1)",
                    "TST_NUMBER3" => "::oneMore(2)"
                ) ,
                array(
                    'TST_P1' => 'PSecond',
                    "TST_P2" => "10",
                    "TST_P3" => "::oneMore(TST_P2)"
                )
            ) ,
            array(
                "TST_DEFAULTFAMILY4",
                array(
                    "TST_TITLE" => "Third",
                    "TST_NUMBER1" => "::isOne()",
                    "TST_NUMBER2" => "::oneMore(TST_NUMBER1)",
                    "TST_NUMBER3" => ""
                ) ,
                array(
                    'TST_P1' => 'PThird',
                    "TST_P2" => "10",
                    "TST_P3" => "::oneMore(TST_P2)"
                )
            )
        );
    }
    
    public function dataDefaultInheritedValues()
    {
        return array(
            array(
                "TST_DEFAULTFAMILY2",
                array(
                    "TST_TITLE" => "First",
                    "TST_NUMBER1" => "1",
                    "TST_NUMBER2" => "2",
                    "TST_NUMBER3" => "3"
                ) ,
                array(
                    'TST_P1' => 'PFirst',
                    "TST_P2" => "10",
                    "TST_P3" => "11"
                )
            ) ,
            array(
                "TST_DEFAULTFAMILY3",
                array(
                    "TST_TITLE" => 'Second',
                    "TST_NUMBER1" => "1",
                    "TST_NUMBER2" => "13",
                    "TST_NUMBER3" => "3"
                ) ,
                array(
                    "TST_P1" => 'PSecond',
                    "TST_P2" => "10",
                    "TST_P3" => "11"
                )
            ) ,
            array(
                "TST_DEFAULTFAMILY4",
                array(
                    "TST_TITLE" => 'Third',
                    "TST_NUMBER1" => "1",
                    "TST_NUMBER2" => "2",
                    "TST_NUMBER3" => ""
                ) ,
                array(
                    "TST_P1" => 'PThird',
                    "TST_P2" => "10",
                    "TST_P3" => "11"
                )
            )
        );
    }
    
    public function dataDefaultValues()
    {
        return array(
            array(
                'TST_TITLE',
                'The title'
            ) ,
            array(
                'TST_NUMBER1',
                '1'
            ) ,
            array(
                'TST_NUMBER2',
                '2'
            ) ,
            array(
                'TST_NUMBER3',
                '3'
            ) ,
            array(
                'TST_NUMBER4',
                '4'
            ) ,
            array(
                'TST_NUMBER5',
                '5'
            ) ,
            array(
                'TST_NUMBER6',
                '50'
            ) ,
            
            array(
                'TST_NUMBER7',
                '53'
            ) ,
            
            array(
                'TST_NUMBER8',
                '6'
            ) ,
            array(
                'TST_NUMBER9',
                '11'
            ) ,
            array(
                'TST_TEXT1',
                'TST_TITLE'
            ) ,
            array(
                'TST_TEXT2',
                'TST_TITLE'
            ) ,
            array(
                'TST_TEXT3',
                'TST_TITLE,TST_TITLE'
            ) ,
            array(
                'TST_TEXT4',
                'it is,simple word,testing'
            ) ,
            array(
                'TST_TEXT5',
                'it\'s,a "citation",and "second"'
            ) ,
            array(
                'TST_TEXT6',
                '[:TST_TITLE:]'
            )
        );
    }
    
    public function dataDefaultParamValues()
    {
        return array(
            array(
                'TST_P1',
                'test one'
            ) ,
            array(
                'TST_P2',
                '10'
            ) ,
            array(
                'TST_P3',
                '11'
            )
        );
    }
}
?>