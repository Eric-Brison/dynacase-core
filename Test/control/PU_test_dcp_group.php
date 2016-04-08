<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Pu;
/**
 * @author Anakeen
 * @package Dcp\Pu
 */
require_once 'PU_testcase_dcp_commonfamily.php';

class TestGroup extends TestCaseDcpCommonFamily
{
    protected static function getCommonImportFile()
    {
        return "PU_data_dcp_group.ods";
    }
    /**
     * @dataProvider datagroupWithUserDelete
     * @param string $login
     * @param array $groupLoginsToCreate
     * @param array $groupLoginsToDelete
     * @param array $groupLoginsResult
     */
    public function testDeleteGroupWithUser($login, array $groupLoginsToCreate, array $groupLoginsToDelete, array $groupLoginsResult)
    {
        // create user
        $user = createDoc(self::$dbaccess, "IUSER");
        $this->assertTrue(is_object($user) , "cannot create user");
        $err = $user->setValue("us_login", $login);
        $password = 'secret';
        $err.= $user->setValue("us_passwd1", $password);
        $err.= $user->setValue("us_passwd2", $password);
        $this->assertEmpty($err, sprintf("cannot set iuser %s", $err));
        
        $err = $user->store();
        $this->assertEmpty($err, sprintf("cannot store iuser %s", $err));
        // create group
        $newGids = array();
        $groups = array();
        foreach ($groupLoginsToCreate as $gLogin) {
            /**
             * @var \_IGROUP $group
             */
            $group = createDoc(self::$dbaccess, "IGROUP");
            $this->assertTrue(is_object($group) , "cannot create group");
            $err = $group->setValue("us_login", $gLogin);
            $this->assertEmpty($err, sprintf("cannot set igroup %s", $err));
            
            $err = $group->store();
            $this->assertEmpty($err, sprintf("cannot store igroup %s", $err));
            $group->insertDocument($user->initid);
            $newGids[] = $group->getRawValue("us_whatid");
            $groups[$gLogin] = $group;
        }
        $u = new \Account("", $user->getRawValue("us_whatid"));
        $this->assertTrue($u->isAffected() , sprintf("cannot find %s account", $user->getRawValue("us_whatid")));
        $gids = $u->getGroupsId();
        
        $this->assertEmpty(array_diff($newGids, $gids) , "groups are not in new user");
        //Deleting group
        foreach ($groupLoginsToDelete as $gLogin) {
            $err = $groups[$gLogin]->Delete();
            $this->assertEmpty($err, sprintf("error when delete group : %s", $err));
        }
        
        $userGroups = $u->getUserParents();
        //Checking result
        foreach ($userGroups as $uGroup) {
            if (($uGroup["login"] != "all") && ($uGroup["accounttype"] != 'R')) {
                $this->assertTrue(in_array($uGroup["login"], $groupLoginsResult) , sprintf("login %s not in %s", $uGroup["login"], implode(',', $groupLoginsResult)));
            }
        }
    }
    /**
     * @dataProvider datagroupCreate
     * @param string $login
     */
    public function testDeleteGroup($login)
    {
        $group = $this->testCreateGroup($login);
        $err = $group->Delete();
        $this->assertEmpty($err, sprintf("cannot delete igroup %s", $err));
    }
    /**
     * @dataProvider datagroupCreate
     * @param string $login login for user and group
     * @return \_IGROUP
     */
    public function testCreateGroup($login)
    {
        /**
         * @var \_IGROUP $doc
         */
        $doc = createDoc(self::$dbaccess, "IGROUP");
        $this->assertTrue(is_object($doc) , "cannot create group");
        $err = $doc->setValue("us_login", $login);
        $this->assertEmpty($err, sprintf("cannot set igroup %s", $err));
        
        $err = $doc->store();
        $this->assertEmpty($err, sprintf("cannot store igroup %s", $err));
        
        $u = new \Account();
        $this->assertTrue($u->setLoginName($login) , "system group not found");
        $this->assertEquals($login, $u->login);
        $this->assertEquals($doc->id, $u->fid, "mismatch document igroup reference");
        $this->assertEquals($doc->getRawValue("us_whatid") , $u->id, "mismatch system igroup reference");
        return $doc;
    }
    /**
     * @param string $userLogin
     * @param array $groupLogins
     * @---depends testCreateGroup
     * @return \Account
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
            $group->insertDocument($user->initid);
            $newGids[] = $group->getRawValue("us_whatid");
        }
        $u = new \Account("", $user->getRawValue("us_whatid"));
        $this->assertTrue($u->isAffected());
        $gids = $u->getGroupsId();
        
        $this->assertEmpty(array_diff($newGids, $gids) , "groups are not in new user");
    }
    /**
     * @dataProvider dataNotgroupCreate
     * @param string $login
     */
    public function testNotCreateGroup($login)
    {
        
        $doc = createDoc(self::$dbaccess, "IGROUP");
        $this->assertTrue(is_object($doc) , "cannot create group");
        $err = $doc->setValue("us_login", $login);
        $this->assertEmpty($err, sprintf("cannot set igroup %s", $err));
        
        $err = $doc->store();
        $this->assertNotEmpty($err, sprintf("must be impossible to store igroup"));
        
        $u = new \Account();
        $this->assertTrue($u->setLoginName($login) , "system group not found");
        $this->assertEquals($login, $u->login);
    }
    /**
     * @param $userId int|string The user performing the insertDocument()
     * @param $groupId int|string The group on which insertDocument() is performed
     * @param $insertUserId int|string The user inserted in the group
     * @dataProvider data_userInsertDocument
     */
    public function test_userInsertDocument($userId, $groupId, $insertUserId)
    {
        /**
         * @var \Dcp\Core\UserAccount $user
         */
        $user = new_Doc(self::$dbaccess, $userId, true);
        if (!$user->isAlive()) {
            $this->markTestIncomplete(sprintf("User with id '%s' is not alive.", $userId));
        }
        $userWhatId = $user->getRawValue('us_whatid');
        $userAccount = new \Account(self::$dbaccess, $userWhatId);
        /**
         * @var \Dcp\Core\GroupAccount $group
         */
        $group = new_Doc(self::$dbaccess, $groupId, true);
        if (!$group->isAlive()) {
            $this->markTestIncomplete(sprintf("Group with id '%s' is not alive.", $groupId));
        }
        $groupWhatId = $group->getRawValue('us_whatid');
        $groupAccount = new \Account(self::$dbaccess, $groupWhatId);
        /**
         * @var \Dcp\Core\UserAccount $insertUser
         */
        $insertUser = new_Doc(self::$dbaccess, $insertUserId);
        if (!$insertUser->isAlive()) {
            $this->markTestIncomplete(sprintf("User with id '%s' is not alive.", $insertUserId));
        }
        $insertUserWhatId = $insertUser->getRawValue('us_whatid');
        $insertUserAccount = new \Account(self::$dbaccess, $insertUserWhatId);
        /*
         * Setuid to $userId
        */
        $this->sudo($user->getRawValue('us_login'));
        /*
         * Insert $insertUserId into $groupId
        */
        $group->insertDocument($insertUserId);
        /*
         * Check table groups
        */
        $groupsIdList = $insertUserAccount->getGroupsId();
        $this->assertTrue(in_array($groupWhatId, $groupsIdList) , sprintf("User with id '%d' has not group with id '%d' as parent.", $insertUserWhatId, $groupWhatId));
        /*
         * Check table fld
        */
        $fld = $group->getContent();
        $found = false;
        foreach ($fld as & $doc) {
            if ($doc['id'] == $insertUser->id) {
                $found = true;
                break;
            }
        }
        unset($doc);
        $this->assertTrue($found, sprintf("Group with '%d' does not contain inserted user with id '%d'.", $group->id, $insertUser->id));
        /*
         * Exit sudo
        */
        $this->exitSudo();
    }
    
    public function datagroupWithUserDelete()
    {
        return array(
            array(
                "john.doc32",
                array(
                    "patissier"
                ) ,
                array(
                    "patissier"
                ) ,
                array()
            ) ,
            array(
                "john.doc32",
                array(
                    "patissier",
                    "menuisier",
                    "charpentier"
                ) ,
                array(
                    "patissier"
                ) ,
                array(
                    "menuisier",
                    "charpentier"
                )
            ) ,
            array(
                "john.doc32",
                array(
                    "patissier",
                    "menuisier",
                    "charpentier"
                ) ,
                array(
                    "menuisier",
                    "patissier"
                ) ,
                array(
                    "charpentier"
                )
            )
        );
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
    public function data_userInsertDocument()
    {
        return array(
            array(
                'U_1',
                'G_1',
                'U_2'
            )
        );
    }
}
