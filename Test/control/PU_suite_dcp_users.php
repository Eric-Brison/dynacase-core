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
        // ...
        return $suite;
    }
}
?>