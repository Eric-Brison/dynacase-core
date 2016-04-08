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

class TestDir extends TestCaseDcpCommonFamily
{
    protected static function getCommonImportFile()
    {
        return array(
            'PU_data_dcp_dir.ods'
        );
    }

    /**
     * @param $dirId
     * @dataProvider dataClearDir
     */
    public function testClearDir($dirId)
    {
        /**
         * @var \Dcp\Family\Dir $dir
         */
        $dir = new_Doc('', $dirId, true);
        $this->assertTrue($dir->isAlive() , sprintf("Could not get dir with id '%s'.", $dirId));
        $err = $dir->Clear();
        $this->assertEmpty($err, sprintf("Clear() on dir with id '%s' returned unexpected error message: %s", $dirId, $err));
        $content = $dir->getContent(false);
        $this->assertCount(0, $content, sprintf("Unexpected content's count (%s) for dir with id '%s'.", count($content), $dirId));
    }
    
    public function dataClearDir()
    {
        return array(
            array(
                'DIR_FOO'
            )
        );
    }
}
