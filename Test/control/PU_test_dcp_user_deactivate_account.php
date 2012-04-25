<?php
/*
 * @author anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace PU;
/**
 * @author anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package DCP
 */
require_once 'PU_testcase_dcp_document.php';

class TestUserDeactivateAccount extends TestCaseDcpCommonFamily
{
    protected static function getCommonImportFile()
    {
        return array(
            'PU_data_dcp_user_deactivate_account.ods'
        );
    }
    /**
     * @dataProvider dataUserDeactivateAccount
     */
    public function testExecuteUserDeactivateAccount($effectiveUserLogin, $targetUserId)
    {
        $this->sudo($effectiveUserLogin);
        /**
         * @var \_IUSER $user
         */
        $user = new_Doc(self::$dbaccess, $targetUserId, true);
        $this->assertTrue($user->isAlive() , sprintf("Could not get user with id '%s'.", $targetUserId));
        
        $user->deactivateAccount();
        
        $this->assertTrue($user->isAccountActive() , sprintf("User with id '%s' should be active.", $targetUserId));
        
        $this->exitSudo();
    }
    /**
     * @dataProvider dataUserActivateAccount
     */
    public function testUserActivateAccount($effectiveUserLogin, $targetUserId)
    {
        $this->sudo($effectiveUserLogin);
        /**
         * @var \_IUSER $user
         */
        $user = new_Doc(self::$dbaccess, $targetUserId, true);
        $this->assertTrue($user->isAlive() , sprintf("Could not get user with id '%s'.", $targetUserId));
        
        $user->activateAccount();
        
        $this->assertFalse($user->isAccountActive() , sprintf("User with id '%s' should not be active.", $targetUserId));
        
        $this->exitSudo();
    }
    
    public function dataUserDeactivateAccount()
    {
        return array(
            array(
                "u_1",
                "U_2"
            )
        );
    }
    
    public function dataUserActivateAccount()
    {
        return array(
            array(
                "u_1",
                "U_3"
            )
        );
    }
}
?>