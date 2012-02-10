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
require_once 'WHAT/autoload.php';
class SuiteDcp
{
    public static function suite()
    {
        $suite = new FrameworkDcp('Package');
        
        $suite->addTestSuite('PU\TestDocument');
        $suite->addTestSuite('PU\TestOooLayout');
        $suite->addTestSuite('PU\TestOooSimpleLayout');
        $suite->addTestSuite('PU\TestFolder');
        $suite->addTestSuite('PU\TestSearch');
        $suite->addTestSuite('PU\TestSimpleQuery');
        $suite->addTestSuite('PU\TestProfil');
        $suite->addTestSuite('PU\TestTag');
        $suite->addTestSuite('PU\TestReport');
        $suite->addTestSuite('PU\TestLink');
        $suite->addTestSuite('PU\TestSplitXmlDocument');
        $suite->addTestSuite('PU\TestImportFamily');
        $suite->addTestSuite('PU\TestImportFamilyProperty');
        $suite->addTestSuite('PU\TestImportWorkflow');
        $suite->addTestSuite('PU\TestImportXmlDocuments');
        $suite->addTestSuite('PU\TestImportDocuments');
        $suite->addTestSuite('PU\TestImportArchive');
        $suite->addTestSuite('PU\TestImportProfid');
        $suite->addTestSuite('PU\TestImportAccess');
        $suite->addTestSuite('PU\TestImportProfil');
        $suite->addTestSuite('PU\TestExportXml');
        $suite->addTestSuite('PU\TestGetParam');
        $suite->addTestSuite('PU\TestUsage');
        $suite->addTestSuite('PU\TestParseFunction');
        $suite->addTestSuite('PU\TestParseMethod');
        $suite->addTestSuite('PU\TestExportCsv');
        $suite->addTestSuite('PU\TestMask');
        $suite->addTestSuite('PU\TestVolatileParam');
        $suite->addTestSuite('PU\TestApplicationParameters');
        // ...
        return $suite;
    }
}
?>