<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package DCP
*/

namespace PU;

require_once 'PU_testcase_dcp.php';

class TestExportCsv extends TestCaseDcpCommonFamily
{
    static function getCommonImportFile()
    {
        return array(
            "PU_data_dcp_exportfamilycsv.ods",
            "PU_data_dcp_exportdoccsv.ods"
        );
    }
    /**
     * Test that exported documents have no param columns
     * @param array $data test specification
     * @dataProvider dataExportNoParam
     */
    public function testExportNoParam($data)
    {
        include_once ('FDL/exportfld.php');
        
        foreach (array(
            'export:doc',
            'expect:no:order'
        ) as $key) {
            if (!isset($data[$key])) {
                throw new \Exception(sprintf("Missing key '%s' in test data."));
            }
        }
        /* doc */
        $doc = new_Doc(self::$dbaccess, $data['export:doc']);
        if (!$doc->isAlive()) {
            throw new \Exception(sprintf("Could not get document with id '%s'.", $data['export:doc']));
        }
        /* fout */
        $tmpfile = tempnam(getTmpDir() , 'TST_EXPORT_PARAM');
        if ($tmpfile === false) {
            throw new \Exception(sprintf("Could not create temporary file in '%s'.", getTmpDir()));
        }
        $fout = fopen($tmpfile, 'w');
        if ($fout === false) {
            throw new \Exception(sprintf("Could not create temporary file '%s'.", $tmpfile));
        }
        /* ef */
        $ef = array();
        /* wprof */
        $wprof = false;
        /* wfile */
        $wfile = false;
        /* wident */
        $wident = true;
        /* wutf8 */
        $wutf8 = true;
        /* nopref */
        $nopref = true;
        /* eformat */
        $eformat = 'I';
        
        exportonedoc($doc, $ef, $fout, $wprof, $wfile, $wident, $wutf8, $nopref, $eformat);
        
        fclose($fout);
        
        $out = file_get_contents($tmpfile);
        $lines = preg_split("/\n/", $out);
        foreach ($lines as & $line) {
            if (!preg_match('/^ORDER;/', $line)) {
                continue;
            }
            foreach ($data['expect:no:order'] as $column) {
                $match = preg_match(sprintf('/;%s;/', preg_quote($column, '/')) , $line);
                $this->assertTrue(($match <= 0) , sprintf("Found param '%s' in ORDER line '%s'.", $column, $line));
            }
        }
        unset($line);
        
        unlink($tmpfile);
    }
    public function dataExportNoParam()
    {
        return array(
            array(
                array(
                    "export:doc" => "TST_EXPORT_PARAM_01",
                    "expect:no:order" => array(
                        "a_param_text"
                    )
                )
            )
        );
    }
}
?>