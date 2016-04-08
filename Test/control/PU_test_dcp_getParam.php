<?php
/*
 * @author Anakeen
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
    /**
     * @param $paramName
     * @param $appName
     * @param $expectedProps
     * @dataProvider dataGetParamDef
     */
    public function testGetParamDef($paramName, $appName, $expectedProps)
    {
        
        $appId = null;
        if ($appName) {
            simpleQuery(self::$dbaccess, sprintf("select id from application where name='%s'", pg_escape_string($appName)) , $appId, true, true);
        }
        $paramDef = \ParamDef::getParamDef($paramName, $appId);
        if (empty($expectedProps)) {
            $this->assertEmpty($paramDef, "parameter $paramName must not be found");
        } else {
            $this->assertNotEmpty($paramDef, "parameter $paramName must be found in app  #$appId");
            foreach ($expectedProps as $kProp => $vProp) {
                $this->assertEquals($vProp, $paramDef->$kProp, "wrong property $kProp" . print_r($paramDef->getValues() , true));
            }
        }
    }
    
    public function dataGetParamDef()
    {
        return array(
            array(
                'name' => 'CORE_NON_EXISTING_PARAM',
                'app' => 'FDL',
                'expected' => ''
            ) ,
            array(
                'name' => 'AUTHENT_SHOW_LANG_SELECTION',
                'app' => 'FDL',
                'expected' => ''
            ) ,
            array(
                'name' => 'AUTHENT_SHOW_LANG_SELECTION',
                'app' => 'AUTHENT',
                'expected' => array(
                    "name" => "AUTHENT_SHOW_LANG_SELECTION",
                    "isglob" => "N"
                )
            ) ,
            array(
                'name' => 'VERSION',
                'app' => 'AUTHENT',
                'expected' => array(
                    "name" => "VERSION",
                    "isglob" => "N"
                )
            ) ,
            array(
                'name' => 'CORE_CLIENT',
                'app' => 'CORE',
                'expected' => array(
                    "name" => "CORE_CLIENT",
                    "isglob" => "Y"
                )
            ) ,
            array(
                'name' => 'CORE_CLIENT',
                'app' => '',
                'expected' => array(
                    "name" => "CORE_CLIENT",
                    "isglob" => "Y"
                )
            ) ,
            array(
                'name' => 'CORE_CLIENT',
                'app' => 'FDL',
                'expected' => array(
                    "name" => "CORE_CLIENT",
                    "isglob" => "Y"
                )
            ) ,
            array(
                'name' => 'CORE_CLIENT',
                'app' => 'FDL',
                'expected' => array(
                    "name" => "CORE_CLIENT",
                    "isglob" => "Y"
                )
            ) ,
            array(
                'name' => 'SMTP_HOST',
                'app' => '',
                'expected' => array(
                    "name" => "SMTP_HOST",
                    "isglob" => "Y"
                )
            ) ,
            array(
                'name' => 'SMTP_HOST',
                'app' => 'FDL',
                'expected' => array(
                    "name" => "SMTP_HOST",
                    "isglob" => "Y"
                )
            ) ,
            array(
                'name' => 'SMTP_HOST',
                'app' => 'APPMNG',
                'expected' => array(
                    "name" => "SMTP_HOST",
                    "isglob" => "Y"
                )
            )
        );
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
                    'name' => 'CORE_CLIENT'
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
                    'name' => 'CORE_CLIENT'
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