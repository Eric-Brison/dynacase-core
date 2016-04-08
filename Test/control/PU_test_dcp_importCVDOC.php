<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Pu;
/**
 * @author Anakeen
 * @package Dcp\Pu
 */

require_once 'PU_testcase_dcp_commonfamily.php';

class TestImportCVDOC extends TestCaseDcpCommonFamily
{
    /**
     * @dataProvider dataBadCVDOC
     */
    public function testErrorImportCVDOC($familyFile, array $expectedErrors)
    {
        $this->requiresCoreParamEquals('CORE_LANG', 'fr_FR');
        
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
    
    public function dataBadCVDOC()
    {
        return array(
            array(
                "file" => "PU_data_dcp_importcvdocbad1.ods",
                "errors" => array(
                    "DOC0111",
                    "[Identifiant de la vue]",
                    "DOC0111",
                    "[Label]"
                ) ,
            )
        );
    }
}
