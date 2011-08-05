<?php

/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 */

require_once 'PHPUnit/Framework.php';
 
require_once 'PU_TestDocument.php';
// ...
 
class Package_DocumentTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuiteDocument('Package');
 
        $suite->addTestSuite('TestDocument');
        // ...
 
        return $suite;
    }
}
?>