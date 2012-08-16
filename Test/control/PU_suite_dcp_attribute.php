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

require_once 'WHAT/autoload.php';
class SuiteDcpAttribute
{
    public static function suite()
    {
        $suite = new FrameworkDcp('Package');
        
        $suite->addTestSuite('Dcp\Pu\TestAddArrayRow');
        $suite->addTestSuite('Dcp\Pu\TestGetResPhpFunc');
        $suite->addTestSuite('Dcp\Pu\TestGetEnum');
        $suite->addTestSuite('Dcp\Pu\TestAttributeVisibility');
        $suite->addTestSuite('Dcp\Pu\TestAttributeValue');
        $suite->addTestSuite('Dcp\Pu\TestAttributeDefault');
        $suite->addTestSuite('Dcp\Pu\TestAttributeCompute');
        $suite->addTestSuite('Dcp\Pu\TestAttributeDate');
        $suite->addTestSuite('Dcp\Pu\TestAttributeSlashes');
        $suite->addTestSuite('Dcp\Pu\TestGetSearchMethods');
        $suite->addTestSuite('Dcp\Pu\TestGetSortAttributes');
        $suite->addTestSuite('Dcp\Pu\TestGetSortProperties');
        // $suite->addTestSuite('Dcp\Pu\TestAutocompletion'); // This test requires a bootstrap.php with ob_start()
        // ...
        return $suite;
    }
}
?>