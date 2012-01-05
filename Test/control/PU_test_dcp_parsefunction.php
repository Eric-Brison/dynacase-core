<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package DCP
*/

namespace PU;

require_once 'PU_testcase_dcp.php';

class TestParseFunction extends TestCaseDcp
{
    /**
     * @dataProvider dataGoodStruct
     */
    public function testParseFunctionCall($callName, $expectedStructs)
    {
        $err = '';
        
        $oParse = new \parseFamilyFunction();
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
    public function testParseFunctionCallErrors($callName, $expectedErrors)
    {
        $err = '';
        $oParse = new \parseFamilyFunction();
        $struct = $oParse->parse($callName);
        $err = $struct->getError();
        
        $this->assertNotEmpty($err, "function struct no error detected");
        foreach ($expectedErrors as $expectedError) {
            
            $this->assertContains($expectedError, $err, sprintf('not the correct error reporting : "%s" : %s', $err, print_r($struct, true)));
        }
    }
    
    public function dataGoodStruct()
    {
        return array(
            // test simple function
            array(
                "good():OUT",
                array(
                    "functionName" => "good",
                    "outputs" => array(
                        "OUT"
                    )
                )
            ) ,
            // test simple function
            array(
                "good ():OUT",
                array(
                    "functionName" => "good"
                )
            ) ,
            // test simple function
            array(
                "good ():OUT1, OUT2",
                array(
                    "functionName" => "good",
                    "outputs" => array(
                        "OUT1",
                        "OUT2"
                    )
                )
            ) ,
            // test attr attribute arg
            array(
                "good(ONE):OUT",
                array(
                    "functionName" => "good",
                    "inputs" => array(
                        array(
                            "name" => "ONE",
                            "type" => "any"
                        )
                    )
                )
            ) , // test attr attribute arg
            array(
                "good(ONE, CT):OUT",
                array(
                    "functionName" => "good",
                    "inputs" => array(
                        array(
                            "name" => "ONE",
                            "type" => "any"
                        ) ,
                        array(
                            "name" => "CT",
                            "type" => "any"
                        )
                    )
                )
            ) , // test attr attribute arg
            array(
                "good(ONE, CT, 'CT'):OUT",
                array(
                    "functionName" => "good",
                    "inputs" => array(
                        array(
                            "name" => "ONE",
                            "type" => "any"
                        ) ,
                        array(
                            "name" => "CT",
                            "type" => "any"
                        ) ,
                        array(
                            "name" => "CT",
                            "type" => "string"
                        )
                    )
                )
            ) ,
            // test attr attribute arg
            array(
                'good( "ONE, TWO" ):OUT',
                array(
                    "functionName" => "good",
                    "inputs" => array(
                        array(
                            "name" => "ONE, TWO",
                            "type" => "string"
                        )
                    )
                )
            ) ,
            // test attr attribute arg
            array(
                'good( " ONE \'TWO\' " ):OUT',
                array(
                    "functionName" => "good",
                    "inputs" => array(
                        array(
                            "name" => " ONE 'TWO' ",
                            "type" => "string"
                        )
                    )
                )
            ) ,
            // test attr attribute arg
            array(
                "good( ONE ):OUT",
                array(
                    "functionName" => "good",
                    "inputs" => array(
                        array(
                            "name" => "ONE",
                            "type" => "any"
                        )
                    )
                )
            ) ,
            // test attr string arg
            array(
                'good("ONE"):OUT',
                array(
                    "functionName" => "good",
                    "inputs" => array(
                        array(
                            "name" => "ONE",
                            "type" => "string"
                        )
                    )
                )
            ) ,
            // test attr string arg
            array(
                'good( "ONE" ):OUT',
                array(
                    "functionName" => "good",
                    "inputs" => array(
                        array(
                            "name" => "ONE",
                            "type" => "string"
                        )
                    )
                )
            ) ,
            // test attr string arg
            array(
                'good("ONE\"TWO"):OUT',
                array(
                    "functionName" => "good",
                    "inputs" => array(
                        array(
                            "name" => 'ONE"TWO',
                            "type" => "string"
                        )
                    )
                )
            ) ,
            // test attr string arg
            array(
                'good("ONE","TWO"):OUT',
                array(
                    "functionName" => "good",
                    "inputs" => array(
                        array(
                            "name" => 'ONE',
                            "type" => "string"
                        ) ,
                        array(
                            "name" => 'TWO',
                            "type" => "string"
                        )
                    )
                )
            ) ,
            // test attr string arg
            array(
                "good('ONE'):OUT",
                array(
                    "functionName" => "good",
                    "inputs" => array(
                        array(
                            "name" => "ONE",
                            "type" => "string"
                        )
                    )
                )
            ) ,
            // test attr string arg
            array(
                "good('ONE,'TWO):OUT",
                array(
                    "functionName" => "good",
                    "inputs" => array(
                        array(
                            "name" => 'ONE',
                            "type" => "string"
                        ) ,
                        array(
                            "name" => 'TWO',
                            "type" => "string"
                        )
                    )
                )
            ) ,
            // test attr string arg
            array(
                "good('ONE','TWO'):OUT",
                array(
                    "functionName" => "good",
                    "inputs" => array(
                        array(
                            "name" => 'ONE',
                            "type" => "string"
                        ) ,
                        array(
                            "name" => 'TWO',
                            "type" => "string"
                        )
                    )
                )
            ) ,
            // test attr string arg
            array(
                "good('(ONE)',')TWO(:'):OUT",
                array(
                    "functionName" => "good",
                    "inputs" => array(
                        array(
                            "name" => '(ONE)',
                            "type" => "string"
                        ) ,
                        array(
                            "name" => ')TWO(:',
                            "type" => "string"
                        )
                    )
                )
            ) ,
            // test attr string arg
            array(
                "good('ONE', 'TWO'):OUT",
                array(
                    "functionName" => "good",
                    "inputs" => array(
                        array(
                            "name" => 'ONE',
                            "type" => "string"
                        ) ,
                        array(
                            "name" => 'TWO',
                            "type" => "string"
                        )
                    )
                )
            ) ,
            // test attr string arg
            array(
                "good('ONE'TWO):OUT",
                array(
                    "functionName" => "good",
                    "inputs" => array(
                        array(
                            "name" => "ONE'TWO",
                            "type" => "string"
                        )
                    )
                )
            ) ,
            // test attr string arg
            array(
                "good('ONE\\'TWO):OUT",
                array(
                    "functionName" => "good",
                    "inputs" => array(
                        array(
                            "name" => "ONE'TWO",
                            "type" => "string"
                        )
                    )
                )
            ) , // test attr attribute arg
            array(
                "good( a simple text ):OUT",
                array(
                    "functionName" => "good",
                    "inputs" => array(
                        array(
                            "name" => "a simple text",
                            "type" => "string"
                        )
                    )
                )
            ) ,
            // test attr attribute arg
            array(
                "MY_APP:good(ONE):",
                array(
                    "functionName" => "good",
                    "appName" => "MY_APP",
                    "inputs" => array(
                        array(
                            "name" => "ONE",
                            "type" => "any"
                        )
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
                "test:out",
                array(
                    "ATTR1201",
                    "test"
                )
            ) ,
            // test invert parenthesis
            array(
                "test)(:out",
                array(
                    "ATTR1201",
                    "test"
                )
            ) ,
            // test space func name
            array(
                "test one():OUT",
                array(
                    "ATTR1202",
                    "test one"
                )
            ) ,
            // test space func name
            array(
                "testone() O:UT",
                array(
                    "ATTR1201"
                )
            ) ,
            // test double quote
            array(
                'test("ONE):OUT',
                array(
                    "ATTR1204"
                )
            ) ,
            // test double quote extra
            array(
                'test("ONE" a):OUT',
                array(
                    "ATTR1204"
                )
            ) ,
            // test without output
            array(
                'test("ONE")',
                array(
                    "ATTR1206"
                )
            ) ,
            // test simple function
            array(
                "good ():OUT 1",
                array(
                    "ATTR1207"
                )
            ) ,
        );
    }
}
?>