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
//require_once 'PHPUnit/Framework.php';
set_include_path(get_include_path() . PATH_SEPARATOR . "./DCPTEST:./WHAT");

require_once 'WHAT/autoload.php';
// ...
class TestSuiteDcp
{
    const logFile = "/var/tmp/pudcp.log";
    const msgFile = "/var/tmp/pudcp.msg";
    private static $allInProgress = false;
    public static function suite()
    {
        self::configure();
        self::$allInProgress = true;
        $suite = new FrameworkDcp('Project');
        
        $suite->addTest(SuiteDcp::suite());
        $suite->addTest(SuiteDcpAttribute::suite());
        $suite->addTest(SuiteDcpUser::suite());
        $suite->addTest(SuiteDcpSecurity::suite());
        // ...
        printf("\nerror log in %s, messages in %s\n", self::logFile, self::msgFile);
        return $suite;
    }
    
    private static function configure()
    {
        @unlink(self::logFile);
        ini_set("error_log", self::logFile);
        file_put_contents(self::msgFile, strftime('%Y-%m-%d %T'));
    }
    
    public static function addMessage($msg)
    {
        
        if (!self::$allInProgress) {
            print "$msg\n";
        } else {
            file_put_contents(self::msgFile, $msg, FILE_APPEND);
        }
    }
}
?>
