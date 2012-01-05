<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package DCP
*/

namespace PU;

require_once 'PU_testcase_dcp.php';

class TestParseMethod extends TestCaseDcp
{
    /**
     * @dataProvider dataGoodStruct
     */
    public function testParseMethodCall($callName, $expectedStructs)
    {
        $err = '';
        
        $oParse = new \parseFamilyMethod();
        $struct = $oParse->parse($callName);
        $err = $struct->getError();
        
        $this->assertEmpty($err, sprintf("function struct error detected : %s, %s", $err, print_r($struct, true)));
        foreach ($expectedStructs as $key => $expectedValue) {
            if (is_array($expectedValue)) {
                $values = $struct->$key;
                foreach ($expectedValue as $kv => $singleValue) {
                    if (is_array($singleValue)) {
                        foreach ($singleValue as $k => $v) {
                            $this->assertEquals($v, $values[$kv]->$k, sprintf("test struct %s/%s [%s]", $key, $k . "[$kv]", print_r($values, true)));
                        }
                    } else {
                        $this->assertEquals($singleValue, $values[$kv], sprintf("testout struct %s/%s ", $key . "[$kv]", print_r($struct, true)));
                    }
                }
            } else {
                $this->assertEquals($expectedValue, $struct->$key);
            }
            //$this->assertContains($expectedError, $err, sprintf("not the correct error reporting : %s", $err));
            
        }
    }
    /**
     * test struct errors
     * @dataProvider databadStruct
     */
    public function testParseMethodCallErrors($callName, $expectedErrors)
    {
        $err = '';
        $oParse = new \parseFamilyMethod();
        $struct = $oParse->parse($callName);
        $err = $struct->getError();
        
        $this->assertNotEmpty($err, sprintf("function struct no error detected :%s", print_r($struct, true)));
        foreach ($expectedErrors as $expectedError) {
            
            $this->assertContains($expectedError, $err, sprintf('not the correct error reporting : "%s" : %s', $err, print_r($struct, true)));
        }
    }
    
    public function dataGoodStruct()
    {
        return array(
            // test simple function
            array(
                "::getTitle():OUT",
                array(
                    "methodName" => "getTitle",
                    "outputs" => array(
                        "OUT"
                    )
                )
            ) ,
            // test simple function
            array(
                "::getTitle ()",
                array(
                    "methodName" => "getTitle",
                    "className" => ""
                )
            ) ,
            // test simple function
            array(
                "myClass::getTitle ()",
                array(
                    "methodName" => "getTitle",
                    "className" => "myClass"
                )
            ) ,
            // test attr string arg
            array(
                '::getTitle( "ONE" ): OUT',
                array(
                    "methodName" => "getTitle",
                    "inputs" => array(
                        array(
                            "name" => "ONE",
                            "type" => "string"
                        )
                    ) ,
                    "outputs" => array(
                        "OUT"
                    )
                )
            )
        );
    }
    
    public function databadStruct()
    {
        return array(
            // test no parenthesis
            array(
                "test()",
                array(
                    "ATTR1251",
                    "test"
                )
            ) ,
            array(
                "::test",
                array(
                    "ATTR1201",
                    "test"
                )
            ) ,
            array(
                "::test two()",
                array(
                    "ATTR1252",
                    "test two"
                )
            ) ,
            array(
                "class one::testTwo()",
                array(
                    "ATTR1253",
                    "class one"
                )
            ) ,
            array(
                "::testTwo():ONE,TWO",
                array(
                    "ATTR1254"
                )
            ) ,
            array(
                "::testTwo() ONE",
                array(
                    "ATTR1201"
                )
            )
        );
    }
}
?>