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
     * @dataProvider dataBadFamilyFiles
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
        $this->assertContains($expectedError, $err, sprintf("not the correct error reporting : %s", $err));
    }
    /**
     * @dataProvider dataGoodFamilyFiles
     */
    public function testSqlViewFamily($familyFile)
    {
        $err = '';
        try {
            $this->importDocument($familyFile);
        }
        catch(\Exception $e) {
            $err = $e->getMessage();
        }
        $this->assertEmpty($err, "import error detected $err");
        $doc = createDoc("", "TST_GOODFAMIMP1");
        $this->assertTrue(is_object($doc));
        $err = $doc->store();
        $this->assertEmpty($err, "cannot create good doc");
        $id = $this->_DBGetValue("select id from family.tst_goodfamimp1 limit 1");
        
        $this->assertGreaterThan(1000, $id, "not found by view");
    }


    /**
     * @dataProvider dataBadUpdateFamilyFiles
     */
    public function testBadUpdateFamily($installFamilyFile, $updateFamilyFile, $expectedError)
    {
        $this->importDocument($installFamilyFile);
        try {
            $this->importDocument($updateFamilyFile);
        }
        catch(\Exception $e) {
            $err = $e->getMessage();
        }
        $this->assertNotEmpty($err, "no update error detected");
        $this->assertContains($expectedError, $err, sprintf("not the correct error reporting : %s", $err));
    }

    public function dataBadUpdateFamilyFiles() {
        return array(
            // test attribute too long
            array(
                "PU_data_dcp_initfamily1.ods",
                "PU_data_dcp_updatefamily1.ods",
                "TST_TITLE"
            ),array(
                "PU_data_dcp_initfamily2.ods",
                "PU_data_dcp_updatefamily2.ods",
                "TST_TITLE"
            ),array(
                "PU_data_dcp_initfamily2.ods",
                "PU_data_dcp_updatefamily2.ods",
                "TST_PARAM"
            ));
    }
    public function dataBadFamilyFiles()
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
            ) ,
            // test order needed
            array(
                "PU_data_dcp_badfamily3.ods",
                "TST_ARRAY"
            ) ,
            // test order needed
            array(
                "PU_data_dcp_badfamily3.ods",
                "TST_TITLE"
            ) ,
            // test visibility
            array(
                "PU_data_dcp_badfamily3.ods",
                "W3"
            )
        );
    }
    
    public function dataGoodFamilyFiles()
    {
        return array(
            // test attribute too long
            array(
                "PU_data_dcp_goodfamily1.ods"
            )
        );
    }
}
?>