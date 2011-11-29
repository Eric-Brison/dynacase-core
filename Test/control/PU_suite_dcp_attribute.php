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

class SuiteDcpAttribute
{
    public static function suite()
    {
        $suite = new FrameworkDcp('Package');
        
        $suite->addTestSuite('PU\TestAddArrayRow');
        $suite->addTestSuite('PU\TestGetResPhpFunc');
        // $suite->addTestSuite('PU\TestAutocompletion'); // This test requires a bootstrap.php with ob_start()
        // ...
        return $suite;
    }
}
?>