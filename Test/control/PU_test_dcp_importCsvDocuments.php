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

require_once 'PU_testcase_dcp.php';

class TestImportCsvDocuments extends TestCaseDcp
{
    /**
     * @param $fileName
     * @param $separator
     * @param $enclosure
     * @param $expected
     * @throws \Dcp\Exception
     * @dataProvider dataImportCsvFamily
     */
    public function testImportCsvFamily($fileName, $separator, $enclosure, $famName, $expected)
    {
        $oImport = new \ImportDocument();
        $oImport->setCsvOptions($separator, $enclosure);
        $oImport->importDocuments(self::getAction() , "DCPTEST/" . $fileName);
        $err = $oImport->getErrorMessage();
        $this->assertEmpty($err, "import family error : $err");
        $f = new_doc('', $famName);
        $this->assertTrue($f->isAlive() , sprintf("family %s not found", $famName));
        $this->assertEquals($expected["title"], $f->getTitle() , "incorrect family title");
        foreach ($expected["alabel"] as $aid => $elabel) {
            
            $this->assertEquals($elabel, $f->getLabel($aid) , "incorrect attribute label");
        }
        foreach ($expected["doc"] as $k => $v) {
            
            $d = new_doc('', $v["name"]);
            $this->assertTrue($d->isAlive() , sprintf("document %s not found", $v["name"]));
            foreach ($v["values"] as $aid => $aval) {
                $this->assertEquals($aval, $d->getRawValue($aid) , sprintf("incorrect attribute [%s] value : %s ", $aid, print_r($d->getValues() , true)));
            }
        }
    }
    /**
     * @param $fileName
     * @param $expectedSeparator
     * @param $expectedEnclosure
     * @dataProvider dataDetectCsvOptions
     */
    public function testDetectCsvOptions($fileName, $expectedSeparator, $expectedEnclosure)
    {
        $oImport = new \importDocumentDescription("DCPTEST/" . $fileName);
        $options = $oImport->setCsvOptions('auto', 'auto');
        
        $this->assertEquals($expectedSeparator, $options["separator"], "incorrect csv separator");
        $this->assertEquals($expectedEnclosure, $options["enclosure"], "incorrect csv enclosure");
    }
    
    public function dataDetectCsvOptions()
    {
        return array(
            array(
                "PU_data_dcp_goodfamilyforcsvcommadoublequote1.csv",
                ",",
                '"'
            ) ,
            array(
                "PU_data_dcp_goodfamilyforcsvsemicolonsinglequote1.csv",
                ";",
                "'"
            ) ,
            array(
                "PU_data_dcp_goodfamilyforcsvcommadoublequote2.csv",
                ",",
                '"'
            ) ,
            array(
                "PU_data_dcp_goodfamilyforcsvsemicolonsinglequote2.csv",
                ";",
                "'"
            ) ,
            array(
                "PU_data_dcp_goodfamilyforcsvsemicolondoublequote1.csv",
                ";",
                '"'
            ) ,
            array(
                "PU_data_dcp_goodfamilyforcsvsemicolondoublequote2.csv",
                ";",
                '"'
            ) ,
            array(
                "PU_data_dcp_goodfamilyforcsvsemicolonsinglequote2iso.csv",
                ";",
                "'"
            ) ,
            array(
                "PU_data_dcp_goodfamilyforcsvsemicolon.csv",
                ";",
                ""
            )
        );
    }
    public function dataImportCsvFamily()
    {
        return array(
            
            array(
                "file" => "PU_data_dcp_goodfamilyforcsvsemicolon.csv",
                "separator" => ";",
                "enclosure" => '',
                "famname" => "TST_GOODFAMIMPCSVSEMICOLON
                ",
                "expect" => array(
                    "title" => 'Test Famille, "Csv"',
                    "alabel" => array(
                        "tst_text" => 'Texte "principal"',
                        "tst_date" => "Date, 'principale'"
                    ) ,
                    "doc" => array(
                        array(
                            "name" => "TST_CVSSEMICOLON1",
                            "values" => array(
                                "tst_title" => "Hello",
                                "tst_text" => "The world",
                                "tst_coltext" => "Un\nDeux",
                                "tst_coldate" => "2012-02-17\n2013-06-12"
                            )
                        )
                    )
                )
            ) ,
            array(
                "file" => "PU_data_dcp_goodfamilyforcsvcommadoublequote1.csv",
                "separator" => ",",
                "enclosure" => '"',
                "famname" => "TST_GOODFAMIMPCSVCOMMA1",
                "expect" => array(
                    "title" => 'Test Famille, "Csv"',
                    "alabel" => array(
                        "tst_text" => 'Texte "principal"',
                        "tst_date" => "Date, 'principale'"
                    ) ,
                    "doc" => array(
                        array(
                            "name" => "TST_CVSCOMMA11",
                            "values" => array(
                                "tst_title" => "Hello",
                                "tst_text" => "The world",
                                "tst_coltext" => "Un\nDeux",
                                "tst_coldate" => "2012-02-17\n2013-06-12"
                            )
                        ) ,
                        array(
                            "name" => "TST_CVSCOMMA12",
                            "values" => array(
                                "tst_title" => "Virgule , et ;",
                                "tst_text" => 'The "world" end "earth"',
                                "tst_coltext" => "Un\nDeux",
                                "tst_coldate" => "2012-02-18\n2013-10-12"
                            )
                        )
                    )
                )
            ) ,
            
            array(
                "file" => "PU_data_dcp_goodfamilyforcsvcommadoublequote2.csv",
                "separator" => ",",
                "enclosure" => '"',
                "famname" => "TST_GOODFAMIMPCSVCOMMA2",
                "expect" => array(
                    "title" => 'Test Famille, "Csv"',
                    "alabel" => array(
                        "tst_text" => 'Texte "principal"',
                        "tst_date" => "Date, 'principale'"
                    ) ,
                    "doc" => array(
                        array(
                            "name" => "TST_CVSCOMMA21",
                            "values" => array(
                                "tst_title" => "Hello",
                                "tst_text" => "The world",
                                "tst_coltext" => "Un\nDeux",
                                "tst_coldate" => "2012-02-17\n2013-06-12"
                            )
                        ) ,
                        array(
                            "name" => "TST_CVSCOMMA22",
                            "values" => array(
                                "tst_title" => "Virgule , et ;",
                                "tst_text" => 'The "world" end "earth"',
                                "tst_coltext" => "Un\nDeux",
                                "tst_coldate" => "2012-02-18\n2013-10-12"
                            )
                        )
                    )
                )
            ) ,
            array(
                "file" => "PU_data_dcp_goodfamilyforcsvsemicolonsinglequote1.csv",
                "separator" => ";",
                "enclosure" => "'",
                "famname" => "TST_GOODFAMIMPCSVSEMICOLON1",
                "expect" => array(
                    "title" => 'Test Famille, "Csv"',
                    "alabel" => array(
                        "tst_text" => 'Texte "principal"',
                        "tst_date" => "Date, 'principale'"
                    ) ,
                    "doc" => array(
                        array(
                            "name" => "TST_CSVSEMICOLON11",
                            "values" => array(
                                "tst_title" => "Hello",
                                "tst_text" => "The world",
                                "tst_coltext" => "Un\nDeux",
                                "tst_coldate" => "2012-02-17\n2013-06-12"
                            )
                        ) ,
                        array(
                            "name" => "TST_CSVSEMICOLON12",
                            "values" => array(
                                "tst_title" => "Virgule , et ;",
                                "tst_text" => "L'être ou le n°3\nAccentué : ça c'est fait",
                                "tst_coltext" => "Un\nDeux\nTrois",
                                "tst_coldate" => "2012-02-18\n2013-10-12\n"
                            )
                        ) ,
                        array(
                            "name" => "TST_CSVSEMICOLON13",
                            "values" => array(
                                "tst_title" => 'quote \' double " point-virgule ; et virgule ,',
                                "tst_text" => "The \"world\" \nis beautiful\nisn't it",
                                "tst_coltext" => "Un\nDeux\nTrois",
                                "tst_coldate" => "2012-02-18\n2013-10-12\n"
                            )
                        )
                    )
                )
            ) ,
            array(
                "file" => "PU_data_dcp_goodfamilyforcsvsemicolonsinglequote2.csv",
                "separator" => ";",
                "enclosure" => "'",
                "famname" => "TST_GOODFAMIMPCSVSEMICOLON2",
                "expect" => array(
                    "title" => 'Test Famille, "Csv"',
                    "alabel" => array(
                        "tst_text" => 'Texte "principal"',
                        "tst_date" => "Date, 'principale'"
                    ) ,
                    "doc" => array(
                        array(
                            "name" => "TST_CSVSEMICOLON21",
                            "values" => array(
                                "tst_title" => "Hello",
                                "tst_text" => "The world",
                                "tst_coltext" => "Un\nDeux",
                                "tst_coldate" => "2012-02-17\n2013-06-12"
                            )
                        ) ,
                        array(
                            "name" => "TST_CSVSEMICOLON22",
                            "values" => array(
                                "tst_title" => "Virgule , et ;",
                                "tst_text" => "L'être ou le n°3\nAccentué : ça c'est fait",
                                "tst_coltext" => "Un\nDeux\nTrois",
                                "tst_coldate" => "2012-02-18\n2013-10-12\n"
                            )
                        ) ,
                        array(
                            "name" => "TST_CSVSEMICOLON23",
                            "values" => array(
                                "tst_title" => 'quote \' double " point-virgule ; et virgule ,',
                                "tst_text" => "The \"world\" \nis beautiful\nisn't it",
                                "tst_coltext" => "Un\nDeux\nTrois",
                                "tst_coldate" => "2012-02-18\n2013-10-12\n"
                            )
                        )
                    )
                )
            ) ,
            array(
                "file" => "PU_data_dcp_goodfamilyforcsvsemicolonsinglequote2iso.csv",
                "separator" => ";",
                "enclosure" => "'",
                "famname" => "TST_GOODFAMIMPCSVSEMICOLON5",
                "expect" => array(
                    "title" => 'Test Famille, "Csv"',
                    "alabel" => array(
                        "tst_text" => 'Texte "principal"',
                        "tst_date" => "Date, 'principale'"
                    ) ,
                    "doc" => array(
                        array(
                            "name" => "TST_CSVSEMICOLON51",
                            "values" => array(
                                "tst_title" => "Hello",
                                "tst_text" => "The world",
                                "tst_coltext" => "Un\nDeux",
                                "tst_coldate" => "2012-02-17\n2013-06-12"
                            )
                        ) ,
                        array(
                            "name" => "TST_CSVSEMICOLON52",
                            "values" => array(
                                "tst_title" => "Virgule , et ;",
                                "tst_text" => "L'être ou le n°3\nAccentué : ça c'est fait",
                                "tst_coltext" => "Un\nDeux\nTrois",
                                "tst_coldate" => "2012-02-18\n2013-10-12\n"
                            )
                        ) ,
                        array(
                            "name" => "TST_CSVSEMICOLON53",
                            "values" => array(
                                "tst_title" => 'quote \' double " point-virgule ; et virgule ,',
                                "tst_text" => "The \"world\" \nis beautiful\nisn't it",
                                "tst_coltext" => "Un\nDeux\nTrois",
                                "tst_coldate" => "2012-02-18\n2013-10-12\n"
                            )
                        )
                    )
                )
            ) ,
            array(
                "file" => "PU_data_dcp_goodfamilyforcsvsemicolondoublequote1.csv",
                "separator" => ";",
                "enclosure" => '"',
                "famname" => "TST_GOODFAMIMPCSVSEMICOLON3",
                "expect" => array(
                    "title" => 'Test Famille, "Csv"',
                    "alabel" => array(
                        "tst_text" => 'Texte "principal"',
                        "tst_date" => "Date, 'principale'"
                    ) ,
                    "doc" => array(
                        array(
                            "name" => "TST_CSVSEMICOLON31",
                            "values" => array(
                                "tst_title" => "Hello",
                                "tst_text" => "The world",
                                "tst_coltext" => "Un\nDeux",
                                "tst_coldate" => "2012-02-17\n2013-06-12"
                            )
                        ) ,
                        array(
                            "name" => "TST_CSVSEMICOLON32",
                            "values" => array(
                                "tst_title" => "Virgule , et ;",
                                "tst_text" => "L'être ou le n°3\nAccentué : ça c'est fait",
                                "tst_coltext" => "Un\nDeux\nTrois",
                                "tst_coldate" => "2012-02-18\n2013-10-12\n"
                            )
                        ) ,
                        array(
                            "name" => "TST_CSVSEMICOLON33",
                            "values" => array(
                                "tst_title" => 'quote \' double " point-virgule ; et virgule ,',
                                "tst_text" => "The \"world\" \nis beautiful\nisn't it",
                                "tst_coltext" => "Un\nDeux\nTrois",
                                "tst_coldate" => "2012-02-18\n2013-10-12\n"
                            )
                        )
                    )
                )
            ) ,
            array(
                "file" => "PU_data_dcp_goodfamilyforcsvsemicolondoublequote2.csv",
                "separator" => ";",
                "enclosure" => '"',
                "famname" => "TST_GOODFAMIMPCSVSEMICOLON4",
                "expect" => array(
                    "title" => 'Test Famille, "Csv"',
                    "alabel" => array(
                        "tst_text" => 'Texte "principal"',
                        "tst_date" => "Date, 'principale'"
                    ) ,
                    "doc" => array(
                        array(
                            "name" => "TST_CSVSEMICOLON41",
                            "values" => array(
                                "tst_title" => "Hello",
                                "tst_text" => "The world",
                                "tst_coltext" => "Un\nDeux",
                                "tst_coldate" => "2012-02-17\n2013-06-12"
                            )
                        ) ,
                        array(
                            "name" => "TST_CSVSEMICOLON42",
                            "values" => array(
                                "tst_title" => "Virgule , et ;",
                                "tst_text" => "L'être ou le n°3\nAccentué : ça c'est fait",
                                "tst_coltext" => "Un\nDeux\nTrois",
                                "tst_coldate" => "2012-02-18\n2013-10-12\n"
                            )
                        ) ,
                        array(
                            "name" => "TST_CSVSEMICOLON43",
                            "values" => array(
                                "tst_title" => 'quote \' double " point-virgule ; et virgule ,',
                                "tst_text" => "The \"world\" \nis beautiful\nisn't it",
                                "tst_coltext" => "Un\nDeux\nTrois",
                                "tst_coldate" => "2012-02-18\n2013-10-12\n"
                            )
                        )
                    )
                )
            ) ,
            array(
                "file" => "PU_data_dcp_goodfamilyforcsvcommai.csv",
                "separator" => ",",
                "enclosure" => "i",
                "famname" => "TST_GOODFAMIMPCSVSEMICOMMAI",
                "expect" => array(
                    "title" => 'Test Famille, "Csv"',
                    "alabel" => array(
                        "tst_text" => 'Texte "principal"',
                        "tst_date" => "Date, 'principale'"
                    ) ,
                    "doc" => array(
                        array(
                            "name" => "TST_CVSSEMICOMMAI1",
                            "values" => array(
                                "tst_title" => "Hello",
                                "tst_text" => "The i world",
                                "tst_coltext" => "i\nii\niii\niv",
                                "tst_coldate" => "2012-02-17\n2013-06-12\n2012-12-17\n2013-06-17"
                            )
                        ) ,
                        array(
                            "name" => "TST_CVSSEMICOMMAI2",
                            "values" => array(
                                "tst_title" => "Le monde des i's",
                                "tst_text" => "The \"i world\" \nis beautiful\nisn't it",
                                "tst_coltext" => "i\nii\niii",
                                "tst_coldate" => "2012-02-18\n2013-10-12\n2013-10-13"
                            )
                        )
                    )
                )
            ) ,
            array(
                "file" => "PU_data_dcp_goodfamilyforcsvcommadoublequote2iso.csv",
                "separator" => "auto",
                "enclosure" => 'auto',
                "famname" => "TST_GOODFAMIMPCSVCOMMA3",
                "expect" => array(
                    "title" => 'Test Famille, "Csv"',
                    "alabel" => array(
                        "tst_text" => 'Texte "principal"',
                        "tst_date" => "Date, 'principale'"
                    ) ,
                    "doc" => array(
                        array(
                            "name" => "TST_CVSCOMMA31",
                            "values" => array(
                                "tst_title" => "Hello",
                                "tst_text" => "Le monde à l'été",
                                "tst_coltext" => "Un\nDeux",
                                "tst_coldate" => "2012-02-17\n2013-06-12"
                            )
                        ) ,
                        array(
                            "name" => "TST_CVSCOMMA32",
                            "values" => array(
                                "tst_title" => "Virgule , et ;",
                                "tst_text" => 'The "world" end "earth"',
                                "tst_coltext" => "Un\nDeux",
                                "tst_coldate" => "2012-02-18\n2013-10-12"
                            )
                        )
                    )
                )
            ) ,
        );
    }
}
?>