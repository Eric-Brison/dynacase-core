<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */

require_once 'PHPUnit/Framework.php';

require_once 'PU_Framework_TestSuiteDocument.php';
require_once 'PU_SuiteDocumentAll.php';
// ...
class FdlTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuiteDocument('Project');
        
        $suite->addTest(Package_DocumentTests::suite());
        // ...
        return $suite;
    }
}
?>