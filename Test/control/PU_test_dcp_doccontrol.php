<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package DCP
*/

namespace PU;

require_once 'PU_testcase_dcp_document.php';

class TestDocControl extends TestCaseDcpDocument
{
    /**
     * Test ->control() method on HELPAGE family
     * @param array $data test specification
     * @dataProvider dataHelpPage
     */
    public function testHelpPage($data)
    {
        $myAction = self::getAction();
        
        if (isset($data['import'])) {
            if (!is_array($data['import'])) {
                $data['import'] = array(
                    $data['import']
                );
            }
            foreach ($data['import'] as $import) {
                $this->importDocument($import);
            }
        }
        
        if (!isset($data['control']) || !is_array($data['control'])) {
            return;
        }
        
        foreach ($data['control'] as $controlIdx => & $control) {
            $user = new_Doc(self::$dbaccess, $control['user']);
            $this->assertTrue($user->isAlive() , sprintf("control#%s> Could not get user with id '%s'.", $controlIdx, $control['user']));
            
            $login = $user->getValue('us_login');
            $this->assertNotEmpty($login, sprintf("control#%s> User with id '%s' have an empty login.", $controlIdx, $control['user']));
            
            $this->sudo($login);
            
            $doc = new_Doc(self::$dbaccess, $control['doc']);
            $this->assertTrue($doc->isAlive() , sprintf("control#%s> Could not get document with id '%s'.", $controlIdx, $control['doc']));
            
            $permission = $doc->control($control['acl']);
            if ($control['result'] === false) {
                $this->assertNotEmpty($permission, sprintf("control#%s> Unexpected empty control value while expecting a non-empty value.", $controlIdx));
            } else {
                $this->assertEmpty($permission, sprintf("control#%s> Unexpected control value '%s' while expecting empty value.", $controlIdx, $permission));
            }
            
            $this->exitSudo();
        }
        unset($control);
    }
    
    public function dataHelpPage()
    {
        return array(
            array(
                /* 0 */
                array(
                    "import" => array(
                        "PU_data_dcp_doccontrol_users.ods",
                        "PU_data_dcp_doccontrol_profiles.ods",
                        "PU_data_dcp_doccontrol_families.ods",
                        "PU_data_dcp_doccontrol_docs.ods"
                    ) ,
                    "control" => array(
                        array(
                            "user" => "TST_U_HOMER_SIMPSON",
                            "doc" => "TST_HELPPAGE_01",
                            "acl" => "view",
                            "result" => false
                        ) ,
                        array(
                            "user" => "TST_U_HOMER_SIMPSON",
                            "doc" => "TST_HELPPAGE_01",
                            "acl" => "edit",
                            "result" => false
                        ) ,
                        array(
                            "user" => "TST_U_MARGE_SIMPSON",
                            "doc" => "TST_HELPPAGE_01",
                            "acl" => "view",
                            "result" => true
                        ) ,
                        array(
                            "user" => "TST_U_MARGE_SIMPSON",
                            "doc" => "TST_HELPPAGE_01",
                            "acl" => "edit",
                            "result" => false
                        )
                    )
                )
            )
        );
    }
}
?>