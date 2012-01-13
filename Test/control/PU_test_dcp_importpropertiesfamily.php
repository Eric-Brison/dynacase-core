<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package DCP
*/

namespace PU;

require_once 'PU_testcase_dcp_document.php';

class TestImportFamilyProperty extends TestCaseDcpDocument
{
    protected static $outputDir;
    /**
     * @dataProvider dataBadFamilyFiles
     */
    public function testErrorImportFamily($familyFile, $expectedErrors)
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
    
    public function dataBadFamilyFiles()
    {
        return array(
            // test attribute too long
            array(
                "PU_data_dcp_badfamprop1.ods",
                array(
                    "FAM0500",
                    "Test 1",
                    "FAM0501",
                    "TST_BAD FAM1",
                    "FAM0502",
                    "TST_DOC32",
                    "Test Profil"
                )
            ) ,
            array(
                "PU_data_dcp_badfamprop2.ods",
                array(
                    "FAM0100",
                    "UNKNOW",
                    "FAM0101",
                    "TST_BADSAME",
                    "FAM0102",
                    "TST_BADFAM11",
                    "WDOC"
                )
            ) ,
            array(
                "PU_data_dcp_badfamprop3.ods",
                array(
                    "FAM0200"
                )
            ) ,
            array(
                "PU_data_dcp_badfamprop4.ods",
                array(
                    "FAM0400",
                    "FAM0401",
                    "TestNotFound"
                )
            ) ,
            array(
                "PU_data_dcp_badfamprop5.ods",
                array(
                    "RESE0001",
                    "patate",
                    "DFLD0001",
                    "folderNotFound",
                    "DFLD0002",
                    "TST_DOCPRF8",
                    
                    "CFLD0001",
                    "searchNotFound",
                    "CFLD0002",
                    "TST_DOCPRF8",
                    
                    "CVID0001",
                    "cvNotFound",
                    "CVID0002",
                    "TST_DOCPRF8",
                    "WID0001",
                    "workflowNotFound",
                    "WID0002",
                    "TST_DOCPRF8",
                    "CPRF0001",
                    "profilNotFound"
                    //"CPRF0002",
                    
                )
            ),
                        array(
                            "PU_data_dcp_badfamprop6.ods",
                            array(
                                "MTHD0001",
                                "MTHD0002",
                                "Nothing"
                            )
                        )
        );
    }
}
?>