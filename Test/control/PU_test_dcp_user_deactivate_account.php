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
        // the $effectiveUserLogin cannot do this operation
        $err = $user->deactivateAccount();
        $this->assertNotEmpty($err, "deactivate error must be detected");
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
        // the $effectiveUserLogin cannot do this operation
        $err = $user->activateAccount();
        
        $this->assertNotEmpty($err, "activate error must be detected");
        $this->assertFalse($user->isAccountActive() , sprintf("User with id '%s' should not be active.", $targetUserId));
        
        $this->exitSudo();
    }
    /**
     * @dataProvider dataDeActivateAccount
     */
    public function testDeActivateAccount($targetUserId)
    {
        /**
         * @var \_IUSER $user
         */
        $user = new_Doc(self::$dbaccess, $targetUserId, true);
        $this->assertTrue($user->isAlive() , sprintf("Could not get user with id '%s'.", $targetUserId));
        $err = $user->deactivateAccount();
        
        $this->assertEmpty($err, "activate error detected : $err");
        $this->assertFalse($user->isAccountActive() , sprintf("User with id '%s' should not be active.", $targetUserId));
        $this->assertTrue($user->isAccountInActive() , sprintf("User with id '%s' should not be active.", $targetUserId));
        $err = $user->activateAccount();
        
        $this->assertEmpty($err, "activate error detected : $err");
        $this->assertTrue($user->isAccountActive() , sprintf("User with id '%s' should not be active.", $targetUserId));
        $this->assertFalse($user->isAccountInActive() , sprintf("User with id '%s' should not be active.", $targetUserId));
    }
    
    public function dataUserDeactivateAccount()
    {
        return array(
            array(
                "U_1",
                "U_2"
            )
        );
    }
    
    public function dataDeActivateAccount()
    {
        return array(
            array(
                "U_1"
            ) ,
            array(
                "U_2"
            )
        );
    }
    public function dataUserActivateAccount()
    {
        return array(
            array(
                "U_1",
                "U_3"
            )
        );
    }
}
?>