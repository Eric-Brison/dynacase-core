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

require_once 'PU_testcase_dcp_commonfamily.php';

class TestImportArchive extends TestCaseDcpCommonFamily
{
    
    protected static function getCommonImportFile()
    {
        return 'PU_dcp_data_archivefamily.csv';
    }
    /**
     * @dataProvider dataDocumentFiles
     */
    public function testExecuteImportArchive($archiveFile, $resultDocuments)
    {
        
        $oImport = new \ImportDocument();
        $onlyAnalyze = false;
        $archive = true;
        $oImport->importDocuments($this->getAction() , $archiveFile, $onlyAnalyze, $archive);
        $err = $oImport->getErrorMessage();
        
        $this->assertEmpty($err, sprintf("import error %s", $err));
        foreach ($resultDocuments as $expected) {
            $s = new \SearchDoc('', $expected["family"]);
            $s->addFilter("title = '%s'", $expected["title"]);
            $count = $s->onlyCount();
            $this->assertGreaterThan(0, $count, sprintf("document %s not imported from archive %s", $expected["title"], $archiveFile));
        }
    }
    
    public function dataDocumentFiles()
    {
        return array(
            array(
                "./DCPTEST/PU_dcp_data_archiveimage.zip",
                array(
                    array(
                        "family" => "IMAGE",
                        "title" => "testdcp1.png"
                    ) ,
                    array(
                        "family" => "IMAGE",
                        "title" => "testdcp2.png"
                    )
                )
            ) ,
            array(
                "./DCPTEST/PU_dcp_data_archiveimage.tgz",
                array(
                    array(
                        "family" => "IMAGE",
                        "title" => "testdcp1.png"
                    ) ,
                    array(
                        "family" => "IMAGE",
                        "title" => "testdcp2.png"
                    ) ,
                    array(
                        "family" => "FILE",
                        "title" => "testdcp3.txt"
                    )
                )
            ) ,
            array(
                "./DCPTEST/PU_dcp_data_archivefile.zip",
                array(
                    array(
                        "family" => "TST_ARCHFILE",
                        "title" => "Fichier un"
                    ) ,
                    array(
                        "family" => "TST_ARCHFILE",
                        "title" => "Fichier deux"
                    ) ,
                    array(
                        "family" => "TST_ARCHFILES",
                        "title" => "Fichier un et deux"
                    )
                )
            ) ,
        );
    }
}
