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

class TestSearchAccount extends TestCaseDcpCommonFamily
{
    protected static $outputDir;
    /**
     * import TST_FAMSETVALUE family
     * @static
     * @return array|string
     */
    protected static function getCommonImportFile()
    {
        return array(
            "PU_data_dcp_searchaccount.ods"
        );
    }
    /**
     * @dataProvider dataSearchByRole
     */
    public function testSearchByRole($roleFilter, $groupFilter, array $expectedAccounts)
    {
        $s = new \SearchAccount();
        if ($roleFilter) $s->addRoleFilter($roleFilter);
        if ($groupFilter) $s->addGroupFilter($groupFilter);
        $s->setReturnType($s::returnAccount);
        /**
         * @var \AccountList $al
         */
        $al = $s->search();
        
        $expectedAccounts = array_map("mb_strtolower", $expectedAccounts);
        $loginFounds = array();
        
        $lLogin = $this->getAccounlLogin($al);
        /**
         * @var \Account $account
         */
        foreach ($al as $account) {
            $login = $account->login;
            $this->assertTrue(in_array($login, $expectedAccounts) , sprintf("login <%s> must not be present : %s", $login, $lLogin));
            $loginFounds[] = $login;
        }
        
        foreach ($expectedAccounts as $expectLogin) {
            $this->assertTrue(in_array($expectLogin, $loginFounds) , sprintf("login <%s> must be present", $expectLogin));
        }
    }
    
    private function getAccounlLogin(\AccountList $al)
    {
        $logins = array();
        /**
         * @var \Account $account
         */
        foreach ($al as $account) {
            $logins[] = $account->login;
        }
        return implode(',', $logins);
    }
    /**
     * @dataProvider dataSearchByRole
     */
    public function testSearchByRoleDocument($roleFilter, $groupFilter, array $expectedAccounts)
    {
        $s = new \SearchAccount();
        if ($roleFilter) $s->addRoleFilter($roleFilter);
        if ($groupFilter) $s->addGroupFilter($groupFilter);
        $s->setReturnType($s::returnDocument);
        /**
         * @var \DocumentList $al
         */
        $al = $s->search();
        
        $expectedAccounts = array_map("mb_strtolower", $expectedAccounts);
        $loginFounds = array();
        /**
         * @var \Doc $doc
         */
        foreach ($al as $doc) {
            $login = '';
            if ($doc->getAttribute("us_login")) $login = $doc->getRawValue("us_login");
            elseif ($doc->getAttribute("role_login")) $login = $doc->getRawValue("role_login");
            $this->assertTrue(in_array($login, $expectedAccounts) , sprintf("login <%s> #%s must not be present", $login, $doc->id));
            $loginFounds[] = $login;
        }
        
        foreach ($expectedAccounts as $expectLogin) {
            $this->assertTrue(in_array($expectLogin, $loginFounds) , sprintf("login <%s> must be present", $expectLogin));
        }
    }
    /**
     * @dataProvider dataCountSearchByRole
     */
    public function testCountSearchByRole($slice, $start, $order, $roleFilter, array $expectedAccounts)
    {
        $s = new \SearchAccount();
        $s->addRoleFilter($roleFilter);
        $s->setSlice($slice);
        $s->setStart($start);
        $s->setOrder($order);
        $s->setReturnType($s::returnAccount);
        /**
         * @var \AccountList $al
         */
        $al = $s->search();
        
        $expectedAccounts = array_map("mb_strtolower", $expectedAccounts);
        /**
         * @var \Account $account
         */
        $k = 0;
        foreach ($al as $account) {
            $login = $account->login;
            $this->assertEquals($expectedAccounts[$k], $login, sprintf("%s login must not be present", $login));
            $k++;
        }
    }
    /**
     * @dataProvider dataFilterSearch
     */
    public function testFilterSearch($filter, $filterArg, $accountType, array $expectedAccounts)
    {
        $s = new \SearchAccount();
        $s->addFilter($filter, $filterArg);
        if ($accountType) $s->setTypeFilter($accountType);
        $s->setOrder("login");
        $s->setReturnType($s::returnAccount);
        /**
         * @var \AccountList $al
         */
        $al = $s->search();
        
        $expectedAccounts = array_map("mb_strtolower", $expectedAccounts);
        $ll = $this->getAccounlLogin($al);
        /**
         * @var \Account $account
         */
        $k = 0;
        foreach ($al as $account) {
            $login = $account->login;
            $this->assertEquals($expectedAccounts[$k], $login, sprintf("%s login must not be present : found %s", $login, $ll));
            $k++;
        }
        $this->assertEquals(count($expectedAccounts) , count($al) , sprintf("not same count expected %d : %s", count($expectedAccounts) , $ll));
    }
    /**
     * @dataProvider dataFilterViewControl
     */
    public function testFilterViewControl($login, $filter, $filterArg, array $expectedAccounts)
    {
        $this->sudo($login);
        $s = new \SearchAccount();
        $s->addFilter($filter, $filterArg);
        $s->setOrder("login");
        $s->overrideViewControl(false);
        $s->setReturnType($s::returnAccount);
        /**
         * @var \AccountList $al
         */
        $al = $s->search();
        
        $expectedAccounts = array_map("mb_strtolower", $expectedAccounts);
        $ll = $this->getAccounlLogin($al);
        /**
         * @var \Account $account
         */
        $k = 0;
        foreach ($al as $account) {
            $login = $account->login;
            $this->assertEquals($expectedAccounts[$k], $login, sprintf("%s login must not be present : found %s", $login, $ll));
            $k++;
        }
        
        $this->assertEquals(count($expectedAccounts) , count($al) , sprintf("not same count expected %d : %s", count($expectedAccounts) , $ll));
        $this->exitSudo();
    }
    /**
     * @dataProvider dataDocName2Login
     */
    public function testDocName2Login($docName, $login)
    {
        $this->assertEquals(mb_strtolower($login) , \SearchAccount::getLoginFromDocName($docName) , "logical name convert to login failed");
    }
    /**
     * @dataProvider dataFilterByFamily
     */
    public function testFilterByFamily($family, $filter, $expectedCount)
    {
        $this->sudo("tstlogina1");
        $s = new \SearchAccount();
        $s->addFilter($filter);
        $s->setOrder("login");
        $s->overrideViewControl(false);
        $s->setReturnType($s::returnAccount);
        $s->filterFamily($family);
        /**
         * @var \AccountList $al
         */
        $al = $s->search();
        
        $ll = $this->getAccounlLogin($al);
        
        $this->assertEquals($expectedCount, count($al) , sprintf("not same count expected %d : %s", $expectedCount, $ll));
        $this->exitSudo();
    }
    /**
     * @dataProvider dataFilterByFamily
     */
    public function testFilterByFamilyNoView($family, $filter, $expectedCount)
    {
        $s = new \SearchAccount();
        $s->addFilter($filter);
        $s->setOrder("login");
        $s->overrideViewControl(true);
        $s->setReturnType($s::returnAccount);
        $s->filterFamily($family);
        /**
         * @var \AccountList $al
         */
        $al = $s->search();
        
        $ll = $this->getAccounlLogin($al);
        
        $this->assertEquals($expectedCount, count($al) , sprintf("not same count expected %d : %s", $expectedCount, $ll));
    }
    
    public function dataFilterByFamily()
    {
        return array(
            array(
                "family" => "TST_uSER",
                "filter" => " login ~ '^tstloginu'",
                "expect" => 7
            ) ,
            array(
                "family" => "TST_OTHERuSER",
                "filter" => " login ~ '^tstloginu'",
                "expect" => 1
            )
        );
    }
    public function dataFilterViewControl()
    {
        return array(
            array(
                "login" => "tstLoginA1",
                "filter" => "login ~ '%s'",
                "arg" => '^tst.*1$',
                array(
                    "tstlogina1",
                    "tstloginr1",
                    "tstloginu1",
                    "tstloginu11"
                )
            ) ,
            array(
                "login" => "tstLoginA2",
                "filter" => "login ~ '%s'",
                "arg" => '^tst.*1$',
                array(
                    "tstlogina1",
                    "tstloging1",
                    "tstloginu1"
                )
            ) ,
            
            array(
                "login" => "tstLoginU1",
                "filter" => "login ~ '%s'",
                "arg" => '^tst.*1$',
                array()
            )
        );
    }
    public function dataFilterSearch()
    {
        return array(
            array(
                "filter" => "login = lower('%s')",
                "arg" => 'tstLoginU1',
                "type" => 0,
                array(
                    "tstLoginU1"
                )
            ) ,
            array(
                "filter" => "login ~ '^tst.*1$'",
                "arg" => '^tst.*1$',
                "type" => \SearchAccount::userType,
                array(
                    "tstLoginA1",
                    "tstLoginU1",
                    "tstLoginU11"
                )
            ) ,
            array(
                "filter" => "login ~ '%s'",
                "arg" => '^tst.*1$',
                "type" => \SearchAccount::groupType,
                array(
                    "tstLoginG1"
                )
            ) ,
            array(
                "filter" => "login ~ '%s'",
                "arg" => '^tst.*1$',
                "type" => \SearchAccount::roleType,
                array(
                    "tstLoginR1"
                )
            ) ,
            array(
                "filter" => "login ~ '%s'",
                "arg" => '^tst.*1$',
                "type" => \SearchAccount::roleType | \SearchAccount::groupType,
                array(
                    "tstLoginG1",
                    "tstLoginR1"
                )
            ) ,
            array(
                "filter" => "login ~ '%s'",
                "arg" => '^tst.*1$',
                "type" => \SearchAccount::userType | \SearchAccount::groupType,
                array(
                    "tstLoginA1",
                    "tstLoginG1",
                    "tstLoginU1",
                    "tstLoginU11"
                )
            ) ,
            array(
                "filter" => "login ~ '%s'",
                "arg" => '^tst.*1$',
                "type" => \SearchAccount::userType | \SearchAccount::roleType,
                array(
                    "tstLoginA1",
                    "tstLoginR1",
                    "tstLoginU1",
                    "tstLoginU11"
                )
            )
        );
    }
    
    public function dataDocName2Login()
    {
        return array(
            array(
                "TST_ROLE1",
                "tstLoginR1"
            ) ,
            array(
                "TST_GROUP1",
                "tstLoginG1"
            ) ,
            array(
                "TST_USER1",
                "tstLoginU1"
            )
        );
    }
    public function dataCountSearchByRole()
    {
        return array(
            array(
                "slice" => "ALL",
                "start" => 0,
                "order" => "id",
                "role" => "tstLoginR1",
                array(
                    "tstLoginU1",
                    "tstLoginU2",
                    "tstLoginU3",
                    "tstLoginU4",
                    "tstLoginU5"
                )
            ) ,
            array(
                "slice" => "ALL",
                "start" => 0,
                "order" => "id desc",
                "role" => "tstLoginR1",
                array(
                    "tstLoginU5",
                    "tstLoginU4",
                    "tstLoginU3",
                    "tstLoginU2",
                    "tstLoginU1"
                )
            ) ,
            array(
                "slice" => "2",
                "start" => 0,
                "order" => "login",
                "role" => "tstLoginR1",
                array(
                    "tstLoginU1",
                    "tstLoginU2"
                )
            ) ,
            array(
                "slice" => 2,
                "start" => 2,
                "order" => "login",
                "role" => "tstLoginR1",
                array(
                    "tstLoginU3",
                    "tstLoginU4"
                )
            )
        );
    }
    
    public function dataSearchByRole()
    {
        return array(
            array(
                "role" => "tstLoginR1",
                "group" => "",
                array(
                    "tstLoginU1",
                    "tstLoginU2",
                    "tstLoginU3",
                    "tstLoginU4",
                    "tstLoginU5"
                )
            ) ,
            
            array(
                "role" => "tstLoginR2",
                "group" => "",
                array(
                    "tstLoginU2",
                    "tstLoginU3",
                    "tstLoginU4",
                    "tstLoginU6"
                )
            ) ,
            
            array(
                "role" => "tstLoginR3",
                "group" => "",
                array(
                    "tstLoginU3",
                    "tstLoginU4",
                    "tstLoginU7"
                )
            ) ,
            
            array(
                "role" => "tstLoginR3 tstLoginR2",
                "group" => "",
                array(
                    "tstLoginU2",
                    "tstLoginU3",
                    "tstLoginU4",
                    "tstLoginU6",
                    "tstLoginU7"
                )
            ) ,
            
            array(
                "role" => "tstLoginR3 tstLoginR1 tstLoginR2",
                "group" => "",
                array(
                    "tstLoginU1",
                    "tstLoginU2",
                    "tstLoginU3",
                    "tstLoginU4",
                    "tstLoginU5",
                    "tstLoginU6",
                    "tstLoginU7"
                )
            ) ,
            
            array(
                "role" => "tstLoginR4",
                "group" => "",
                array(
                    "tstLoginG1",
                    "tstLoginU10",
                    "tstLoginU11",
                    "tstLoginU15",
                    "tstLoginU4"
                )
            ) ,
            array(
                "role" => "",
                "group" => "tstLoginG1",
                array(
                    "tstLoginU10",
                    "tstLoginU11",
                    "tstLoginU15"
                )
            ) ,
            
            array(
                "role" => "",
                "group" => "tstLoginG2",
                array(
                    "tstLoginU12",
                    "tstLoginU13",
                    "tstLoginU15"
                )
            ) ,
            
            array(
                "role" => "",
                "group" => "tstLoginG3",
                array(
                    "tstLoginU14",
                    "tstLoginU15"
                )
            ) ,
            
            array(
                "role" => "",
                "group" => "tstLoginG3 tstLoginG2 tstLoginG1",
                array(
                    "tstLoginU10",
                    "tstLoginU11",
                    "tstLoginU12",
                    "tstLoginU13",
                    "tstLoginU14",
                    "tstLoginU15"
                )
            ) ,
            
            array(
                "role" => "tstLoginR3",
                "group" => "tstLoginG3",
                array(
                    "tstLoginU14",
                    "tstLoginU15",
                    "tstLoginU3",
                    "tstLoginU4",
                    "tstLoginU7"
                )
            )
        );
    }
}
