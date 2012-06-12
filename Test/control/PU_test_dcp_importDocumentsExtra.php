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

require_once 'PU_testcase_dcp_commonfamily.php';

class TestImportDocumentsExtra extends TestCaseDcpCommonFamily
{
    /**
     * @param string $file file to import
     * @param array $datas Array of data
     * @return void
     @dataProvider dataTestImportDocumentExtra
     */
    public function testDoImportDocumentsExtra($file, array $datas)
    {
        $err = "";
        try {
            $this->importDocument($file);
        }
        catch(\Exception $e) {
            $err = $e->getMessage();
        }
        $this->assertEmpty($err, "import error detected $err");
        foreach ($datas as $data) {
            $docName = $data[0];
            $stateExpected = $data[1];
            $doc = new_Doc(self::$dbaccess, $docName);
            $this->assertTrue($doc->isAlive() , sprintf("cannot find doc [%s]", $docName));
            $stateFound = json_decode($doc->getValue("test_extra") , true);
            $this->assertEquals($stateExpected, $stateFound, sprintf("State found : [%s] is not the same as state expected: [%s]", $stateFound["state"], $stateExpected["state"]));
        }
    }
    
    public function dataTestImportDocumentExtra()
    {
        return array(
            array(
                "PU_data_dcp_impworkflowextra.ods",
                array(
                    array(
                        "TEST_EXTRA_DEFAULT1",
                        array(
                            "state" => "alive",
                            "num" => "1"
                        )
                    ) ,
                    array(
                        "TEST_EXTRA_DEFAULT2",
                        array(
                            "state" => "sick",
                            "num" => "2"
                        )
                    )
                )
            )
        );
    }
}
