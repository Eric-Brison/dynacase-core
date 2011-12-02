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
                array(
                    "FA002",
                    "AAAAAA"
                )
            ) ,
            // test method not found
            array(
                "PU_data_dcp_badfamily2.ods",
                array(
                    "Method.NotFound"
                )
            ) ,
            // test order needed
            array(
                "PU_data_dcp_badfamily3.ods",
                array(
                    "FA003",
                    "FA006",
                    "FA004",
                    "TST_ARRAY",
                    "TST_TITLE",
                    "W3"
                )
            ) ,
            // test workflow
            array(
                "PU_data_dcp_badfamily4.ods",
                array(
                    "W0008",
                    "WTST_WFFAMIMP4"
                )
            ) ,
            // test workflow class name syntax error
            array(
                "PU_data_dcp_badfamily5.ods",
                array(
                    "W0017",
                    "WImpTest5"
                )
            ) ,
            // test workflow without class
            array(
                "PU_data_dcp_badfamily6.ods",
                array(
                    "W0018",
                    "WNotFound"
                )
            ) ,
            // test workflow with a incorrect class name
            array(
                "PU_data_dcp_badfamily7.ods",
                array(
                    "W0013",
                    "WTestBadNameImp7"
                )
            ) ,
            // test workflow with class syntax error
            array(
                "PU_data_dcp_badfamily8.ods",
                array(
                    "W0012",
                    "Parse error"
                )
            ) ,
            // test workflow attrPrefix empty
            array(
                "PU_data_dcp_badfamily9.ods",
                array(
                    "W0008",
                    "W0014",
                    "attrPrefix"
                )
            ) ,
            // test workflow attrPrefix syntax
            array(
                "PU_data_dcp_badfamily10.ods",
                array(
                    "W0015",
                    "syntax"
                ) ,
            ) ,
            // test workflow transition model definition
            array(
                "PU_data_dcp_badfamily11.ods",
                array(
                    "W0006",
                    "W0007",
                    "W0008",
                    "W0009",
                    "W0011"
                )
            ) ,
            // test workflow transition definition
            array(
                "PU_data_dcp_badfamily12.ods",
                array(
                    "W0011",
                    "W0002",
                    "W0003",
                    "dead or not",
                    " e3 "
                )
            )
        );
    }
    
    public function dataGoodFamilyFiles()
    {
        return array(
            // test simple family
            array(
                "PU_data_dcp_goodfamily1.ods",
                "TST_GOODFAMIMP1",
                false
            ) ,
            // test workflow family
            array(
                "PU_data_dcp_impworkflowfamily1.ods",
                "TST_WFFAMIMP1",
                true
            )
        );
    }
}
?>