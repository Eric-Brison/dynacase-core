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

class TestImportAccounts extends TestCaseDcpCommonFamily
{
    /**
     * import TST_FAMSETVALUE family
     * @static
     * @return string
     */
    protected static function getCommonImportFile()
    {
        return "PU_data_dcp_accountFamilies.ods";
    }
    /**
     * test import simple document
     * @dataProvider dataGoodImportAccount
     * @param $accountFile
     * @param array $expects
     */
    public function testGoodImportAccount($accountFile, array $expects)
    {
        $err = '';
        
        try {
            $import = new \Dcp\Core\ImportAccounts();
            
            $import->setFile(sprintf("%s/DCPTEST/Layout/%s", DEFAULT_PUBDIR, $accountFile));
            $import->import();
        }
        catch(\Exception $e) {
            $err = $e->getMessage();
        }
        $this->assertEmpty($err, "import error detected $err");
        $testingAccount = new \Account();
        foreach ($expects as $accountData) {
            $login = $accountData["login"];
            $testingAccount->setLoginName($login);
            
            $this->assertTrue($testingAccount->isAffected() , "Login $login not found");
            foreach ($accountData["expectValues"] as $aid => $expVal) {
                
                $this->assertEquals($expVal, $testingAccount->$aid, "Search \"$aid\"." . print_r($testingAccount->getValues() , true));
            }
            if (isset($accountData["docName"])) {
                $doc = new_doc("", $accountData["docName"]);
                $this->assertTrue($doc->isAlive() , sprintf("Doc %s not found", $accountData["docName"]));
                $this->assertEquals($doc->getRawValue("us_whatid") , $testingAccount->id, sprintf("Account \"%s\" is not linked: %s", $testingAccount->login, $accountData["docName"]));
            } else {
                $doc = new_doc("", $testingAccount->fid);
            }
            
            if (isset($accountData["expectedDocValue"])) {
                foreach ($accountData["expectedDocValue"] as $aid => $expVal) {
                    $this->assertEquals($expVal, $doc->getRawValue($aid) , sprintf("for %s (%s) : %s", $doc, $aid, print_r($doc->getValues() , true)));
                }
            }
            if (isset($accountData["expectedRoles"])) {
                $roles = $testingAccount->getAllRoles();
                $this->assertEquals(count($accountData["expectedRoles"]) , count($roles) , sprintf("Role count fail %s : %s", $testingAccount->login, print_r($roles, true)));
                foreach ($accountData["expectedRoles"] as $roleRef) {
                    $filteredRoles = array_filter($roles, function ($roleData) use ($roleRef)
                    {
                        return ($roleData["login"] === $roleRef);
                    });
                    $this->assertEquals(count($filteredRoles) , 1, sprintf("role %s not found : %s", $roleRef, $testingAccount->login, print_r($roles, true)));
                }
            }
            
            if (isset($accountData["expectedGroups"])) {
                $groupIds = $testingAccount->getGroupsId();
                $this->assertEquals(count($accountData["expectedGroups"]) , count($groupIds) , sprintf("Group count fail %s : %s", $testingAccount->login, print_r($groupIds, true)));
                foreach ($accountData["expectedGroups"] as $groupRef) {
                    $filteredRoles = array_filter($groupIds, function ($groupId) use ($groupRef)
                    {
                        $a = new \Account();
                        $a->select($groupId);
                        return ($a->login === strtolower($groupRef));
                    });
                    $this->assertEquals(count($filteredRoles) , 1, sprintf("group %s not found : %s : %s", $groupRef, $testingAccount->login, print_r($groupIds, true)));
                }
            }
            
            if (isset($accountData["password"])) {
                $this->assertTrue($testingAccount->checkpassword($accountData["password"]) , sprintf("password no match for %s : \"%s\"\n%s", $testingAccount->login, $testingAccount->password, print_r($testingAccount->getValues() , true)));
            }
        }
    }
    public function dataGoodImportAccount()
    {
        return array(
            array(
                "file" => "PU_data_dcp_accounts1.xml",
                array(
                    // ============= ROLES ===========
                    array(
                        "login" => "fat capacity",
                        "expectValues" => array(
                            "lastname" => "Grosse capacité",
                            "mail" => "",
                            "password" => "-"
                        ) ,
                        "docName" => "TST_CAPACITY1",
                        "expectedRoles" => array() ,
                        "expectedGroups" => array()
                    ) ,
                    array(
                        "login" => "big capacity",
                        "expectValues" => array(
                            "lastname" => "Grande capacité",
                            "mail" => "",
                            "password" => "-"
                        ) ,
                        "docName" => "TST_CAPACITY2",
                        "expectedDocValue" => array(
                            "role_login" => "big capacity",
                            "role_name" => "Grande capacité",
                            "tst_addr" => "10 rue des agences"
                        ) ,
                        "expectedRoles" => array() ,
                        "expectedGroups" => array()
                    ) ,
                    array(
                        "login" => "supervisor",
                        "expectValues" => array(
                            "lastname" => "Surveillance galactique",
                            "mail" => "",
                            "password" => "-"
                        ) ,
                        "docName" => "TST_SUPERVISOR",
                        "expectedDocValue" => array(
                            "role_login" => "supervisor",
                            "role_name" => "Surveillance galactique"
                        ) ,
                        "expectedRoles" => array() ,
                        "expectedGroups" => array()
                    ) ,
                    // ============= GROUPS ===========
                    array(
                        "login" => "topsupervisor",
                        "expectValues" => array(
                            "lastname" => "Sécurité du toit",
                            "mail" => "",
                            "password" => "-"
                        ) ,
                        "docName" => "TST_GRP_ROOFSUPERVISOR",
                        "expectedDocValue" => array(
                            "us_login" => "topsupervisor",
                            "grp_name" => "Sécurité du toit"
                        ) ,
                        "expectedRoles" => array(
                            "supervisor"
                        ) ,
                        "expectedGroups" => array()
                    ) ,
                    array(
                        "login" => "levelsupervisor",
                        "expectValues" => array(
                            "lastname" => "Sécurité des niveaux",
                            "mail" => "",
                            "password" => "-"
                        ) ,
                        "docName" => "TST_GRP_LEVELSUPERVISOR",
                        "expectedDocValue" => array(
                            "us_login" => "levelsupervisor",
                            "grp_name" => "Sécurité des niveaux"
                        ) ,
                        "expectedRoles" => array(
                            "supervisor"
                        ) ,
                        "expectedGroups" => array(
                            "topsupervisor"
                        )
                    ) ,
                    array(
                        "login" => "undergroundsupervisor",
                        "expectValues" => array(
                            "lastname" => "Sécurité du sous-sol",
                            "mail" => "",
                            "password" => "-"
                        ) ,
                        "docName" => "TST_GRP_UNDERGROUNDSUPERVISOR",
                        "expectedDocValue" => array(
                            "us_login" => "undergroundsupervisor",
                            "grp_name" => "Sécurité du sous-sol"
                        ) ,
                        "expectedRoles" => array(
                            "supervisor"
                        ) ,
                        "expectedGroups" => array(
                            "topsupervisor"
                        )
                    ) ,
                    // ============= USERS ===========
                    array(
                        "login" => "chewie",
                        "expectValues" => array(
                            "lastname" => "Chewbacca",
                            "mail" => "chewie@starwars.com",
                            "status" => "A",
                            "password" => "-"
                        ) ,
                        "expectedRoles" => array() ,
                        "expectedGroups" => array()
                    ) ,
                    
                    array(
                        "login" => "luke",
                        "expectValues" => array(
                            "firstname" => "Luke",
                            "lastname" => "Skywalker",
                            "mail" => "luke@starwars.com",
                            "status" => "A"
                        ) ,
                        "docName" => "TST_AGENT_L",
                        "expectedDocValue" => array(
                            "tst_phone" => "63.76.89.33",
                            "tst_mat" => "3323",
                            "us_group" => "Sécurité des niveaux"
                        ) ,
                        "expectedRoles" => array(
                            "big capacity",
                            "supervisor"
                        ) ,
                        "expectedGroups" => array(
                            "LevelSupervisor"
                        ) ,
                        "password" => "May the force be with you"
                    ) ,
                    
                    array(
                        "login" => "leia",
                        "expectValues" => array(
                            "firstname" => "Leia",
                            "lastname" => "Skywalker",
                            "mail" => "leia@starwars.com",
                            "status" => "A"
                        ) ,
                        "docName" => "TST_AGENT_P",
                        "expectedDocValue" => array(
                            "tst_phone" => "63.76.89.34",
                            "tst_mat" => "3324",
                            "us_group" => "Sécurité des niveaux"
                        ) ,
                        "expectedRoles" => array(
                            "big capacity",
                            "supervisor"
                        ) ,
                        "expectedGroups" => array(
                            "LevelSupervisor"
                        ) ,
                        "password" => "May the force be with you"
                    ) ,
                    
                    array(
                        "login" => "solo",
                        "expectValues" => array(
                            "firstname" => "Han",
                            "lastname" => "Solo",
                            "mail" => "solo@starwars.com",
                            "status" => "D"
                        ) ,
                        "docName" => "TST_AGENT_H",
                        "expectedDocValue" => array(
                            "tst_phone" => "83.26.89.43",
                            "tst_mat" => "3524",
                            "us_group" => "Sécurité des niveaux\nSécurité du toit"
                        ) ,
                        "expectedRoles" => array(
                            "fat capacity",
                            "supervisor"
                        ) ,
                        "expectedGroups" => array(
                            "LevelSupervisor",
                            "TopSupervisor"
                        ) ,
                        "password" => "Falcon Millenium"
                    )
                )
            )
        );
    }
}
