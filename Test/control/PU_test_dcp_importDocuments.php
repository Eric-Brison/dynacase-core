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

require_once 'PU_testcase_dcp_commonfamily.php';

class TestImportDocuments extends TestCaseDcpCommonFamily
{
    /**
     * import TST_FAMSETVALUE family
     * @static
     * @return string
     */
    protected static function getCommonImportFile()
    {
        return "PU_data_dcp_goodfamilyfordoc.ods";
    }
    /**
     * test import simple document
     * @dataProvider dataGoodDocFiles
     */
    public function testGoodImportDocument($documentFile, array $docNames)
    {
        $err = '';
        $d = createDoc(self::$dbaccess, "TST_GOODFAMIMPDOC");
        $this->assertTrue(is_object($d) , "cannot create TST_GOODFAMIMPDOC document");
        try {
            $this->importDocument($documentFile);
        }
        catch(\Exception $e) {
            $err = $e->getMessage();
        }
        $this->assertEmpty($err, "import error detected $err");
        $name = $docNames["famName"];
        $t = getTDoc(self::$dbaccess, $name);
        $this->assertArrayHasKey('id', $t, sprintf("cannot find %s document", $name));
        foreach ($docNames["expectValue"] as $aid => $expVal) {
            $this->assertEquals($expVal, $t[$aid]);
        }
    }
    /**
     * @dataProvider dataBadDocFiles
     * @---depends testGoodImportDocument
     *
     */
    public function testErrorImportDocument($familyFile, array $expectedErrors)
    {
        
        $err = '';
        try {
            $this->importDocument($familyFile);
        }
        catch(\Exception $e) {
            $err = $e->getMessage();
        }
        $this->assertNotEmpty($err, "no import error detected");
        if (!is_array($expectedErrors)) $expectedErrors = array(
            $expectedErrors
        );
        
        foreach ($expectedErrors as $expectedError) {
            $this->assertContains($expectedError, $err, sprintf("not the correct error reporting : %s", $err));
        }
    }
    /**
     * @dataProvider dataReturnOfImportDocument
     */
    public function testReturnOfImportDocument($data)
    {
        $this->requiresCoreParamEquals('CORE_LANG', 'fr_FR');
        
        $err = '';
        $cr = array();
        try {
            $cr = $this->importDocument($data['file']);
        }
        catch(\Exception $e) {
            $err = $e->getMessage();
        }
        $this->assertEmpty($err, sprintf("Import of '%s' returned with unexpected errors: %s", $data['file'], $err));
        
        foreach (array(
            'specmsg'
        ) as $prop) {
            if (isset($data[$prop])) {
                if (isset($data[$prop]['contains'])) {
                    foreach ($data[$prop]['contains'] as $string) {
                        $found = false;
                        foreach ($cr as $line) {
                            $msg = (isset($line[$prop]) ? $line[$prop] : '');
                            if (strpos($msg, $string) !== false) {
                                $found = true;
                            }
                        }
                        $this->assertTrue($found, sprintf("Expected string '%s' not found in '%s': %s", $string, $prop, var_export($cr, true)));
                    }
                }
                if (isset($data[$prop]['not:contains'])) {
                    foreach ($data[$prop]['not:contains'] as $string) {
                        $found = false;
                        foreach ($cr as $line) {
                            $msg = (isset($line[$prop]) ? $line[$prop] : '');
                            if (strpos($msg, $string) !== false) {
                                $found = true;
                            }
                        }
                        $this->assertFalse($found, sprintf("Non-expected string '%s' found in '%s': %s", $string, $prop, var_export($cr, true)));
                    }
                }
            }
        }
    }
    public function dataGoodDocFiles()
    {
        return array(
            array(
                "file" => "PU_data_dcp_importdocgood1.ods",
                "names" => array(
                    "famName" => "TST_GOOD1",
                    "expectValue" => array(
                        "tst_title" => "Test1",
                        "tst_number" => "20"
                    )
                )
            )
        );
    }
    
    public function dataBadDocFiles()
    {
        return array(
            array(
                "file" => "PU_data_dcp_importdocbad1.ods",
                "errors" => array(
                    "DOC0100",
                    "tst_number",
                    "DOC0002",
                    "NoFamily",
                    "DOC0003",
                    "Bad Family",
                    "DOC0004",
                    "DOC0005",
                    "UNKNOWFAMILY",
                    "DOC0006",
                    "NotFamily",
                    "Bad name",
                    "DOC0008",
                    "DIR",
                    "DOC0201",
                    "TST_BADINSERT",
                    "DOC0202",
                    "TST_UNKFOLDER"
                ) ,
            ) ,
            array(
                "file" => "PU_data_dcp_importdocbad2.ods",
                "errors" => array(
                    "ORDR0001",
                    "ORDR0002",
                    "TST_FOLDER1",
                    "ORDR0003",
                    "ORDR0006",
                    "TST_FAMUNK",
                    "ORDR0100",
                    "tst_unknow"
                )
            ) ,
            array(
                "file" => "PU_data_dcp_importdocbad3.ods",
                "errors" => array(
                    "KEYS0001",
                    "KEYS0002",
                    "TST_FOLDER1",
                    "KEYS0003",
                    "KEYS0006",
                    "TST_KEYFAMUNK",
                    "KEYS0100",
                    "tst_keyunknow",
                    "KEYS0101",
                    "KEYS0101",
                    "KEYS0102"
                )
            )
        );
    }
    public function dataReturnOfImportDocument()
    {
        return array(
            array(
                array(
                    "file" => "PU_data_dcp_importdocbad4.ods",
                    "specmsg" => array(
                        "contains" => array(
                            "Nom logique 'TST_GOODFAMIMPDOC_4' inconnu dans l'attribut 'tst_docid_1'",
                            "Nom logique 'TST_GOODFAMIMPDOC_4' inconnu dans l'attribut 'tst_docid_m'",
                            "Nom logique 'TST_GOODFAMIMPDOC_4' inconnu dans l'attribut 'tst_docid_x'"
                        ) ,
                        "not:contains" => array(
                            "Nom logique 'TST_GOODFAMIMPDOC_1' inconnu dans l'attribut 'tst_docid_",
                            "Nom logique 'TST_GOODFAMIMPDOC_3' inconnu dans l'attribut 'tst_docid_"
                        )
                    )
                )
            )
        );
    }
}
