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

class SuiteDcpUser
{
    public static function suite()
    {
        $suite = new FrameworkDcp('Package');
        
        $suite->addTestSuite('PU\TestUser');
        $suite->addTestSuite('PU\TestGroup');
        $suite->addTestSuite('PU\TestUserDeactivateAccount');
        $suite->addTestSuite('PU\TestDocControlSubstitute');
        // ...
        return $suite;
    }
}
?>