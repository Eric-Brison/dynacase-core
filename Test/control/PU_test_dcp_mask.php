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

use Dcp\ApiUsage\Exception;

require_once 'PU_testcase_dcp_commonfamily.php';

class TestMask extends TestCaseDcpCommonFamily
{
    /**
     * import some documents
     * @static
     * @return string
     */
    protected static function getCommonImportFile()
    {
        return "PU_data_dcp_maskfamily.ods";
    }
    /**
     * apply mask
     * @dataProvider dataGoodMask
     * @param $docid
     * @param $mid
     */
    public function testsetMask($docid, $mid, array $expectedVisibilities)
    {
        $doc = new_doc(self::$dbaccess, $docid, true);
        
        if ($doc->isAlive()) {
            $err = $doc->setMask($mid);
            $this->assertEmpty($err, sprintf("mask apply error %s", $err));
            foreach ($expectedVisibilities as $attrid => $expectVis) {
                $this->assertEquals($expectVis, $doc->getAttribute($attrid)->mvisibility, sprintf("Attribute $attrid"));
            }
        } else {
            $this->markTestIncomplete(sprintf(_('Document %d not alive.') , $docid));
        }
    }
    /**
     * apply mask (detect errors)
     * @dataProvider dataBadMask
     * @param $docid
     * @param $mid
     * @param array $expectedErrors
     */
    public function testsetMaskError($docid, $mid, array $expectedErrors)
    {
        
        $doc = new_doc(self::$dbaccess, $docid, true);
        
        if ($doc->isAlive()) {
            
            $err = $doc->applyMask($mid);
            $this->assertNotEmpty($err, sprintf("mask apply need error"));
            foreach ($expectedErrors as $error) {
                $this->assertContains($error, $err, sprintf("mask apply not correct error %s", $err));
            }
        } else {
            $this->markTestIncomplete(sprintf(_('Document %d not alive.') , $docid));
        }
    }
    /**
     * @param       $file
     * @param array $expectedErrors
     * @dataProvider dataBadImportMask
     */
    public function testBadImportMask($file, array $expectedErrors)
    {
        try {
            self::importDocument($file);
            $this->assertTrue(false, "Mask import errors must not be empty");
        }
        catch(\Dcp\Exception $e) {
            foreach ($expectedErrors as $error) {
                $this->assertContains($error, $e->getMessage() , sprintf("mask apply not correct error %s", $e->getMessage()));
            }
        }
    }
    
    public function dataBadImportMask()
    {
        return array(
            ["PU_data_dcp_badMask.ods",
            ["MSK0001",
            "TST_BADMASK1",
            "MSK0002",
            "TST_BADMASK2",
            "MSK0003",
            "tst_titlex",
            "tst_numberx",
            "tst_p2"]]
        );
    }
    public function dataGoodMask()
    {
        return array(
            array(
                'TST_DOCBASE1',
                'TST_GOODMASK1',
                array(
                    "tst_title" => "R",
                    "tst_number" => "W",
                    "tst_date" => "W",
                    "tst_coltext" => "W",
                    "tst_coldate" => "W",
                    "tst_text" => "W"
                )
            ) ,
            array(
                'TST_DOCBASE1',
                'TST_GOODMASK2',
                array(
                    "tst_title" => "H",
                    "tst_number" => "H",
                    "tst_date" => "H",
                    "tst_coltext" => "H",
                    "tst_coldate" => "H",
                    "tst_text" => "W"
                )
            ) ,
            array(
                'TST_DOCBASE1',
                'TST_GOODMASK3',
                array(
                    "tst_title" => "H",
                    "tst_number" => "H",
                    "tst_date" => "H",
                    "tst_coltext" => "H",
                    "tst_coldate" => "H",
                    "tst_text" => "H"
                )
            ) ,
            array(
                'TST_DOCBASE1',
                'TST_GOODMASK4',
                array(
                    "tst_title" => "R",
                    "tst_number" => "R",
                    "tst_date" => "R",
                    "tst_coltext" => "W",
                    "tst_coldate" => "W",
                    "tst_text" => "H"
                )
            )
        );
    }
    
    public function dataBadMask()
    {
        return array(
            array(
                'TST_DOCBASE1',
                '878',
                array(
                    'DOC1000',
                    '878'
                )
            ) ,
            array(
                'TST_DOCBASE1',
                'TST_UNKNOW',
                array(
                    'DOC1004',
                    'TST_UNKNOW'
                )
            ) ,
            array(
                'TST_DOCBASE1',
                'TST_MASK2',
                array(
                    'DOC1002',
                    'TST_MASK2',
                    'IGROUP'
                )
            ) ,
            array(
                'TST_DOCBASE1',
                'TST_DOCBASE1',
                array(
                    'DOC1001',
                    'TST_DOCBASE1'
                )
            )
        );
    }
}
?>