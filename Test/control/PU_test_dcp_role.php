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

class TestRole extends TestCaseDcpCommonFamily
{
    protected static $outputDir;
    /**
     * import TST_FAMSETVALUE family
     * @static
     * @return string
     */
    protected static function getCommonImportFile()
    {
        return array(
            "PU_data_dcp_role_family.ods"
        );
    }
    /**
     * @dataProvider dataRoleByGroup
     */
    public function testRoleByGroup(array $addTo, array $expectedRoles)
    {
        $u = new \User(self::$dbaccess, "");
        $u->login = "tst_jd1";
        $u->password_new = 'a';
        $err = $u->add();
        $this->assertEmpty($err, "cannot create user");
        $du = new_doc(self::$dbaccess, $u->fid);
        $this->assertTrue($du->isAlive() , "cannot create user document");
        
        foreach ($addTo as $aGroupName) {
            /**
             * @var \_IGROUP $dg
             */
            $dg = new_doc(self::$dbaccess, $aGroupName);
            $err = $dg->addFile($du->id);
            $this->assertEmpty($err, "cannot add user to $aGroupName");
        }
        $u->setLoginName("tst_jd1");
        $uRoles = $u->getAllRoles();
        $uRoleLogins = array();
        foreach ($uRoles as $aRole) {
            $uRoleLogins[] = $aRole["login"];
        }
        
        foreach ($expectedRoles as $roleLogin) {
            $this->assertTrue(in_array($roleLogin, $uRoleLogins) , sprintf("role %s must be present", $roleLogin));
        }
        foreach ($uRoleLogins as $roleLogin) {
            $this->assertTrue(in_array($roleLogin, $expectedRoles) , sprintf("role %s must not be present", $roleLogin));
        }
    }
    /**
     * test from import
     * @dataProvider dataDirectRole
     */
    public function testDirectRole($login, array $expectedRoles)
    {
        $u = new \User(self::$dbaccess);
        $u->setLoginName($login);
        $this->assertTrue($u->isAffected() , "cannot find $login user");
        $uRoleIds = $u->getRoles();
        simpleQuery(self::$dbaccess, sprintf("select login from users where id in (%s)", implode(',', $uRoleIds)) , $uRoleLogins, true);
        
        foreach ($expectedRoles as $roleLogin) {
            $this->assertTrue(in_array($roleLogin, $uRoleLogins) , sprintf("role %s must be present", $roleLogin));
        }
        foreach ($uRoleLogins as $roleLogin) {
            $this->assertTrue(in_array($roleLogin, $expectedRoles) , sprintf("role %s must not be present", $roleLogin));
        }
    }
    /**
     * test from import
     * @dataProvider dataRoleMail
     */
    public function testRoleMail($roleLogin, $expectRawMail, $expectCompleteMail)
    {
        $r = new \User(self::$dbaccess);
        $r->setLoginName($roleLogin);
        $this->assertTrue($r->isAffected() , "cannot find $roleLogin role");
        
        $rawMail = $r->getMail(true);
        $completeMail = $r->getMail(false);
        $this->assertEquals($expectRawMail, $rawMail, "role raw mail test");
        $this->assertEquals($expectCompleteMail, $completeMail, "role complete mail test");
        /**
         * @var \_ROLE $dr
         */
        $dr = new_doc(self::$dbaccess, $r->fid);
        $this->assertTrue($dr->isAlive() , "cannot find $roleLogin document role");
        
        $rawMail = $dr->getMail(true);
        $completeMail = $dr->getMail(false);
        $this->assertEquals($expectRawMail, $rawMail, "document role raw mail test");
        $this->assertEquals($expectCompleteMail, $completeMail, "document role complete mail test");
    }
    /**
     * @dataProvider dataAccessByRole
     */
    public function testAccessByRole($docid, $login, array $expectedAccesses)
    {
        $this->sudo($login);
        $d = new_doc(self::$dbaccess, $docid);
        $this->assertTrue($d->isAlive() , "document $docid not found");
        
        foreach ($expectedAccesses as $aName => $aAccess) {
            $err = $d->control($aName);
            $this->assertEquals($aAccess, ($err == "") , "error in access $aName : $err");
        }
    }
    
    public function dataAccessByRole()
    {
        return array(
            array(
                "docName" => "TST_BASERED1",
                "login" => "ublue",
                "access" => array(
                    "view" => true,
                    "edit" => true,
                    "delete" => false
                )
            ) ,
            array(
                "docName" => "TST_BASERED1",
                "login" => "ugreen",
                "access" => array(
                    "view" => false,
                    "edit" => false,
                    "delete" => false
                )
            ) ,
            
            array(
                "docName" => "TST_BASERED2",
                "login" => "ublue",
                "access" => array(
                    "view" => true,
                    "edit" => true,
                    "delete" => false
                )
            ) ,
            array(
                "docName" => "TST_BASERED2",
                "login" => "ugreen",
                "access" => array(
                    "view" => false,
                    "edit" => false,
                    "delete" => false
                )
            ) ,
            array(
                "docName" => "TST_BASEBLUE1",
                "login" => "ublue",
                "access" => array(
                    "view" => true,
                    "edit" => true,
                    "delete" => false
                )
            ) ,
            array(
                "docName" => "TST_BASEBLUE1",
                "login" => "ugreen",
                "access" => array(
                    "view" => false,
                    "edit" => false,
                    "delete" => false
                )
            ) ,
            
            array(
                "docName" => "TST_BASEBLUE2",
                "login" => "ublue",
                "access" => array(
                    "view" => true,
                    "edit" => true,
                    "delete" => false
                )
            ) ,
            array(
                "docName" => "TST_BASEBLUE2",
                "login" => "ugreen",
                "access" => array(
                    "view" => true,
                    "edit" => true,
                    "delete" => false
                )
            ) ,
            array(
                "docName" => "TST_BASEYELLOW1",
                "login" => "ublue",
                "access" => array(
                    "view" => false,
                    "edit" => false,
                    "delete" => false
                )
            ) ,
            array(
                "docName" => "TST_BASEYELLOW1",
                "login" => "ugreen",
                "access" => array(
                    "view" => false,
                    "edit" => false,
                    "delete" => false
                )
            ) ,
            
            array(
                "docName" => "TST_BASEYELLOW2",
                "login" => "ublue",
                "access" => array(
                    "view" => false,
                    "edit" => false,
                    "delete" => false
                )
            ) ,
            array(
                "docName" => "TST_BASEYELLOW2",
                "login" => "ugreen",
                "access" => array(
                    "view" => true,
                    "edit" => true,
                    "delete" => false
                )
            ) ,
            array(
                "docName" => "TST_BASEYELLOW2",
                "login" => "uryellow",
                "access" => array(
                    "view" => true,
                    "edit" => true,
                    "delete" => false
                )
            ) ,
            array(
                "docName" => "TST_BASEYELLOW2",
                "login" => "urgreen",
                "access" => array(
                    "view" => true,
                    "edit" => true,
                    "delete" => false
                )
            ) ,
            array(
                "docName" => "TST_BASEYELLOW2",
                "login" => "uggreen",
                "access" => array(
                    "view" => true,
                    "edit" => true,
                    "delete" => false
                )
            ) ,
            
            array(
                "docName" => "TST_BASEGREEN",
                "login" => "ublue",
                "access" => array(
                    "view" => true,
                    "edit" => false,
                    "delete" => false
                )
            ) ,
            array(
                "docName" => "TST_BASEGREEN",
                "login" => "ugreen",
                "access" => array(
                    "view" => true,
                    "edit" => true,
                    "delete" => false
                )
            ) ,
            array(
                "docName" => "TST_BASEGREEN",
                "login" => "uryellow",
                "access" => array(
                    "view" => true,
                    "edit" => false,
                    "delete" => false
                )
            ) ,
            array(
                "docName" => "TST_BASEGREEN",
                "login" => "urgreen",
                "access" => array(
                    "view" => true,
                    "edit" => false,
                    "delete" => false
                )
            ) ,
            array(
                "docName" => "TST_BASEGREEN",
                "login" => "uggreen",
                "access" => array(
                    "view" => true,
                    "edit" => true,
                    "delete" => false
                )
            )
        );
    }
    
    public function dataRoleMail()
    {
        return array(
            array(
                "login" => "rblue",
                "rawMail" => "blue@anakeen.com, green@anakeen.com, green@group.org, green@role.org",
                "completeMail" => '"John Bleu" <blue@anakeen.com>, "Jane Vert" <green@anakeen.com>, "John Vert" <green@group.org>, "John Blue-Yellow" <green@role.org>'
            ) ,
            array(
                "login" => "rred",
                "rawMail" => "blue@anakeen.com",
                "completeMail" => '"John Bleu" <blue@anakeen.com>'
            ) ,
            array(
                "login" => "ryellow",
                "rawMail" => "green@anakeen.com, green@group.org, green@role.org, yellow@role.org",
                "completeMail" => '"Jane Vert" <green@anakeen.com>, "John Vert" <green@group.org>, "John Blue-Yellow" <green@role.org>, "Jane Jaune" <yellow@role.org>'
            )
        );
    }
    
    public function dataDirectRole()
    {
        return array(
            array(
                "login" => "ublue",
                "expectRoles" => array(
                    "rblue"
                )
            ) ,
            array(
                "login" => "ugreen",
                "expectRoles" => array(
                    "ryellow",
                    "rblue"
                )
            )
        );
    }
    
    public function dataRoleByGroup()
    {
        return array(
            array(
                "addTo" => array(
                    "TST_GRPRED"
                ) ,
                "expectRoles" => array(
                    "rred"
                )
            ) ,
            array(
                "addTo" => array(
                    "TST_GRPGREEN"
                ) ,
                "expectRoles" => array(
                    "rblue",
                    "ryellow"
                )
            ) ,
            array(
                "addTo" => array(
                    "TST_GRPYELLOW"
                ) ,
                "expectRoles" => array(
                    "rred",
                    "ryellow"
                )
            ) ,
            array(
                "addTo" => array(
                    "TST_GRPBLUE"
                ) ,
                "expectRoles" => array(
                    "rred",
                    "rblue"
                )
            ) ,
            array(
                "addTo" => array(
                    "TST_GRPBLUE",
                    "TST_GRPRED"
                ) ,
                "expectRoles" => array(
                    "rred",
                    "rblue"
                )
            ) ,
            array(
                "addTo" => array(
                    "TST_GRPBLUE",
                    "TST_GRPGREEN"
                ) ,
                "expectRoles" => array(
                    "rred",
                    "ryellow",
                    "rblue"
                )
            ) ,
            array(
                "addTo" => array(
                    "TST_GRPBLUE",
                    "TST_GRPGREEN",
                    "TST_GRPYELLOW"
                ) ,
                "expectRoles" => array(
                    "rred",
                    "ryellow",
                    "rblue"
                )
            )
        );
    }
}
