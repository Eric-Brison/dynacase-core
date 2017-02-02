<?php
/*
 * @author Anakeen
 * @package Dcp\Pu
*/

namespace Dcp\Pu;

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
            // type modattr
            array(
                "PU_data_dcp_badmodattr1.ods",
                array(
                    "ATTR0604",
                    "TST_BADMODFAM_2",
                    "attr_int",
                    '"int"',
                    "TST_BADMODFAM_3",
                    "attr_date",
                    '"date"'
                )
            ) ,
            // enum redfinition
            array(
                "PU_data_dcp_badmodattr2.ods",
                array(
                    "ATTR0606",
                    "attr_enum_a",
                    "TST_BADMODFAM_4"
                )
            ) ,
            // undefined modattr
            array(
                "PU_data_dcp_badmodattr3.ods",
                array(
                    "ATTR0605",
                    "attr_undefined"
                )
            ) ,
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
                    "tst_orphan",
                    "ATTR0201",
                    "tst_invstruct",
                    "not defined",
                    "ATTR0202",
                    "tst_same",
                    "ATTR0206",
                    "none_tab"
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
            // test isTitle isAbstract isNeeded
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
                    "ATTR0900",
                    "tst_two",
                    "ATTR0901",
                    "tst_noneed"
                )
            ) ,
            // input help
            array(
                "PU_data_dcp_badattr7.ods",
                array(
                    "ATTR1100",
                    "tstNoHelp.php",
                    "ATTR1101",
                    "ATTR1200",
                    "ATTR1201",
                    "tst_nphelp",
                    "noParenthesis",
                    "ATTR1202",
                    "ATTR1203",
                    "testNoExistsReally",
                    "ATTR1209",
                    "is_a",
                    "ATTR1210",
                    "addLogMsg"
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
            ) ,
            // options syntax
            array(
                "PU_data_dcp_badattr9.ods",
                array(
                    "ATTR1250",
                    "tst_number1",
                    "ATTR1252",
                    "ATTR1251",
                    "tst_number2",
                    "bad Compute1",
                    "ATTR1400",
                    "tst_number3",
                    "ATTR1255",
                    "tst_number4"
                )
            ) ,
            // method control in the end
            array(
                "PU_data_dcp_badattr10.ods",
                array(
                    "ATTR1260",
                    "badCompute1",
                    "context : \"phpFunc\"",
                    "tst_number1",
                    "ATTR1261",
                    "tst_number2",
                    "goodCompute2",
                    "tst_number3",
                    "ATTR1401",
                    "tst_number4",
                    "ATTR1402",
                    "DFLT0004",
                    "context : \"Default value\"",
                    "DFLT0005",
                    "tst_notfound",
                    "badCall",
                    "ATTR1001",
                    "ATTR1002",
                    "goodCompute1",
                    "DFLT0006",
                    "notjson",
                    "INIT0005",
                    "tst_p2"
                )
            ) ,
            // method control in the end
            array(
                "PU_data_dcp_badattr11.ods",
                array(
                    "ATTR0203",
                    "tst_number2",
                    "ATTR0204",
                    "tst_number1",
                    "ATTR0205",
                    "tst_number4",
                    "ATTR0207",
                    "tst_frame2"
                )
            ) ,
            // static enum
            array(
                "PU_data_dcp_badattr12.ods",
                array(
                    "ATTR1270",
                    "tst_badeenum",
                    "tst_badnenum",
                    "ATTR1271",
                    "tst_badkenum",
                    "DFLT0001",
                    "tst syntax",
                    "DFLT0002",
                    "DFLT0003",
                    "ATTR1272",
                    "tst_bad_enum_empty_key"
                )
            ) ,
            // format string
            array(
                "PU_data_dcp_badattr13.ods",
                array(
                    "ATTR0603",
                    "tst_badformat2"
                )
            ) ,
            // format string
            array(
                "PU_data_dcp_badattr14.ods",
                array(
                    "ATTR1701"
                )
            ) ,
            // PARAM frame errors
            array(
                "PU_data_dcp_badattr15.ods",
                array(
                    "ATTR0208",
                    "tst_number2",
                    "ATTR0209",
                    "tst_number1",
                    "ATTR0210",
                    "tst_number4",
                    "tst_frame2",
                    "ATTR0903",
                    "tst_colneed"
                )
            ) ,
            // PARAM phpfunc errors
            array(
                "PU_data_dcp_badattr16.ods",
                array(
                    "ATTR0211",
                    "tst_docid"
                )
            ) ,
            array(
                "PU_data_dcp_badattr17.ods",
                array(
                    "ATTR0212",
                    "tst_errorder",
                    "one"
                )
            ) ,
            array(
                "PU_data_dcp_badattr18.ods",
                array(
                    "ATTR0213",
                    "tst_t3",
                    "tst_frame1",
                    "tst_frame2"
                )
            ),
            array(
                "PU_data_dcp_badattr19.ods",
                array(
                    "ATTR0214",
                    "loop_a5"
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
            ) , // with method file
            array(
                "PU_data_dcp_goodfamily2.ods",
                "TST_GOODFAMIMP2",
                false
            ) , // with class file
            array(
                "PU_data_dcp_goodfamily4.ods",
                "TST_GOODFAMIMP4",
                false
            ) ,
            // Family update with PARAM + INITIAL value
            array(
                "PU_data_dcp_goodfamily5.ods",
                "TST_GOODFAMIMP5",
                false
            ) ,
            // Family update with PARAM + INITIAL value
            array(
                "PU_data_dcp_goodfamily6.ods",
                "TST_GOODFAMIMP6",
                false
            )
        );
    }
}
