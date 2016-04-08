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
require_once 'EXTERNALS/fdl.php';
class TestLFamily extends TestCaseDcpCommonFamily
{
    public static function getCommonImportFile()
    {
        setLanguage("fr_FR");
        return "PU_data_dcp_lfamily.ods";
    }
    /**
     * @dataProvider dataWithoutDiacriticLFamily
     */
    public function testWithoutDiacriticLFamily($searchKey, $expectedResultCount)
    {
        $r = lfamily(self::$dbaccess, "TST_LFAMILY", $searchKey, 0, array() , "id", false);
        $this->assertEquals($expectedResultCount, count($r) , sprintf("Return %s", print_r($r, true)));
    }
    /**
     * @dataProvider dataTitleLFamily
     */
    public function testTitleLFamily($searchKey, array $expectedTitles)
    {
        $r = lfamily(self::$dbaccess, "TST_LFAMILY", $searchKey, 0, array() , "id", false);
        $titles = array();
        $this->assertTrue(is_array($r) , sprintf("Return %s", print_r($r, true)));
        foreach ($r as $k => $possibilities) {
            $titles[] = $possibilities[2];
            $this->assertTrue(in_array($possibilities[2], $expectedTitles));
        }
    }
    
    public function dataTitleLFamily()
    {
        return array(
            array(
                "cornichon",
                array(
                    "Cornichon"
                )
            ) ,
            array(
                "scorsonére",
                array(
                    "scorsonère"
                )
            ) ,
            array(
                "a",
                array(
                    "pastèque",
                    "pâtisson",
                    "maïs doux",
                    "épinard",
                    "châtaigne",
                    "pomme de terre française",
                    "Kvitkål",
                    "pequeños dados"
                )
            )
        );
    }
    public function dataWithoutDiacriticLFamily()
    {
        return array(
            array(
                "cornichon",
                1
            ) ,
            array(
                "scorsonère",
                1
            ) ,
            array(
                "scorsonere",
                1
            ) ,
            array(
                "pastèque",
                1
            ) ,
            array(
                "pâtisson",
                1
            ) ,
            array(
                "nombril de Vénus",
                1
            ) ,
            array(
                "maïs doux",
                1
            ) ,
            array(
                "niébé",
                1
            ) ,
            array(
                "épinard",
                1
            ) ,
            array(
                "pomme de terre française",
                1
            ) ,
            array(
                "Bønner",
                1
            ) ,
            array(
                "Kvitkål",
                1
            ) ,
            array(
                "pequeños dados",
                1
            ) ,
            array(
                "pasteque",
                1
            ) ,
            array(
                "patisson",
                1
            ) ,
            array(
                "nombril de venus",
                1
            ) ,
            array(
                "maïs doux",
                1
            ) ,
            array(
                "niebe",
                1
            ) ,
            array(
                "épinard",
                1
            ) ,
            array(
                "pomme de terre francaise",
                1
            ) ,
            array(
                "Bonner",
                1
            ) ,
            array(
                "Kvitkal",
                1
            ) ,
            array(
                "pequenos dados",
                1
            ) ,
            array(
                "a",
                8
            ) ,
            array(
                "n",
                10
            )
        );
    }
}
?>