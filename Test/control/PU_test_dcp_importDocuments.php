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
    public function testGoodImportDocument($documentFile, array $expects)
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
        
        foreach ($expects as $docNames) {
            $name = $docNames["docName"];
            $t = getTDoc(self::$dbaccess, $name);
            $this->assertArrayHasKey('id', $t, sprintf("cannot find %s document", $name));
            foreach ($docNames["expectValue"] as $aid => $expVal) {
                if ($expVal[0] === "*") {
                    $this->assertContains(substr($expVal, 1) , $t[$aid]);
                } else {
                    $this->assertEquals($expVal, $t[$aid]);
                }
            }
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
                array(
                    array(
                        "docName" => "TST_GOOD1",
                        "expectValue" => array(
                            "tst_title" => "Test1",
                            "tst_number" => "20"
                        )
                    ) ,
                    array(
                        "docName" => "TST_GOOD4",
                        "expectValue" => array(
                            "tst_title" => "Portez ce vieux whisky au juge blond qui fume sur son île intérieure, à côté de l'alcôve ovoïde, où les bûches se consument dans l'âtre, ce qui lui permet de penser à la cænogénèse de l'être dont il est question dans la cause ambiguë entendue à Moÿ, dans un capharnaüm qui, pense-t-il, diminue çà et là la qualité de son œuvre.",
                            "tst_number" => "-4"
                        )
                    )
                )
            ) ,
            array(
                "file" => "../PU_data_dcp_importdocgood2.zip",
                array(
                    array(
                        "docName" => "TST_GOOD2",
                        "expectValue" => array(
                            "tst_title" => "Dès Noël où un zéphyr haï me vêt de glaçons würmiens je dîne d’exquis rôtis de bœuf au kir à l’aÿ d’âge mûr & cætera",
                            "tst_number" => "9878",
                            "tst_date" => "1987-12-02"
                        )
                    ) ,
                    array(
                        "docName" => "TST_GOOD3",
                        "expectValue" => array(
                            "tst_title" => "Zéphir",
                            "tst_number" => "987",
                            "tst_date" => "2015-10-02",
                            "tst_file" => "*Zéphir.txt"
                        )
                    ) ,
                    array(
                        "docName" => "TST_GOOD4",
                        "expectValue" => array(
                            "tst_title" => "<Foo'Bar\"Baz&Buz>",
                            "tst_number" => "987",
                            "tst_date" => "2015-10-02",
                            "tst_file" => "*|<Foo'Bar\"Baz&Buz>.txt"
                        )
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
            ) ,
            array(
                "PU_data_dcp_importdocbad5.ods",
                array(
                    "Couldn't find end of Start Tag p",
                    "error parsing attribute name",
                    "Opening and ending tag mismatch: p and em"
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
