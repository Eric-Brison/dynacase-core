<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * @author Anakeen
 * @package Dcp\Pu
 */

namespace Dcp\Pu;

require_once 'PU_testcase_dcp.php';
require_once 'Lib.Main.php';

class TestVolatileParam extends TestCaseDcp
{
    /**
     * @dataProvider dataParamKeys
     */
    public function testVolatileExists($key, $value)
    {
        static $first = true;
        $a = self::getAction();

        initExplorerParam($a->parent);
        
        $v = $a->getParam($key, null);
        $this->assertTrue($v === $value, sprintf("expect %s value for %s key", $value, $key));
    }
    
    public function dataParamKeys()
    {
        return array(
            array(
                'ISIE',
                false
            ) ,
            array(
                'ISIE6',
                false
            ) ,
            array(
                'ISIE7',
                false
            ) ,
            array(
                'ISIE8',
                false
            ) ,
            array(
                'ISIE9',
                false
            ) ,
            array(
                'ISIE10',
                false
            ) ,
            array(
                'ISAPPLEWEBKIT',
                false
            ) ,
            array(
                'ISSAFARI',
                false
            ) ,
            array(
                'ISCHROME',
                false
            ) ,
            array(
                'HOULALA',
                null
            )
        );
    }
}
?>