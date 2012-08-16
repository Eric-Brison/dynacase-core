<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package Dcp\Pu
*/

namespace Dcp\Pu;

class SuiteDcpSecurity
{
    public static function suite()
    {
        $suite = new FrameworkDcp('Package');
        
        $suite->addTestSuite('Dcp\Pu\TestAccess');
        $suite->addTestSuite('Dcp\Pu\TestDocControl');
        $suite->addTestSuite('Dcp\Pu\TestRole');
        // ...
        return $suite;
    }
}
?>