<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package Dcp\Pu
 */

namespace Dcp\Pu;

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
     */
    public function testsetMask($docid, $mid)
    {
        
        $doc = new_doc(self::$dbaccess, $docid, true);
        
        if ($doc->isAlive()) {
            
            $err = $doc->applyMask($mid);
            $this->assertEmpty($err, sprintf("mask apply error %s", $err));
        } else {
            $this->markTestIncomplete(sprintf(_('Document %d not alive.') , $docid));
        }
    }
    /**
     * apply mask (detect errors)
     * @dataProvider dataBadMask
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
    
    public function dataGoodMask()
    {
        return array(
            array(
                'TST_DOCBASE1',
                'TST_MASK1'
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