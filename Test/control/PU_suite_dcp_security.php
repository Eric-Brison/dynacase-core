<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package DCP
*/

namespace PU;

class SuiteDcpSecurity
{
    public static function suite()
    {
        $suite = new FrameworkDcp('Package');
        
        $suite->addTestSuite('PU\TestAccess');
        $suite->addTestSuite('PU\TestDocControl');
        // ...
        return $suite;
    }
}
?>