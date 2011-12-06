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

class TestGroup extends TestCaseDcpDocument
{
    /**
     * @dataProvider datagroupCreate
     */
    public function testCreateGroup($login)
    {
        
        $doc = createDoc(self::$dbaccess, "IGROUP");
        $this->assertTrue(is_object($doc) , "cannot create group");
        $err = $doc->setValue("us_login", $login);
        $this->assertEmpty($err, sprintf("cannot set igroup %s", $err));
        
        $err = $doc->store();
        $this->assertEmpty($err, sprintf("cannot store igroup %s", $err));
        
        $u = new \User();
        $this->assertTrue($u->setLoginName($login) , "system group not found");
        $this->assertEquals($login, $u->login);
        $this->assertEquals($doc->id, $u->fid, "mismatch document igroup reference");
        $this->assertEquals($doc->getValue("us_whatid") , $u->id, "mismatch system igroup reference");
    }
    /**
     * @param string $userLogin
     * @param array $groupLogins
     * @---depends testCreateGroup
     * @dataProvider dataInsertgroupCreate
     */
    public function testInsertInGroup($userLogin, array $groupLogins)
    {
        // create user
        $user = createDoc(self::$dbaccess, "IUSER");
        $this->assertTrue(is_object($user) , "cannot create user");
        $err = $user->setValue("us_login", $userLogin);
        $password = 'secret';
        $err.= $user->setValue("us_passwd1", $password);
        $err.= $user->setValue("us_passwd2", $password);
        $this->assertEmpty($err, sprintf("cannot set iuser %s", $err));
        
        $err = $user->store();
        $this->assertEmpty($err, sprintf("cannot store iuser %s", $err));
        // create group
        $newGids = array();
        foreach ($groupLogins as $gLogin) {
            /**
             * @var \_IGROUP $group
             */
            $group = createDoc(self::$dbaccess, "IGROUP");
            $this->assertTrue(is_object($group) , "cannot create group");
            $err = $group->setValue("us_login", $gLogin);
            $this->assertEmpty($err, sprintf("cannot set igroup %s", $err));
            
            $err = $group->store();
            $this->assertEmpty($err, sprintf("cannot store igroup %s", $err));
            $group->addFile($user->initid);
            $newGids[] = $group->getValue("us_whatid");
        }
        $u = new \User("", $user->getValue("us_whatid"));
        $this->assertTrue($u->isAffected());
        $gids = $u->getGroupsId();
        
        $this->assertEmpty(array_diff($newGids, $gids) , "groups are not in new user");
    }
    /**
     * @dataProvider dataNotgroupCreate
     */
    public function testNotCreateGroup($login)
    {
        
        $doc = createDoc(self::$dbaccess, "IGROUP");
        $this->assertTrue(is_object($doc) , "cannot create group");
        $err = $doc->setValue("us_login", $login);
        $this->assertEmpty($err, sprintf("cannot set igroup %s", $err));
        
        $err = $doc->store();
        $this->assertNotEmpty($err, sprintf("must be impossible to store igroup"));
        
        $u = new \User();
        $this->assertTrue($u->setLoginName($login) , "system group not found");
        $this->assertEquals($login, $u->login);
    }
    
    public function datagroupCreate()
    {
        return array(
            array(
                "patissier"
            ) ,
            array(
                "menuisier"
            )
        );
    }
    
    public function dataInsertgroupCreate()
    {
        return array(
            array(
                "john.doc32",
                array(
                    "patissier"
                )
            ) ,
            array(
                "john.doc32",
                array(
                    "menuisier",
                    "charpentier"
                )
            )
        );
    }
    public function dataNotgroupCreate()
    {
        return array(
            array(
                "gadmin"
            ) ,
            array(
                "all"
            )
        );
    }
}
?>