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

class SuiteDcp
{
    public static function suite()
    {
        $suite = new FrameworkDcp('Package');
        
        $suite->addTestSuite('PU\TestDocument');
        $suite->addTestSuite('PU\TestOooLayout');
        $suite->addTestSuite('PU\TestFolder');
        $suite->addTestSuite('PU\TestSearch');
        $suite->addTestSuite('PU\TestProfil');
        $suite->addTestSuite('PU\TestTag');
        $suite->addTestSuite('PU\TestReport');
        $suite->addTestSuite('PU\TestLink');
        $suite->addTestSuite('PU\TestSplitXmlDocument');
        $suite->addTestSuite('PU\TestImportFamily');
        $suite->addTestSuite('PU\TestImportDocument');
        $suite->addTestSuite('PU\TestImportArchive');
        $suite->addTestSuite('PU\TestExportXml');
        $suite->addTestSuite('PU\TestGetParam');
                $suite->addTestSuite('PU\TestUsage');
        // ...
        return $suite;
    }
}
?>