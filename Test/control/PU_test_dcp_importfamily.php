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

require_once 'PU_testcase_dcp_document.php';

class TestImportFamily extends TestCaseDcpDocument
{
    protected static $outputDir;
    /**
     * @dataProvider dataFamilyFiles
     */
    public function testErrorImportFamily($familyFile, $expectedError)
    {
        try {
            $this->importDocument($familyFile);
        }
        catch(\Exception $e) {
            $err = $e->getMessage();
        }
        $this->assertNotEmpty($err, "no import error detected");
        $this->assertContains($expectedError, $err, sprintf("not the correct error reporting"));
    }
    
    public function dataFamilyFiles()
    {
        return array(
            // test attribute too long
            array(
                "PU_data_dcp_badfamily1.ods",
                "AAAAAA"
            ) ,
            // test method not found
            array(
                "PU_data_dcp_badfamily2.ods",
                "Method.NotFound"
            )
        );
    }
}
?>