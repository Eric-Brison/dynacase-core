<?php
/*
 * @author Anakeen
 * @package Dcp\Pu
*/

namespace Dcp\Pu;

require_once 'PU_testcase_dcp_application.php';

class TestAppInheritAcl extends TestCaseDcpApplication
{
    public static function appConfig()
    {
        return array(
            array(
                "appRoot" => join(DIRECTORY_SEPARATOR, array(
                    DEFAULT_PUBDIR,
                    "DCPTEST",
                    "app"
                )) ,
                "appName" => "INHERIT_ACL_A",
            ) ,
            array(
                "appRoot" => join(DIRECTORY_SEPARATOR, array(
                    DEFAULT_PUBDIR,
                    "DCPTEST",
                    "app"
                )) ,
                "appName" => "INHERIT_ACL_B",
                "import" => array(
                    "PU_data_dcp_app_inherit_acl.ods"
                )
            )
        );
    }
    /**
     * Test applications inherited ACLs
     * @param string $appName The application name
     * @param string $aclName The ACL name
     * @param string $userId The user id or logical name
     * @param bool $expectedPermission The expected permission (true or false)
     * @return void
     * @dataProvider dataInheritedAcl
     */
    public function testInheritedAcl($appName, $aclName, $userId, $expectedPermission)
    {
        $myAction = self::getAction();
        
        $user = new_doc(self::$dbaccess, $userId);
        $this->assertTrue($user->isAlive() , sprintf("Could not get user with id '%s'.", $userId));
        
        $wuser = new \User(self::$dbaccess, $user->getRawValue('us_whatid'));
        $this->assertTrue(is_numeric($wuser->id) , sprintf("Invalid user what id '%s' for user '%s'.", $wuser->id, $userId));
        
        $this->sudo($wuser->login);
        
        $perm = $myAction->hasPermission($aclName, $appName);
        $this->assertTrue($perm === $expectedPermission, sprintf("Unexpected permission '%s' while expecting '%s' for user '%s', ACL '%' and application '%s'.", ($perm ? 'true' : 'false') , ($expectedPermission ? 'true' : 'false') , $userId, $aclName, $appName));
        
        $this->exitSudo();
    }
    public static function dataInheritedAcl()
    {
        return array(
            array(
                "INHERIT_ACL_A",
                "ACL_A_1",
                "TST_U_INHERIT_ACL_1",
                true
            ) ,
            array(
                "INHERIT_ACL_A",
                "ACL_A_2",
                "TST_U_INHERIT_ACL_1",
                false
            ) ,
            array(
                "INHERIT_ACL_B",
                "ACL_A_1",
                "TST_U_INHERIT_ACL_1",
                true
            ) ,
            array(
                "INHERIT_ACL_B",
                "ACL_A_2",
                "TST_U_INHERIT_ACL_1",
                false
            )
        );
    }
}
