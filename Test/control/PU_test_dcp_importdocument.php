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

class TestImportDocument extends TestCaseDcpCommonFamily
{
    /**
     * import TST_FAMIMP1 family
     * @static
     * @return string
     */
    protected static function getCommonImportFile()
    {
        return "PU_data_dcp_importfamily1.ods";
    }
    /**
     * count error from import report message
     * @param $err
     * @return int|void
     */
    private function countErrors($err)
    {
        if (!$err) return 0;
        return count(explode("]\n[", $err));
    }
    /**
     * @dataProvider dataDocumentFiles
     */
    public function testErrorImportDocument($documentFile, $nbError)
    {
        try {
        $err = $this->importDocument($documentFile);
        } catch (\Exception $e) {
            $err=$e->getMessage();
        }
        $this->assertNotEmpty($err);
        $this->assertEquals($nbError, $this->countErrors($err) , sprintf('status error : "%s"', $err));
        
        $s = new \SearchDoc("", "TST_FAMIMP1");
        $this->assertEquals(0, $s->onlyCount() , "document is created and must be not");
    }
    
    public function dataDocumentFiles()
    {
        return array(
            array(
                "PU_data_dcp_importdoc1.xml",
                1
            ) ,
            array(
                "PU_data_dcp_importdoc2.xml",
                1
            ) ,
            array(
                "PU_data_dcp_importdoc3.xml",
                2
            ) ,
            array(
                "PU_data_dcp_importdoc4.xml",
                1
            )
        );
    }
}
?>