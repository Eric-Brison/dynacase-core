<?php

namespace PU;
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package DCP
 */
//require_once 'PHPUnit/Framework.php';
set_include_path(get_include_path() . PATH_SEPARATOR . "./DCPTEST:./WHAT");

require_once 'WHAT/autoload.php';
// ...
class TestSuiteDcp
{
    public static function suite()
    {
        $suite = new FrameworkDcp('Project');
        
        $suite->addTest(SuiteDcp::suite());
        $suite->addTest(SuiteDcpAttribute::suite());
        // ...
        return $suite;
    }
}
?>