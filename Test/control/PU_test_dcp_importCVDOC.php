<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Dcp\Pu;
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
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
                    "[id vues]",
                    "DOC0111",
                    "[label]"
                ) ,
            )
        );
    }
}
