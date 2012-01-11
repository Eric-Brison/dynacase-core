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
//require_once 'PHPUnit/Framework.php';
set_include_path(get_include_path() . PATH_SEPARATOR . "./DCPTEST:./WHAT");

require_once 'WHAT/autoload.php';
// ...
class TestSuiteDcp
{
    const logFile = "/var/tmp/pudcp.log";
    public static function suite()
    {
        self::configure();
        $suite = new FrameworkDcp('Project');
        
        $suite->addTest(SuiteDcp::suite());
        $suite->addTest(SuiteDcpAttribute::suite());
        $suite->addTest(SuiteDcpUser::suite());
        $suite->addTest(SuiteDcpSecurity::suite());
        // ...
        print "\nerror log in " . self::logFile . "\n";
        return $suite;
    }
    
    private static function configure()
    {
        @unlink(self::logFile);
        ini_set("error_log", self::logFile);
    }
}
?>
