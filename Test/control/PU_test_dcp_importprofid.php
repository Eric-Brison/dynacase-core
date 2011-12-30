<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package DCP
*/

namespace PU;

require_once 'PU_testcase_dcp_document.php';

class TestImportProfid extends TestCaseDcpDocument
{
    protected static $outputDir;
    /**
     * @dataProvider dataBadFamilyFiles
     */
    public function testErrorImportProfid($familyFile, $expectedErrors)
    {
        $err = '';
        try {
            $this->importDocument($familyFile);
        }
        catch(\Exception $e) {
            $err = $e->getMessage();
        }
        $this->assertNotEmpty($err, "no import error detected");
        if (!is_array($expectedErrors)) $expectedErrors = array(
            $expectedErrors
        );
        
        foreach ($expectedErrors as $expectedError) {
            $this->assertContains($expectedError, $err, sprintf("not the correct error reporting : %s", $err));
        }
    }
    /**
     * @dataProvider dataGoodFamilyFiles
     */
    public function testImportProfid($familyFile)
    {
        $err = '';
        try {
            $this->importDocument($familyFile);
        }
        catch(\Exception $e) {
            $err = $e->getMessage();
        }
        $this->assertEmpty($err, sprintf("profid error detected %s", $err));
    }
    
    public function dataBadFamilyFiles()
    {
        return array(
            // test unknow profid
            array(
                "PU_data_dcp_badprofid1.ods",
                array(
                    "PRFD0001",
                    "inconnu"
                )
            ) ,
            // test unknow profid
            array(
                "PU_data_dcp_badprofid2.ods",
                array(
                    "PRFD0002",
                    "TST_FOLDER1"
                )
            )
        );
    }
    
    public function dataGoodFamilyFiles()
    {
        return array(
            // test pfam ref
            array(
                "PU_data_dcp_goodprofid1.ods"
            ) ,
            // test itself ref
            array(
                "PU_data_dcp_goodprofid2.ods"
            )
        );
    }
}
?>