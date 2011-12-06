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

class TestUser extends TestCaseDcpDocument
{
    /**
     * @dataProvider dataUserCreate
     */
    public function testCreateUser($login, $password)
    {
        
        $doc = createDoc(self::$dbaccess, "IUSER");
        $this->assertTrue(is_object($doc) , "cannot create user");
        $err = $doc->setValue("us_login", $login);
        $err.= $doc->setValue("us_passwd1", $password);
        $err.= $doc->setValue("us_passwd2", $password);
        $this->assertEmpty($err, sprintf("cannot set iuser %s", $err));
        
        $err = $doc->store();
        $this->assertEmpty($err, sprintf("cannot store iuser %s", $err));
        
        $u = new \User();
        $this->assertTrue($u->setLoginName($login) , "system user not found");
        $this->assertEquals($login, $u->login);
        $this->assertEquals($doc->id, $u->fid, "mismatch document iuser reference");
        $this->assertEquals($doc->getValue("us_whatid") , $u->id, "mismatch system iuser reference");
    }
    /**
     * @dataProvider dataNotUserCreate
     */
    public function testNotCreateUser($login, $password)
    {
        
        $doc = createDoc(self::$dbaccess, "IUSER");
        $this->assertTrue(is_object($doc) , "cannot create user");
        $err = $doc->setValue("us_login", $login);
        $err.= $doc->setValue("us_passwd1", $password);
        $err.= $doc->setValue("us_passwd2", $password);
        $this->assertEmpty($err, sprintf("cannot set iuser %s", $err));
        
        $err = $doc->store();
        $this->assertNotEmpty($err, sprintf("must be impossible to store iuser"));
        
        $u = new \User();
        $this->assertTrue($u->setLoginName($login) , "system user not found");
        $this->assertEquals($login, $u->login);
    }
    
    public function dataUserCreate()
    {
        return array(
            array(
                "joe",
                "secret"
            ) ,
            array(
                "joe2",
                "secret"
            )
        );
    }
    public function dataNotUserCreate()
    {
        return array(
            array(
                "admin",
                "secret"
            ) ,
            array(
                "anonymous",
                "secret"
            )
        );
    }
}
?>