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

class SuiteDcpUser
{
    public static function suite()
    {
        $suite = new FrameworkDcp('Package');
        
        $suite->addTestSuite('Dcp\Pu\TestUser');
        $suite->addTestSuite('Dcp\Pu\TestGroup');
        $suite->addTestSuite('Dcp\Pu\TestUserDeactivateAccount');
        $suite->addTestSuite('Dcp\Pu\TestDocControlSubstitute');
        $suite->addTestSuite('Dcp\Pu\TestSearchAccount');
        $suite->addTestSuite('Dcp\Pu\TestGroupAccount');
        $suite->addTestSuite('Dcp\Pu\TestImportAccounts');
        // ...
        return $suite;
    }
}
