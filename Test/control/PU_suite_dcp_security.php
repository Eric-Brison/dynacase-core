<?php
/*
 * @author Anakeen
 * @package Dcp\Pu
*/

namespace Dcp\Pu;

class SuiteDcpSecurity
{
    public static function suite()
    {
        $suite = new FrameworkDcp('Package');
        
        $suite->addTestSuite('Dcp\Pu\TestAccess');
        $suite->addTestSuite('Dcp\Pu\TestOpenAccess');
        $suite->addTestSuite('Dcp\Pu\TestDocControl');
        $suite->addTestSuite('Dcp\Pu\TestRole');
        $suite->addTestSuite('Dcp\Pu\TestRoleMove');
        $suite->addTestSuite('Dcp\Pu\TestAppInheritAcl');
        $suite->addTestSuite('Dcp\Pu\TestEditControl');
        // ...
        return $suite;
    }
}
