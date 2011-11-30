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
        $err = '';
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
    public function testSqlViewFamily($familyFile, $familyName, $testWorkflow = false)
    {
        $err = '';
        try {
            $this->importDocument($familyFile);
        }
        catch(\Exception $e) {
            $err = $e->getMessage();
        }
        $this->assertEmpty($err, "import error detected $err");
        $doc = createDoc("", $familyName);
        $this->assertTrue(is_object($doc));
        $err = $doc->store();
        $this->assertEmpty($err, "cannot create good doc");
        $id = $this->_DBGetValue(sprintf("select id from family.%s limit 1", strtolower($familyName)));
        
        $this->assertGreaterThan(1000, $id, "not found by view");
        if ($testWorkflow) {
            $wdoc = new_doc('', $doc->wid);
            $this->assertTrue($wdoc->isAlive() , "workflow not alive");
            $this->assertTrue(is_subclass_of($wdoc, "WDoc"));
        }
    }
    /**
     * @dataProvider dataBadUpdateFamilyFiles
     */
    public function testBadUpdateFamily($installFamilyFile, $updateFamilyFile, $expectedError)
    {
        //print "log:".ini_get("error_log").".\n";
        $this->importDocument($installFamilyFile);
        $err = '';
        try {
            $this->importDocument($updateFamilyFile);
        }
        catch(\Exception $e) {
            $err = $e->getMessage();
        }
        $this->assertNotEmpty($err, "no update error detected");
        $this->assertContains($expectedError, $err, sprintf("not the correct error reporting : %s", $err));
    }
    
    public function dataBadUpdateFamilyFiles()
    {
        return array(
            // test attribute too long
            array(
                "PU_data_dcp_initfamily1.ods",
                "PU_data_dcp_updatefamily1.ods",
                "TST_TITLE"
            ) ,
            array(
                "PU_data_dcp_initfamily2.ods",
                "PU_data_dcp_updatefamily2.ods",
                "TST_TITLE"
            )
        );
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
            ) ,
            // test workflow
            array(
                "PU_data_dcp_badfamily4.ods",
                "WTST_WFFAMIMP4"
            ) ,
            // test workflow class name syntax error
            array(
                "PU_data_dcp_badfamily5.ods",
                "WImpTest5"
            ) ,
            // test workflow without class
            array(
                "PU_data_dcp_badfamily6.ods",
                "WNotFound"
            ) ,
            // test workflow with a incorrect class name
            array(
                "PU_data_dcp_badfamily7.ods",
                "WTestBadNameImp7"
            ) ,
            // test workflow with class syntax error
            array(
                "PU_data_dcp_badfamily8.ods",
                "Parse error"
            ) ,
            // test workflow attrPrefix empty
            array(
                "PU_data_dcp_badfamily9.ods",
                "attrPrefix"
            ) ,
            // test workflow attrPrefix syntax
            array(
                "PU_data_dcp_badfamily10.ods",
                "syntax"
            )
        );
    }
    
    public function dataGoodFamilyFiles()
    {
        return array(
            // test attribute too long
            array(
                "PU_data_dcp_goodfamily1.ods",
                "TST_GOODFAMIMP1",
                false
            ) ,
            array(
                "PU_data_dcp_impworkflowfamily1.ods",
                "TST_WFFAMIMP1",
                true
            )
        );
    }
}
?>