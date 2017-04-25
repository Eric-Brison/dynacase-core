<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Pu;
/**
 * @author Anakeen
 * @package Dcp\Pu
 */
require_once 'WHAT/autoload.php';
class SuiteDcp
{
    public static function suite()
    {
        $suite = new FrameworkDcp('Package');
        $suite->addTestSuite('Dcp\Pu\TestDocument');
        $suite->addTestSuite('Dcp\Pu\TestNewDoc');
        $suite->addTestSuite('Dcp\Pu\TestMultipleAlive');
        $suite->addTestSuite('Dcp\Pu\TestDoubleInherit');
        $suite->addTestSuite('Dcp\Pu\TestSetLogicalName');
        $suite->addTestSuite('Dcp\Pu\TestOooLayout');
        $suite->addTestSuite('Dcp\Pu\TestOooSimpleLayout');
        $suite->addTestSuite('Dcp\Pu\TestFolder');
        $suite->addTestSuite('Dcp\Pu\TestSearch');
        $suite->addTestSuite('Dcp\Pu\TestSearchDirective');
        $suite->addTestSuite('Dcp\Pu\TestSearchByFolder');
        $suite->addTestSuite('Dcp\Pu\TestSearchHighlight');
        $suite->addTestSuite('Dcp\Pu\TestSearchJoin');
        $suite->addTestSuite('Dcp\Pu\TestSearchFamilies');
        $suite->addTestSuite('Dcp\Pu\TestFormatCollection');
        $suite->addTestSuite('Dcp\Pu\TestFormatInvisibleCollection');
        $suite->addTestSuite('Dcp\Pu\TestSimpleQuery');
        $suite->addTestSuite('Dcp\Pu\TestProfil');
        $suite->addTestSuite('Dcp\Pu\TestTag');
        $suite->addTestSuite('Dcp\Pu\TestReport');
        $suite->addTestSuite('Dcp\Pu\TestLink');
        $suite->addTestSuite('Dcp\Pu\TestDocRel');
        $suite->addTestSuite('Dcp\Pu\TestUpdateAttribute');
        $suite->addTestSuite('Dcp\Pu\TestGetDocValue');
        $suite->addTestSuite('Dcp\Pu\TestSplitXmlDocument');
        $suite->addTestSuite('Dcp\Pu\TestImportFamily');
        $suite->addTestSuite('Dcp\Pu\TestImportFamilyProperty');
        $suite->addTestSuite('Dcp\Pu\TestImportWorkflow');
        $suite->addTestSuite('Dcp\Pu\TestImportXmlDocuments');
        $suite->addTestSuite('Dcp\Pu\TestImportDocuments');
        $suite->addTestSuite('Dcp\Pu\TestImportArchive');
        $suite->addTestSuite('Dcp\Pu\TestImportProfid');
        $suite->addTestSuite('Dcp\Pu\TestImportAccess');
        $suite->addTestSuite('Dcp\Pu\TestImportProfil');
        $suite->addTestSuite('Dcp\Pu\TestImportCsvDocuments');
        $suite->addTestSuite('Dcp\Pu\TestImportDocumentsExtra');
        $suite->addTestSuite('Dcp\Pu\TestImportCVDOC');
        $suite->addTestSuite('Dcp\Pu\TestExtendProfil');
        $suite->addTestSuite('Dcp\Pu\TestExportXml');
        $suite->addTestSuite('Dcp\Pu\TestExportCollection');
        $suite->addTestSuite('Dcp\Pu\TestExportRevision');
        $suite->addTestSuite('Dcp\Pu\TestGetParam');
        $suite->addTestSuite('Dcp\Pu\TestUsage');
        $suite->addTestSuite('Dcp\Pu\TestHelpUsage');
        $suite->addTestSuite('Dcp\Pu\TestParseFunction');
        $suite->addTestSuite('Dcp\Pu\TestParseMethod');
        $suite->addTestSuite('Dcp\Pu\TestExportCsv');
        $suite->addTestSuite('Dcp\Pu\TestMask');
        $suite->addTestSuite('Dcp\Pu\TestVolatileParam');
        $suite->addTestSuite('Dcp\Pu\TestApplicationParameters');
        $suite->addTestSuite('Dcp\Pu\TestApplicationParameterManeger');
        $suite->addTestSuite('Dcp\Pu\TestParameterManager');
        $suite->addTestSuite('Dcp\Pu\TestVaultDiskStorage');
        $suite->addTestSuite('Dcp\Pu\TestAutoloader');
        $suite->addTestSuite('Dcp\Pu\TestGettext');
        $suite->addTestSuite('Dcp\Pu\TestFdlGen');
        $suite->addTestSuite('Dcp\Pu\TestDir');
        $suite->addTestSuite('Dcp\Pu\TestDSearch');
        $suite->addTestSuite('Dcp\Pu\TestDcpMailMessage');
        $suite->addTestSuite('Dcp\Pu\TestAffect');
        $suite->addTestSuite('Dcp\Pu\TestDocVaultIndex');
        $suite->addTestSuite('Dcp\Pu\TestHtmlclean');
        // ...
        return $suite;
    }
}
