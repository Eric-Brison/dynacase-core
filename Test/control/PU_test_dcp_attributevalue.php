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

class TestAttributeValue extends TestCaseDcpCommonFamily
{
    public $famName = "TST_FAMSETVALUE";
    /**
     * import TST_FAMSETVALUE family
     * @static
     * @return string
     */
    protected static function getCommonImportFile()
    {
        return "PU_data_dcp_setvaluefamily.ods";
    }
    /**
     * @dataProvider goodValues
     */
    public function testGoodSetValue($attrid, $value, $converted = false)
    {
        $d = createDoc(self::$dbaccess, $this->famName);
        $this->assertTrue(is_object($d) , "cannot create TST_FAMSETVALUE document");
        
        $err = $d->setValue($attrid, $value);
        $this->assertEmpty($err, sprintf("setvalue error : %s", $err));
        if ($converted === false) $converted = $value;
        
        $this->assertEquals($converted, $d->getValue($attrid) , "setvalue / getvalue $attrid : not the same");
        $err = $d->store();
        $this->assertEmpty($err, sprintf("store error : %s", $err));
        return $d;
    }
    /**
     * @dataProvider wrongValues
     */
    public function testWrongSetValue($attrid, $value)
    {
        $d = createDoc(self::$dbaccess, $this->famName);
        $this->assertTrue(is_object($d) , "cannot create TST_FAMSETVALUE document");
        
        $err = $d->setValue($attrid, $value);
        $this->assertNotEmpty($err, sprintf("setvalue error : %s", $err));
        $this->assertEmpty($d->getValue($attrid));
        
        return $d;
    }
    /**
     * @dataProvider dataArraySetValue
     */
    public function testArraySetValue(array $values, $expectedCount, array $secondValues = array() , $secondCount = 0)
    {
        $d = createDoc(self::$dbaccess, $this->famName);
        $this->assertTrue(is_object($d) , "cannot create TST_FAMSETVALUE document");
        $err = '';
        foreach ($values as $aid => $value) {
            $err.= $d->setValue($aid, $value);
        }
        $this->assertEmpty($err, sprintf("setvalue error : %s", $err));
        
        foreach ($values as $aid => $value) {
            $this->assertEquals($expectedCount, count($d->getTValue($aid)) , sprintf("count for %s incorrect %d <> %d", $aid, $expectedCount, count($d->getTValue($aid))));
        }
        
        if ($secondValues) {
            foreach ($secondValues as $aid => $value) {
                $err.= $d->setValue($aid, $value);
            }
            $this->assertEmpty($err, sprintf("setvalue error : %s", $err));
            
            foreach ($secondValues as $aid => $value) {
                $oa = $d->getAttribute($aid);
                $this->assertEquals($secondCount, count($d->getTValue($aid)) , sprintf("second count for %s incorrect %d <> %d : %s", $aid, $secondCount, count($d->getTValue($aid)) , print_r($d->getAValues($oa->fieldSet->id) , true)));
            }
        }
        return $d;
    }
    /**
     * @dataProvider dataOldValue
     */
    public function testOldValue(array $before, array $after, array $notchanged)
    {
        $d = createDoc(self::$dbaccess, $this->famName);
        $this->assertTrue(is_object($d) , "cannot create TST_FAMSETVALUE document");
        $err = '';
        foreach ($before as $aid => $value) {
            $err.= $d->setValue($aid, $value);
        }
        $d->store();
        // new instance
        $d = new_doc(self::$dbaccess, $d->id);
        $this->assertEmpty($err, sprintf("before setvalue error : %s", $err));
        foreach ($after as $aid => $value) {
            $err.= $d->setValue($aid, $value);
        }
        $this->assertEmpty($err, sprintf("after setvalue error : %s", $err));
        
        foreach ($notchanged as $aid => $value) {
            $this->assertEquals($value, $d->getOldValue($aid) , "wrong old value $aid" . print_r($d->getValues() , true));
        }
    }
    public function dataOldValue()
    {
        return array(
            
            array(
                "before" => array(
                    "tst_title" => "T3",
                    "tst_int" => 2,
                    "tst_date" => '2012-01-30',
                    "tst_docids" => array() ,
                    "tst_coltext" => array() ,
                    "tst_coldate" => array() ,
                    "tst_colint" => array()
                ) ,
                "after" => array(
                    "tst_title" => "T2",
                    "tst_int" => 2,
                    "tst_date" => '2012-01-30',
                    "tst_docids" => array() ,
                    "tst_coltext" => array() ,
                    "tst_coldate" => array()
                ) ,
                "cnanged" => array(
                    "tst_title" => "T3",
                    "tst_int" => null,
                    "tst_date" => null,
                    "tst_docids" => null,
                    "tst_coltext" => null,
                    "tst_coldate" => null,
                    "tst_colint" => null,
                )
            ) ,
            array(
                "before" => array(
                    "tst_title" => "T1",
                    "tst_int" => 2,
                    "tst_date" => '2012-01-30',
                    "tst_docids" => array(
                        11,
                        9
                    ) ,
                    "tst_coltext" => array(
                        "Un",
                        "Deux"
                    ) ,
                    "tst_coldate" => array(
                        "2012-02-20",
                        "2012-03-26"
                    ) ,
                    "tst_colint" => array(
                        0,
                        1
                    )
                ) ,
                "after" => array(
                    "tst_title" => "T1",
                    "tst_int" => 2,
                    "tst_date" => '30/01/2012',
                    "tst_docids" => array(
                        11,
                        9
                    ) ,
                    "tst_coltext" => array(
                        "Un",
                        "Deux"
                    )
                ) ,
                "cnanged" => array(
                    "tst_title" => null,
                    "tst_int" => null,
                    "tst_date" => null,
                    "tst_docids" => null,
                    "tst_coltext" => null,
                    "tst_coldate" => null,
                    "tst_colint" => null,
                )
            ) ,
            array(
                "before" => array(
                    "tst_title" => "T1",
                    "tst_int" => 2,
                    "tst_date" => '2012-01-30',
                    "tst_docids" => array(
                        11,
                        9
                    ) ,
                    "tst_coltext" => array(
                        "Un",
                        "Deux"
                    ) ,
                    "tst_coldate" => array(
                        "2012-02-20",
                        "2012-03-26"
                    ) ,
                    "tst_colint" => array(
                        0,
                        1
                    )
                ) ,
                "after" => array(
                    "tst_title" => "T1",
                    "tst_int" => 3,
                    "tst_date" => '31/01/2012',
                    "tst_docids" => array(
                        11,
                        9
                    ) ,
                    "tst_coltext" => array(
                        "Un",
                        "Deux",
                        "Trois"
                    )
                ) ,
                "cnanged" => array(
                    "tst_title" => null,
                    "tst_int" => 2,
                    "tst_date" => '2012-01-30',
                    "tst_docids" => null,
                    "tst_coltext" => "Un\nDeux",
                    "tst_coldate" => null,
                    "tst_colint" => null,
                )
            )
        );
    }
    public function dataArraySetValue()
    {
        return array(
            array(
                array(
                    "tst_coltext" => array(
                        "Un",
                        "Deux",
                        "Trois"
                    ) ,
                    "tst_colint" => array(
                        1,
                        2,
                        3
                    ) ,
                    "tst_coldate" => array(
                        "2012-06-20",
                        "2012-06-21",
                        "2012-06-22"
                    )
                ) ,
                3
            ) ,
            array(
                array(
                    "tst_coltext" => array(
                        "Un",
                        "Deux"
                    ) ,
                    "tst_colint" => array(
                        1
                    ) ,
                    "tst_coldate" => array(
                        "2012-06-20",
                        "2012-06-20"
                    )
                ) ,
                2
            ) ,
            array(
                array(
                    "tst_coltext" => array(
                        "Un",
                        "Deux"
                    ) ,
                    "tst_colint" => array() ,
                    "tst_coldate" => array(
                        "2012-06-20",
                        "2012-06-20"
                    )
                ) ,
                2
            ) ,
            
            array(
                array(
                    "tst_coltext" => array() ,
                    "tst_colint" => array() ,
                    "tst_coldate" => array(
                        "2012-06-20",
                        "2012-06-20"
                    )
                ) ,
                2
            ) ,
            array(
                array(
                    "tst_coltext" => array(
                        "Un",
                        "Deux"
                    ) ,
                    "tst_colint" => array() ,
                    "tst_coldate" => array(
                        "2012-06-20",
                        "2012-06-20"
                    )
                ) ,
                2,
                array(
                    "tst_coltext" => array(
                        "Un"
                    ) ,
                    "tst_colint" => array() ,
                    "tst_coldate" => array(
                        "2012-06-20"
                    )
                ) ,
                1
            ) ,
            array(
                array(
                    "tst_coltext" => array(
                        "Un",
                        "Deux",
                        "Trois"
                    ) ,
                    "tst_colint" => array(
                        1,
                        2,
                        3
                    ) ,
                    "tst_coldate" => array(
                        "2012-06-20",
                        "2012-06-21",
                        "2012-06-22"
                    )
                ) ,
                3,
                array(
                    "tst_coltext" => array(
                        "Un",
                        "Deux"
                    ) ,
                    "tst_colint" => array(
                        1,
                        2
                    ) ,
                    "tst_coldate" => array(
                        "2012-06-20",
                        "2012-06-21"
                    )
                ) ,
                2
            ) ,
            array(
                array(
                    "tst_coltext" => array(
                        "Un",
                        "Deux",
                        "Trois"
                    ) ,
                    "tst_colint" => array(
                        1,
                        2,
                        3
                    ) ,
                    "tst_coldate" => array(
                        "2012-06-20",
                        "2012-06-21",
                        "2012-06-22"
                    )
                ) ,
                3,
                array(
                    "tst_coltext" => array(
                        "Un"
                    ) ,
                    "tst_colint" => array(
                        1
                    ) ,
                    "tst_coldate" => array(
                        "2012-06-20"
                    )
                ) ,
                1
            ) ,
            array(
                array(
                    "tst_coltext" => array(
                        "Un",
                        "Deux",
                        "Trois"
                    ) ,
                    "tst_colint" => array(
                        1,
                        2,
                        3
                    ) ,
                    "tst_coldate" => array(
                        "2012-06-20",
                        "2012-06-21",
                        "2012-06-22"
                    )
                ) ,
                3,
                array(
                    "tst_coltext" => array() ,
                    "tst_colint" => array() ,
                    "tst_coldate" => array()
                ) ,
                0
            )
        );
    }
    
    public function goodValues()
    {
        $iso = (getLcDate() == 'iso');
        return array(
            array(
                'TST_INT',
                3
            ) ,
            array(
                'TST_INT', -698
            ) ,
            array(
                'TST_TITLE',
                'hello world'
            ) ,
            array(
                'TST_DATE',
                '20/11/2011',
                $iso ? '2011-11-20' : "20/11/2011"
            ) ,
            array(
                'TST_DATE',
                '2011-11-21',
                $iso ? '2011-11-21' : "21/11/2011"
            ) ,
            array(
                'TST_DOUBLE',
                '3.34'
            ) ,
            array(
                'TST_DOUBLE',
                '3,34',
                '3.34'
            ) ,
            array(
                'TST_TIME',
                '12:34'
            ) ,
            array(
                'TST_TIMESTAMP',
                '2011-11-21 12:34',
                $iso ? '2011-11-21 12:34' : '21/11/2011 12:34'
            ) ,
            array(
                'TST_TIMESTAMP',
                '2011-11-21T12:34',
                $iso ? '2011-11-21T12:34' : '21/11/2011T12:34'
            )
        );
    }
    
    public function wrongValues()
    {
        return array(
            array(
                'TST_INT',
                'a'
            ) ,
            array(
                'TST_INT',
                '123 34'
            ) ,
            array(
                'TST_TIME',
                '12'
            ) ,
            array(
                'TST_DATE',
                'a'
            ) ,
            array(
                'TST_DATE',
                '2001-65-54'
            ) ,
            array(
                'TST_TIMESTAMP',
                'a'
            )
        );
    }
}
?>