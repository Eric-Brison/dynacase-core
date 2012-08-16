<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package Dcp\Pu
*/

namespace Dcp\Pu;

require_once 'PU_testcase_dcp_document.php';

class TestImportWorkflow extends TestCaseDcpDocument
{
    protected static $outputDir;
    /**
     * @dataProvider dataBadFamilyFiles
     */
    public function testErrorImportWorkflow($familyFile, $expectedErrors)
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
     * test import workflow
     * @dataProvider dataGoodFamilyFiles
     */
    public function testExecuteImportWorkflow($familyFile, $familyName)
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
        
        $wdoc = new_doc('', $doc->wid);
        $this->assertTrue($wdoc->isAlive() , "workflow not alive");
        $this->assertTrue(is_subclass_of($wdoc, "WDoc"));
    }
    
    public function dataBadFamilyFiles()
    {
        return array(
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
                    "unknowM1",
                    "WFL0106",
                    "unknowM2",
                    "WFL0108",
                    "unknowM0",
                    "WFL0109",
                    "unknowM3",
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