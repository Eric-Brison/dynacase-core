<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package DCP
*/

namespace PU;

require_once 'PU_testcase_dcp_document.php';

class TestImportProfil extends TestCaseDcpDocument
{
    protected static $outputDir;
    /**
     * @dataProvider dataBadFamilyFiles
     */
    public function testErrorImportProfil($familyFile, $expectedErrors)
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
            $this->assertContains($expectedError, $err, sprintf("not the correct error reporting"));
        }
    }
    /**
     * @dataProvider dataGoodFamilyFiles
     */
    public function testExecuteImportProfil($familyFile)
    {
        $err = '';
        try {
            $this->importDocument($familyFile);
        }
        catch(\Exception $e) {
            $err = $e->getMessage();
        }
        $this->assertEmpty($err, sprintf("profil error detected : %s", $err));
    }
    
    public function dataBadFamilyFiles()
    {
        return array(
            // test unknow profid
            array(
                "PU_data_dcp_badprofil1.ods",
                array(
                    "PRFL0001",
                    "PRFL0002",
                    "first",
                    "second",
                    "PRFL0003",
                    "third",
                    "TST_PDOC1",
                    "PRFL0004",
                    "PRFL0005",
                    "special"
                )
            ) ,
            array(
                "PU_data_dcp_badprofil2.ods",
                array(
                    "PRFL0100",
                    "aclfour",
                    "PRFL0101",
                    "aclfive",
                    "PRFL0103",
                    "TST_JOHNDOE"
                )
            ) ,
            array(
                "PU_data_dcp_badprofil3.ods",
                array(
                    "PRFL0200",
                    'TST_DYNPDOC1',
                    'nothing',
                    'TST_DYNPDOC2',
                    "PRFL0201",
                    'tst_title',
                    'TST_DYNPDOC3',
                    "PRFL0203"
                )
            ) ,
            
            array(
                "PU_data_dcp_badprofil4.ods",
                array(
                    "PRFL0202",
                    "family document test"
                )
            )
        );
    }
    
    public function dataGoodFamilyFiles()
    {
        return array(
            // test profil ref
            array(
                "PU_data_dcp_goodprofil1.ods"
            )
        );
    }
}
?>