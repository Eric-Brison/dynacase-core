<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package DCP
*/

namespace PU;

require_once 'PU_testcase_dcp_document.php';

class TestImportAccess extends TestCaseDcpDocument
{
    protected static $outputDir;
    /**
     * @dataProvider dataBadFamilyFiles
     */
    public function testErrorImportAccess($familyFile, $expectedErrors)
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
            $this->assertContains($expectedError, $err, sprintf("access : not the correct error reporting : %s", $err));
        }
    }
    /**
     * @dataProvider dataGoodFamilyFiles
     */
    public function testImportAccess($familyFile, $userid, $appName, array $acls)
    {
        $err = '';
        try {
            $this->importDocument($familyFile);
        }
        catch(\Exception $e) {
            $err = $e->getMessage();
        }
        $this->assertEmpty($err, sprintf("access error detected %s", $err));
        $this->sudo($userid);
        foreach ($acls as $acl) {
            $hasPriv = $this->getAction()->HasPermission($acl, $appName);
            $this->assertTrue($hasPriv, sprintf("access error privilege"));
        }
        $this->exitSudo();
    }
    
    public function dataBadFamilyFiles()
    {
        return array(
            // test unknow profid
            array(
                "PU_data_dcp_badaccess1.ods",
                array(
                    "ACCS0001",
                    "TSTAPPUNKNOW"
                )
            ) ,
            // test unknow acl
            array(
                "PU_data_dcp_badaccess2.ods",
                array(
                    "ACCS0002",
                    "USER2",
                    "ACCS0004",
                    "acl test",
                    "ACCS0004",
                    "acl test",
                    "ACCS0003",
                    "GTST_UNKNOW",
                    "ACCS0007",
                    "ACCS0005",
                    "not an application",
                    "ACCS0006"
                )
            )
        );
    }
    
    public function dataGoodFamilyFiles()
    {
        return array(
            // test access
            array(
                "PU_data_dcp_goodaccess1.ods",
                "GADMIN",
                "APPMNG",
                array(
                    "USER",
                    "ADMIN"
                )
            )
        );
    }
}
?>