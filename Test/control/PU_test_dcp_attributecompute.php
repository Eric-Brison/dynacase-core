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

class TestAttributeCompute extends TestCaseDcpCommonFamily
{
    /**
     * import TST_DEFAULTFAMILY1 family
     * @static
     * @return string
     */
    protected static function getCommonImportFile()
    {
        return "PU_data_dcp_familycomputed.ods";
    }
    
    protected $famName = 'TST_FAMILYCOMPUTED';
    /**
     * @dataProvider dataComputedValues
     */
    public function testComputedValue(array $inputs, array $expectedvalues, array $logMatch)
    {
        static $d = null;
        if ($d === null) {
            $d = createDoc(self::$dbaccess, $this->famName);
            $this->assertTrue(is_object($d) , sprintf("cannot create %s document", $this->famName));
        }
        
        foreach ($inputs as $k => $v) {
            $d->setValue($k, $v);
        }
        
        $tmpLogFile = tempnam(getTmpDir() , __FUNCTION__);
        $d->log = new \Log($tmpLogFile, $d->log->application, $d->log->function);
        
        $info = new \storeInfo();
        $err = $d->store($info);
        
        $log = file_get_contents($tmpLogFile);
        unlink($tmpLogFile);
        
        $this->assertEmpty($err, sprintf("cannot modify %s document", $this->famName));
        foreach ($expectedvalues as $k => $v) {
            if (is_array($v)) $value = $d->getMultipleRawValues($k);
            else $value = $d->getRawValue($k);
            $this->assertEquals($v, $value, sprintf("error computed %s", $k));
        }
        $this->assertEmpty($info->refresh, sprintf("refresh returned with unexpected error: %s", $info->refresh));
        foreach ($logMatch as $re) {
            $this->assertTrue(preg_match($re, $log) == 1, sprintf("log did not contained expected message '%s'", $re));
        }
        
        return $d;
    }
    
    public function dataComputedValues()
    {
        
        $arg = array(
            array(
                "x" => 10,
                "y1" => 123,
                "z1" => 230
            ) ,
            array(
                "x" => 80,
                "y1" => 133,
                "z1" => 238
            )
        );
        $out = array();
        foreach ($arg as $a) {
            $x = $a["x"];
            $y1 = $a["y1"];
            $z1 = $a["z1"];
            $out[] = array(
                "inputs" => array(
                    'tst_number1' => $x,
                    'tst_number10' => array(
                        $y1,
                        $z1
                    )
                ) ,
                'ouputs' => array(
                    'tst_number1' => $x1 = $x,
                    'tst_number2' => $x2 = $x + 1,
                    'tst_number3' => $x3 = $x2 + 1,
                    'tst_number4' => $x4 = 30 + 5 + 15,
                    'tst_number6' => $x6 = 1 + 2 + 3,
                    'tst_number7' => $x7 = $x1 + $x2 + $x3,
                    'tst_number9' => $x9 = $x3 + 10,
                    'tst_text1' => 'NULL',
                    'tst_text2' => 'zero',
                    'tst_text3' => "[the][beautiful][ rainbow ,][.]",
                    'tst_text4' => 'one',
                    "tst_number11" => array(
                        $y2 = $y1 + 10,
                        $z2 = $z1 + 10
                    ) ,
                    "tst_number12" => array(
                        $y1 + $y2,
                        $z1 + $z2
                    ) ,
                    'tst_count' => 2,
                    'tst_vis_i_1' => '',
                    'tst_vis_i_2' => '',
                    'tst_vis_i_3' => ''
                ) ,
                'logMatch' => array(
                    '/\[W\].*{ATTR1800} value of attribute "tst_vis_i_1"/',
                    '/\[W\].*{ATTR1800} value of attribute "tst_vis_i_3"/'
                )
            );
        }
        return $out;
    }
}
