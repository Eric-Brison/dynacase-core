<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package Dcp\Pu
*/

namespace Dcp\Pu;

require_once 'PU_testcase_dcp.php';

class TestGetParam extends TestCaseDcp
{
    /**
     * @dataProvider dataGetCoreParamNonExisting
     */
    public function testGetCoreParamNonExisting($data)
    {
        $value = getCoreParam($data['name'], $data['def']);
        
        $sameType = (gettype($value) == gettype($data['expected']));
        $sameValue = ($value == $data['expected']);
        
        $this->assertTrue($sameType, sprintf("Result type mismatch: found type '%s' while expecting type '%s'.", gettype($value) , gettype($data['expected'])));
        $this->assertTrue($sameValue, sprintf("Unexpected result: found '%s' while expecting '%s'.", $value, $data['expected']));
    }
    /**
     * @dataProvider dataGetParamNonExisting
     */
    public function testGetParamNonExisting($data)
    {
        $value = getParam($data['name'], $data['def']);
        
        $sameType = (gettype($value) == gettype($data['expected']));
        $sameValue = ($value == $data['expected']);
        
        $this->assertTrue($sameType, sprintf("Result type mismatch: found type '%s' while expecting type '%s'.", gettype($value) , gettype($data['expected'])));
        $this->assertTrue($sameValue, sprintf("Unexpected result: found '%s' while expecting '%s'.", $value, $data['expected']));
    }
    /**
     * @dataProvider dataGetCoreParamIsSet
     */
    public function testGetCoreParamIsSet($data)
    {
        $value = getCoreParam($data['name'], null);
        
        $this->assertTrue(($value !== null) , "Returned value is not set.");
    }
    /**
     * @dataProvider dataGetCoreParamIsSet
     */
    public function testGetParamIsSet($data)
    {
        $value = getParam($data['name'], null);
        
        $this->assertTrue(($value !== null) , "Returned value is not set.");
    }
    
    public function dataGetCoreParamNonExisting()
    {
        return array(
            array(
                array(
                    'name' => 'CORE_NON_EXISTING_PARAM',
                    'def' => 'DOES_NOT_EXISTS',
                    'expected' => 'DOES_NOT_EXISTS'
                )
            )
        );
    }
    
    public function dataGetParamNonExisting()
    {
        return array(
            array(
                array(
                    'name' => 'CORE_NON_EXISTING_PARAM',
                    'def' => 'DOES_NOT_EXISTS',
                    'expected' => 'DOES_NOT_EXISTS'
                )
            )
        );
    }
    
    public function dataGetCoreParamIsSet()
    {
        return array(
            array(
                array(
                    'name' => 'CORE_ANAKEEN'
                    // CORE 'G'
                    
                ) ,
                array(
                    'name' => 'CORE_DB'
                    // CORE 'A'
                    
                )
            )
        );
    }
    
    public function dataGetParamIsSet()
    {
        return array(
            array(
                array(
                    'name' => 'CORE_ANAKEEN'
                    // CORE 'G'
                    
                ) ,
                array(
                    'name' => 'CORE_DB'
                    // CORE 'A'
                    
                )
            )
        );
    }
}
?>