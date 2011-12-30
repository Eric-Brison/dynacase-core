<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package DCP
*/

namespace PU;

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
     * test sql view create
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
                    "WFL0105",
                    "WTST_WFFAMIMP4"
                )
            ) ,
            // test workflow class name syntax error
            array(
                "PU_data_dcp_badfamily5.ods",
                array(
                    "WFL0002",
                    "WImpTest5"
                )
            ) ,
            // test workflow without class
            array(
                "PU_data_dcp_badfamily6.ods",
                array(
                    "WFL0005",
                    "WNotFound"
                )
            ) ,
            // test workflow with a incorrect class name
            array(
                "PU_data_dcp_badfamily7.ods",
                array(
                    "WFL0004",
                    "WTestBadNameImp7"
                )
            ) ,
            // test workflow with class syntax error
            array(
                "PU_data_dcp_badfamily8.ods",
                array(
                    "WFL0003",
                    "Parse error"
                )
            ) ,
            // test workflow attrPrefix empty
            array(
                "PU_data_dcp_badfamily9.ods",
                array(
                    "WFL0105",
                    "WFL0007",
                    "attrPrefix"
                )
            ) ,
            // test workflow attrPrefix syntax
            array(
                "PU_data_dcp_badfamily10.ods",
                array(
                    "WFL0008",
                    "syntax"
                ) ,
            ) ,
            // test workflow transition model definition
            array(
                "PU_data_dcp_badfamily11.ods",
                array(
                    "WFL0101",
                    "WFL0103",
                    "WFL0105",
                    "WFL0106",
                    "WFL0050"
                )
            ) ,
            // test workflow transition definition
            array(
                "PU_data_dcp_badfamily12.ods",
                array(
                    "WFL0050",
                    "WFL0201",
                    "WFL0202",
                    "WFL0052",
                    "WFL0107",
                    "dead or not",
                    " e3 "
                )
            ) ,
            // test inherit workflow class
            array(
                "PU_data_dcp_badfamily13.ods",
                array(
                    "WFL0006"
                )
            ) ,
            // test inherit workflow class
            array(
                "PU_data_dcp_badfamily14.ods",
                array(
                    "WFL0100",
                    "WFL0200",
                    "WFL0051"
                )
            ) ,
            // without classname
            array(
                "PU_data_dcp_badfamily15.ods",
                array(
                    "WFL0001"
                )
            ) ,
            // too many transition model
            array(
                "PU_data_dcp_badfamily16.ods",
                array(
                    "WFL0102"
                )
            ) ,
            // test ask unknow attribute
            array(
                "PU_data_dcp_badfamily17.ods",
                array(
                    "WFL0104"
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