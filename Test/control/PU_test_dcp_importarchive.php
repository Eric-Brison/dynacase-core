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

require_once 'PU_testcase_dcp.php';

class TestImportArchive extends TestCaseDcp
{
    /**
     * count error from import report message
     * @param $err
     * @return int|void
     */
    private function countErrors($err)
    {
        if (!$err) return 0;
        return count(explode("]\n[", $err));
    }
    /**
     * @dataProvider dataDocumentFiles
     */
    public function testImportArchive($archiveFile, $resultDocuments)
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
            )
        );
    }
}
?>