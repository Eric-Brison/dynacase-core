<?php
/*
 * @author Anakeen
 * @package Dcp\Pu
*/

namespace Dcp\Pu;

require_once 'PU_testcase_dcp_application.php';

class TestOpenAccess extends TestCaseDcpApplication
{
    public static function appConfig()
    {
        return array(
            "appRoot" => join(DIRECTORY_SEPARATOR, array(
                DEFAULT_PUBDIR,
                "DCPTEST",
                "app"
            )) ,
            "appName" => "TST_OPENACCESS",
            "import" => array(
                "PU_data_dcp_openaccess.ods"
            ) ,
        );
    }
    /**
     * Test ACCESS on application/action
     * @param array $data test specification
     * @return void
     * @dataProvider dataApplicationToken
     */
    public function testApplicationToken($login, $token, $actionName, $expectedGrant, $error)
    {
        $user = new \Account();
        $user->setLoginName($login);
        $this->assertTrue($user->isAffected() , sprintf("Login %s not found", $login));
        $tokenId = $user->getUserToken($token["expire"], $token["oneshot"], $token["context"], "Tst");
        $this->assertNotEmpty($tokenId, "Token is empty");
        
        $userToken = new \UserToken("", $tokenId);
        //$mainAction = self::getAction();
        global $action;
        $auth = new \openAuthenticator("open", "freedom");
        
        $this->sudo($login);
        
        $mainAction = new \Action();
        $mainAction->set($actionName, self::$app);
        $mainAction->user = $user;
        $mainAction->auth = $auth;
        $mainAction->parent->permission = null;
        setHttpVar("app", $mainAction->parent->name);
        setHttpVar("action", $mainAction->name);
        
        $granted = $auth::verifyOpenAccess($userToken);
        $notExpired = $auth::verifyOpenExpire($userToken);
        
        $this->assertEquals($granted && $notExpired, $expectedGrant, "Wrong open access");
        
        try {
            $out = $mainAction->execute();
            if ($error) {
                $this->assertContains($error, "", "Error must be occur");
            }
        }
        catch(\Exception $e) {
            if ($error) {
                if (is_array($error)) {
                    foreach ($error as $err) {
                        $this->assertContains($err, $e->getMessage() , "Incorrect error");
                    }
                } else {
                    $this->assertContains($error, $e->getMessage() , "Incorrect error");
                }
            } else {
                $this->assertEmpty($e->getMessage() , sprintf("Access must be granted : %s", $e->getMessage()));
            }
        }
        $this->exitSudo();
    }
    
    public function dataApplicationToken()
    {
        return array(
            array(
                "login" => "john.doe1",
                "token" => ["expire" => 200,
                "oneshot" => true,
                "context" => ["app" => "TST_OPENACCESS"]],
                "action" => "TST_OPENACCESS_ACTION_1",
                "grant" => true,
                "error" => "CORE0012"
            ) ,
            
            array(
                "login" => "jane.doe1",
                "token" => ["expire" => 200,
                "oneshot" => true,
                "context" => ["app" => "TST_OPENACCESS"]],
                "action" => "TST_OPENACCESS_ACTION_2",
                "grant" => true,
                "error" => "CORE0012"
            ) ,
            
            array(
                "login" => "admin",
                "token" => ["expire" => 200,
                "oneshot" => true,
                "context" => ["app" => "TST_OPENACCESS"]],
                "action" => "TST_OPENACCESS_ACTION_1",
                "grant" => true,
                "error" => "CORE0012"
            ) ,
            array(
                "login" => "john.doe1",
                "token" => ["expire" => 200,
                "oneshot" => true,
                "context" => ["app" => "TST_OPENACCESS"]],
                "action" => "TST_OPENACCESS_ACTION_OPEN1",
                "grant" => true,
                "error" => ["CORE0006",
                "TST_JANE_ACL"]
            ) ,
            array(
                "login" => "jane.doe1",
                "token" => ["expire" => 200,
                "oneshot" => true,
                "context" => ["app" => "CORE"]],
                "action" => "TST_OPENACCESS_ACTION_OPEN1",
                "grant" => false,
                "error" => ""
            ) ,
            array(
                "login" => "jane.doe1",
                "token" => ["expire" => 200,
                "oneshot" => true,
                "context" => ["app" => "TST_OPENACCESS"]],
                "action" => "TST_OPENACCESS_ACTION_OPEN1",
                "grant" => true,
                "error" => ""
            ) ,
            array(
                "login" => "jane.doe1",
                "token" => ["expire" => 200,
                "oneshot" => true,
                "context" => ["app" => "TST_OPENACCESS",
                "action" => "TST_OPENACCESS_ACTION_OPEN1"]],
                "action" => "TST_OPENACCESS_ACTION_OPEN1",
                "grant" => true,
                "error" => ""
            ) ,
            array(
                "login" => "john.doe1",
                "token" => ["expire" => 200,
                "oneshot" => true,
                "context" => ["app" => "TST_OPENACCESS",
                "action" => "TST_OPENACCESS_ACTION_OPEN2"]],
                "action" => "TST_OPENACCESS_ACTION_OPEN2",
                "grant" => true,
                "error" => ""
            ) ,
            array(
                "login" => "john.doe1",
                "token" => ["expire" => 200,
                "oneshot" => true,
                "context" => ["app" => "TST_OPENACCESS"]],
                "action" => "TST_OPENACCESS_ACTION_OPEN2",
                "grant" => true,
                "error" => ""
            ) ,
            array(
                "login" => "john.doe1",
                "token" => ["expire" => 200,
                "oneshot" => true,
                "context" => []],
                "action" => "TST_OPENACCESS_ACTION_OPEN2",
                "grant" => true,
                "error" => ""
            ) ,
            array(
                "login" => "jane.doe1",
                "token" => ["expire" => 200,
                "oneshot" => true,
                "context" => []],
                "action" => "TST_OPENACCESS_ACTION_OPEN1",
                "grant" => true,
                "error" => ""
            ) ,
            array(
                "login" => "jane.doe1",
                "token" => ["expire" => 200,
                "oneshot" => true,
                "context" => ["app" => "TST_OPENACCESS",
                "action" => "TST_OPENACCESS_ACTION_OPEN2"]],
                "action" => "TST_OPENACCESS_ACTION_OPEN1",
                "grant" => false,
                "error" => ""
            ) ,
            array(
                "login" => "john.doe1",
                "token" => ["expire" => 200,
                "oneshot" => true,
                "context" => ["app" => "TST_OPENACCESS",
                "action" => "TST_OPENACCESS_ACTION_OPEN1"]],
                "action" => "TST_OPENACCESS_ACTION_OPEN1",
                "grant" => true,
                "error" => ["CORE0006",
                "TST_JANE_ACL"]
            ) ,
            array(
                "login" => "john.doe1",
                "token" => ["expire" => 200,
                "oneshot" => true,
                "context" => ["app" => "TST_OPENACCESS",
                "action" => "TST_OPENACCESS_ACTION_OPEN2"]],
                "action" => "TST_OPENACCESS_ACTION_OPEN1",
                "grant" => false,
                "error" => ["CORE0006",
                "TST_JANE_ACL"]
            ) ,
            array(
                "login" => "jane.doe1",
                "token" => ["expire" => - 200,
                "oneshot" => true,
                "context" => []],
                "action" => "TST_OPENACCESS_ACTION_OPEN1",
                "grant" => false,
                "error" => ""
            ) ,
        );
    }
}
