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
    public function testSqlViewFamily($familyFile, $familyName)
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
                "PU_data_dcp_badattr1.ods",
                array(
                    "ATTR0100",
                    "aaaaaa",
                    "ATTR0102",
                    "tst number",
                    "ATTR0101",
                    "select",
                    "ATTR0103",
                    "title",
                    "ATTR0200",
                    "tst_orphean",
                    "ATTR0201",
                    "tst_invstruct",
                    "not defined",
                    "ATTR0202",
                    "tst_same"
                )
            ) ,
            // test method not found
            array(
                "PU_data_dcp_badattr2.ods",
                array(
                    "Method.NotFound"
                )
            ) ,
            // test order needed
            array(
                "PU_data_dcp_badattr3.ods",
                array(
                    "ATTR0700",
                    "tst_noorder",
                    "ATTR0702",
                    "tst_errorder"
                )
            ) ,
            // test type
            array(
                "PU_data_dcp_badattr4.ods",
                array(
                    "ATTR0600",
                    "tst_typetest",
                    "ATTR0601",
                    "ATTR0602",
                    "tst_errtype",
                    "tst_notype"
                )
            ) ,
            // test visibility
            array(
                "PU_data_dcp_badattr5.ods",
                array(
                    "ATTR0800",
                    "tst_novis",
                    "ATTR0801",
                    "tst_errvis",
                    "ZS",
                    "ATTR0802",
                    "tst_noarray"
                )
            ) ,
            // test isTitle isAbstract
            array(
                "PU_data_dcp_badattr6.ods",
                array(
                    "ATTR0500",
                    "tst_noabstract",
                    "ATTR0501",
                    "tst_t_abstract",
                    "ATTR0400",
                    "tst_notitle",
                    "ATTR0401",
                    "tst_t_title",
                )
            ) ,
            // input help
            array(
                "PU_data_dcp_badattr7.ods",
                array(
                    "ATTR1100",
                    "tstNoHelp.php",
                    "ATTR1101"
                )
            ) ,
            // options syntax
            array(
                "PU_data_dcp_badattr8.ods",
                array(
                    "ATTR1500",
                    "optionerror",
                    "ATTR1501",
                    "wrong error"
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
            )
        );
    }
}
?>