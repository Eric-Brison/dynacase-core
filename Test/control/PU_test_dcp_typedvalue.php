<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Pu;

use Dcp\ApiUsage\Exception;
/**
 * @author Anakeen
 * @package Dcp\Pu
 */
require_once 'PU_testcase_dcp_commonfamily.php';

class TestTypedValue extends TestCaseDcpCommonFamily
{
    public $famName = "TST_FAMGETTYPEDVALUE";
    /**
     * import TST_FAMSETVALUE family
     * @static
     * @return string
     */
    protected static function getCommonImportFile()
    {
        return "PU_data_dcp_gettypedvaluefamily.ods";
    }
    /**
     * @dataProvider dataGetAttributeValue
     */
    public function testGetAttributeValue($docName, array $expectedValues)
    {
        $d = new_doc(self::$dbaccess, $docName);
        $this->assertTrue($d->isAlive() , sprintf("cannot access %s document", $docName));
        foreach ($expectedValues as $attrid => $expectedValue) {
            $value = $d->getAttributeValue($attrid);
            $this->assertTrue($expectedValue === $value, sprintf('wrong value "%s" : expected %s, has %s', $attrid, $this->getDump($expectedValue) , $this->getDump($value, true)));
        }
    }
    /**
     * @dataProvider dataErrorGetAttributeValue
     */
    public function testErrorGetAttributeValue($docName, $attrid, $expectedErrorCode)
    {
        $d = new_doc(self::$dbaccess, $docName);
        $this->assertTrue($d->isAlive() , sprintf("cannot access %s document", $docName));
        try {
            $d->getAttributeValue($attrid);
        }
        catch(\Dcp\Exception $e) {
            $this->assertEquals($expectedErrorCode, $e->getDcpCode() , sprintf('"not correct code: %s"', $e->getMessage()));
        }
    }
    /**
     * @dataProvider dataErrorSetAttributeValue
     */
    public function testErrorSetAttributeValue($docName, $attrid, $value, $expectedErrorCode)
    {
        $d = new_doc(self::$dbaccess, $docName);
        $this->assertTrue($d->isAlive() , sprintf("cannot access %s document", $docName));
        try {
            $d->setAttributeValue($attrid, $value);
            $this->assertTrue(false, "no error detected need $expectedErrorCode");
        }
        catch(\Dcp\Exception $e) {
            $this->assertEquals($expectedErrorCode, $e->getDcpCode() , sprintf('"not correct code : %s"', $e->getMessage()));
            $this->log($e->getMessage());
        }
    }
    /**
     * @dataProvider dataGetRelationValues
     */
    public function testGetRelationValues($docName, array $expectedValues)
    {
        $d = new_doc(self::$dbaccess, $docName);
        $this->assertTrue($d->isAlive() , sprintf("cannot access %s document", $docName));
        foreach ($expectedValues as $attrid => $expectedValue) {
            $value = $d->getAttributeValue($attrid);
            // convert docName to docId
            $expectedDocId = $this->docNames2docIds($expectedValue);
            $this->assertTrue($expectedDocId === $value, sprintf('wrong value "%s" : expected %s, has %s \nRaw is :"%s"', $attrid, $this->getDump($expectedDocId) , $this->getDump($value, true) , $d->getRawValue($attrid)));
        }
    }
    /**
     * @dataProvider dataGetDateValues
     */
    public function testGetDateValues($docName, array $expectedValues)
    {
        $d = new_doc(self::$dbaccess, $docName);
        $this->assertTrue($d->isAlive() , sprintf("cannot access %s document", $docName));
        foreach ($expectedValues as $attrid => $expectedValue) {
            $value = $d->getAttributeValue($attrid);
            $stringDates = array();
            $oa = $d->getAttribute($attrid);
            switch ($oa->type) {
                case 'date':
                    $stringDates = $this->date2string($value, 'Y-m-d');
                    break;

                case 'timestamp':
                    $stringDates = $this->date2string($value, 'Y-m-d\TH:i:s');
                    break;
            }
            // convert docName to docId
            $this->assertTrue($stringDates === $expectedValue, sprintf('wrong value "%s" : expected %s, has %s \nRaw is :"%s"', $attrid, $this->getDump($expectedValue, true) , $this->getDump($stringDates) , $d->getRawValue($attrid)));
        }
    }
    /**
     * @dataProvider dataSetRelationValues
     */
    public function testSetRelationValues($docName, array $expectedValues)
    {
        $d = new_doc(self::$dbaccess, $docName);
        $this->assertTrue($d->isAlive() , sprintf("cannot access %s document", $docName));
        foreach ($expectedValues as $attrid => $newValue) {
            $d->setAttributeValue($attrid, $newValue);
        }
        foreach ($expectedValues as $attrid => $expectedValue) {
            $value = $d->getAttributeValue($attrid);
            // convert docName to docId
            $expectedDocId = $this->docNames2docIds($expectedValue);
            
            $this->assertTrue($expectedDocId === $value, sprintf('wrong value "%s" : expected %s, has %s \nRaw is :"%s"', $attrid, $this->getDump($expectedDocId) , $this->getDump($value, true) , $d->getRawValue($attrid)));
        }
    }
    /**
     * @dataProvider dataSetAndGetValues
     */
    public function testSetGetValues($docName, array $setValues, array $expectedValues)
    {
        $d = new_doc(self::$dbaccess, $docName);
        $this->assertTrue($d->isAlive() , sprintf("cannot access %s document", $docName));
        foreach ($setValues as $attrid => $newValue) {
            $d->setAttributeValue($attrid, $newValue);
        }
        $d->store(); // verify database record
        foreach ($expectedValues as $attrid => $expectedValue) {
            $oriValue = $value = $d->getAttributeValue($attrid);
            
            $oa = $d->getAttribute($attrid);
            switch ($oa->type) {
                case 'date':
                    $value = $this->date2string($value, 'Y-m-d');
                    break;

                case 'timestamp':
                    $value = $this->date2string($value, 'Y-m-d\TH:i:s');
                    break;
            }
            
            $this->assertTrue($expectedValue === $value, sprintf('wrong value "%s" : expected %s, has %s \nRaw is :"%s"', $attrid, $this->getDump($expectedValue) , $this->getDump($value, true) , $this->getDump($oriValue)));
        }
    }
    
    private function date2string($dates, $format)
    {
        if (is_array($dates)) {
            $stringDate = array();
            /**
             * @var \DateTime $aDate
             */
            foreach ($dates as $aDate) {
                if (is_array($aDate)) {
                    $Datess = array();
                    /**
                     * @var \DateTime $dates2
                     */
                    foreach ($aDate as $dates2) {
                        $Datess[] = $dates2 ? $dates2->format($format) : null;
                    }
                    $stringDate[] = $Datess;
                } else {
                    $stringDate[] = $aDate ? $aDate->format($format) : null;
                }
            }
        } else {
            /**
             * @var \DateTime $dates
             */
            $stringDate = $dates ? $dates->format($format) : null;
        }
        return $stringDate;
    }
    
    private function docNames2docIds($docNames)
    {
        if (is_array($docNames)) {
            $expectedDocId = array();
            foreach ($docNames as $docName) {
                if (is_array($docName)) {
                    $expectDocId2 = array();
                    foreach ($docName as $docName2) {
                        $expectDocId2[] = $docName2 ? getIdFromName(self::$dbaccess, $docName2) : null;
                    }
                    $expectedDocId[] = $expectDocId2;
                } else {
                    $expectedDocId[] = $docName ? getIdFromName(self::$dbaccess, $docName) : null;
                }
            }
        } else {
            $expectedDocId = $docNames ? getIdFromName(self::$dbaccess, $docNames) : null;
        }
        return $expectedDocId;
    }
    
    private function getDump($o)
    {
        ob_start();
        var_dump($o);
        return ob_get_clean();
    }
    /**
     * @dataProvider dataSetAttributeValue
     */
    public function testCreateSetAttributeValue(array $expectedValues)
    {
        $d = createDoc(self::$dbaccess, $this->famName);
        $this->assertTrue(is_object($d) , sprintf("cannot create %s document", $this->famName));
        foreach ($expectedValues as $attrid => $value) {
            $d->setAttributeValue($attrid, $value);
        }
        
        foreach ($expectedValues as $attrid => $expectedValue) {
            $value = $d->getAttributeValue($attrid);
            $this->assertTrue($expectedValue === $value, sprintf('wrong value "%s" : expected %s, has %s', $attrid, $this->getDump($expectedValue) , $this->getDump($value, true)));
        }
    }
    /**
     * @dataProvider dataModifyAttributeValue
     */
    public function testModifySetAttributeValue($docName, array $expectedValues)
    {
        
        $d = new_doc(self::$dbaccess, $docName);
        $this->assertTrue($d->isAlive() , sprintf("cannot access %s document", $docName));
        foreach ($expectedValues as $attrid => $value) {
            $d->setAttributeValue($attrid, $value);
        }
        $d->store(); // verify database record
        foreach ($expectedValues as $attrid => $expectedValue) {
            $value = $d->getAttributeValue($attrid);
            $this->assertTrue($expectedValue === $value, sprintf('wrong value "%s" : expected %s, has %s', $attrid, $this->getDump($expectedValue) , $this->getDump($value, true)));
        }
    }
    
    public function dataSetAndGetValues()
    {
        return array(
            array(
                'TST_DOCTYPE1',
                "set" => array(
                    "tst_date" => '2013-04-21',
                    "tst_time" => '10:00',
                    "tst_int" => "23",
                    "tst_double" => "24",
                    "tst_timestamp" => '2013-09-30T10:00:00',
                    "tst_dates" => array(
                        "2013-04-20"
                    ) ,
                    "tst_timestamps" => array(
                        "2013-09-30T10:00:00"
                    )
                ) ,
                "get" => array(
                    "tst_date" => '2013-04-21',
                    "tst_time" => '10:00:00',
                    "tst_int" => 23,
                    "tst_double" => 24.0,
                    "tst_timestamp" => '2013-09-30T10:00:00',
                    "tst_dates" => array(
                        "2013-04-20"
                    ) ,
                    "tst_timestamps" => array(
                        "2013-09-30T10:00:00"
                    )
                )
            ) ,
            array(
                'TST_DOCTYPE1',
                "set" => array(
                    "tst_date" => new \DateTime('2013-04-21') ,
                    "tst_time" => '1:2:5',
                    "tst_timestamp" => new \DateTime('2013-09-30 10:00:00') ,
                    "tst_dates" => array(
                        new \DateTime("2013-04-20") ,
                        new \DateTime("2013-4-2") ,
                    ) ,
                    "tst_timestamps" => array(
                        new \DateTime("2013-09-30T10:00:00")
                    )
                ) ,
                "get" => array(
                    "tst_date" => '2013-04-21',
                    "tst_time" => '01:02:05',
                    "tst_timestamp" => '2013-09-30T10:00:00',
                    "tst_dates" => array(
                        "2013-04-20",
                        "2013-04-02"
                    ) ,
                    "tst_timestamps" => array(
                        "2013-09-30T10:00:00"
                    )
                )
            ) ,
            array(
                'TST_DOCTYPE1',
                "set" => array(
                    "tst_array5" => array(
                        array(
                            "tst_dates" => new \DateTime("2013-04-20")
                        ) ,
                        array(
                            "tst_dates" => new \DateTime("2013-4-2")
                        )
                    )
                ) ,
                "get" => array(
                    "tst_dates" => array(
                        "2013-04-20",
                        "2013-04-02"
                    )
                )
            ) ,
            array(
                'TST_DOCTYPE1',
                "set" => array(
                    "tst_array8" => array(
                        array(
                            "tst_timestamps" => new \DateTime("2013-09-30T10:00:00")
                        ) ,
                        array(
                            "tst_timestamps" => new \DateTime("2013-09-30T22:00:00")
                        )
                    )
                ) ,
                "get" => array(
                    "tst_timestamps" => array(
                        '2013-09-30T10:00:00',
                        '2013-09-30T22:00:00'
                    )
                )
            ) ,
            array(
                'TST_DOCTYPE2',
                "set" => array(
                    "tst_t_numbers" => array(
                        array(
                            "tst_ints1" => "23",
                            "tst_doubles1" => 23.6
                        )
                    )
                ) ,
                "get" => array(
                    
                    "tst_t_numbers" => array(
                        array(
                            "tst_ints1" => 23,
                            "tst_doubles1" => 23.6
                        )
                    )
                )
            ) ,
            array(
                'TST_DOCTYPE2',
                "set" => array(
                    "tst_t_numbers" => array(
                        array(
                            "tst_ints1" => "20",
                            "tst_doubles1" => 20.0
                        ) ,
                        array(
                            "tst_ints1" => "21"
                        ) ,
                        array(
                            "tst_doubles1" => "22.2"
                        ) ,
                        array() ,
                        array(
                            "tst_ints1" => "23",
                            "tst_doubles1" => 23.6
                        ) ,
                        array(
                            "tst_ints1" => 0,
                            "tst_doubles1" => 0
                        ) ,
                    )
                ) ,
                "get" => array(
                    
                    "tst_t_numbers" => array(
                        array(
                            "tst_ints1" => 20,
                            "tst_doubles1" => 20.0
                        ) ,
                        array(
                            "tst_ints1" => 21,
                            "tst_doubles1" => null
                        ) ,
                        array(
                            "tst_ints1" => null,
                            "tst_doubles1" => 22.2
                        ) ,
                        array(
                            "tst_ints1" => null,
                            "tst_doubles1" => null
                        ) ,
                        array(
                            "tst_ints1" => 23,
                            "tst_doubles1" => 23.6
                        ) ,
                        array(
                            "tst_ints1" => 0,
                            "tst_doubles1" => 0.0
                        )
                    )
                )
            ) ,
            array(
                'TST_DOCTYPE2',
                "set" => array(
                    "tst_texts" => array(
                        ''
                    )
                ) ,
                "get" => array(
                    "tst_texts" => array() // last empty values are deleted
                    
                )
            ) ,
            array(
                'TST_DOCTYPE2',
                "set" => array(
                    "tst_texts" => array(
                        '',
                        ''
                    )
                ) ,
                "get" => array(
                    "tst_texts" => array() // last empty values are deleted
                    
                )
            ) ,
            array(
                'TST_DOCTYPE2',
                "set" => array(
                    "tst_texts" => array(
                        '',
                        'a',
                        ''
                    )
                ) ,
                "get" => array(
                    "tst_texts" => array(
                        null,
                        'a'
                    ) // last empty values are deleted
                    
                )
            ) ,
            array(
                'TST_DOCTYPE2',
                "set" => array(
                    "tst_t_text" => array(
                        array(
                            "tst_texts" => ''
                        )
                    )
                ) ,
                "get" => array(
                    "tst_texts" => array(
                        null
                    ) // last empty values are not deleted
                    
                )
            ) ,
            array(
                'TST_DOCTYPE2',
                "set" => array(
                    "tst_t_text" => array(
                        array(
                            "tst_texts" => ''
                        ) ,
                        array(
                            "tst_texts" => null
                        )
                    )
                ) ,
                "get" => array(
                    "tst_texts" => array(
                        null,
                        null
                    ) // last empty values are not deleted
                    
                )
            ) ,
            array(
                'TST_DOCTYPE2',
                "set" => array(
                    "tst_t_numbers" => null
                ) ,
                "get" => array(
                    "tst_t_numbers" => array()
                )
            ) ,
            array(
                'TST_DOCTYPE2',
                "set" => array(
                    "tst_t_numbers" => array()
                ) ,
                "get" => array(
                    "tst_t_numbers" => array()
                )
            ) ,
            array(
                'TST_DOCTYPE2',
                "set" => array(
                    "tst_t_numbers" => array(
                        array(
                            "tst_ints1" => "20",
                            "tst_doubles1" => 20.0
                        ) ,
                        array(
                            "tst_ints1" => "21"
                        ) ,
                        array(
                            "tst_doubles1" => "22.2"
                        ) ,
                        array() ,
                        array() ,
                    )
                ) ,
                "get" => array(
                    
                    "tst_t_numbers" => array(
                        array(
                            "tst_ints1" => 20,
                            "tst_doubles1" => 20.0
                        ) ,
                        array(
                            "tst_ints1" => 21,
                            "tst_doubles1" => null
                        ) ,
                        array(
                            "tst_ints1" => null,
                            "tst_doubles1" => 22.2
                        ) ,
                        array(
                            "tst_ints1" => null,
                            "tst_doubles1" => null
                        ) ,
                        array(
                            "tst_ints1" => null,
                            "tst_doubles1" => null
                        ) ,
                    )
                )
            )
        );
    }
    
    public function dataGetDateValues()
    {
        return array(
            array(
                'TST_DOCTYPE1',
                array(
                    "tst_date" => '2013-04-20',
                    "tst_timestamp" => '2013-09-30T10:00:00',
                    "tst_dates" => array(
                        "2013-04-20"
                    ) ,
                    "tst_timestamps" => array(
                        "2013-09-30T10:00:00"
                    )
                )
            ) ,
            array(
                'TST_DOCTYPE2',
                array(
                    "tst_date" => '2020-05-23',
                    "tst_timestamp" => '2013-09-30T20:10:41',
                    "tst_dates" => array(
                        "2020-05-23",
                        "2017-04-13"
                    ) ,
                    "tst_timestamps" => array(
                        "2013-09-30T20:10:41",
                        "2014-05-23T00:00:00"
                    )
                )
            )
        );
    }
    public function dataGetRelationValues()
    {
        return array(
            array(
                'TST_DOCTYPE2',
                array(
                    "tst_rel" => 'TST_DOCTYPE1',
                    "tst_rels" => array(
                        "TST_DOCTYPE1",
                        "TST_DOCTYPE0"
                    ) ,
                    "tst_rels2" => array(
                        array(
                            "TST_DOCTYPE1",
                            "TST_DOCTYPE0"
                        ) ,
                        array(
                            "TST_DOCTYPE0"
                        ) ,
                        array(
                            "TST_DOCTYPE1"
                        )
                    )
                )
            ) ,
            array(
                'TST_DOCTYPE1',
                array( //TST_DOCTYPE0<BR><BR>TST_DOCTYPE0\n  <BR>TST_DOCTYPE0\n\nTST_DOCTYPE0
                    "tst_rels2" => array(
                        array(
                            "TST_DOCTYPE0",
                            null,
                            "TST_DOCTYPE0"
                        ) ,
                        array(
                            null,
                            "TST_DOCTYPE0"
                        ) ,
                        array() ,
                        array(
                            "TST_DOCTYPE0"
                        )
                    )
                )
            )
        );
    }
    
    public function dataSetRelationValues()
    {
        return array(
            array(
                'TST_DOCTYPE2',
                array(
                    "tst_rel" => 'TST_DOCTYPE1',
                    "tst_rels" => array(
                        "TST_DOCTYPE1",
                        "TST_DOCTYPE0"
                    ) ,
                    "tst_rels2" => array(
                        array(
                            "TST_DOCTYPE1",
                            "TST_DOCTYPE0"
                        ) ,
                        array(
                            "TST_DOCTYPE0"
                        ) ,
                        array(
                            "TST_DOCTYPE1"
                        )
                    )
                )
            ) ,
            array(
                'TST_DOCTYPE1',
                array( //TST_DOCTYPE0<BR><BR>TST_DOCTYPE0\n  <BR>TST_DOCTYPE0\n\nTST_DOCTYPE0
                    "tst_rels2" => array(
                        array(
                            "TST_DOCTYPE0",
                            null,
                            "TST_DOCTYPE0"
                        ) ,
                        array(
                            null,
                            "TST_DOCTYPE0"
                        ) ,
                        array() ,
                        array(
                            "TST_DOCTYPE0"
                        )
                    )
                )
            )
        );
    }
    
    public function dataErrorGetAttributeValue()
    {
        return array(
            array(
                'TST_DOCTYPE0',
                "tst_notfound",
                "DOC0114"
            ) ,
            array(
                'TST_DOCTYPE0',
                "tst_frame",
                "DOC0116"
            )
        );
    }
    
    public function dataErrorSetAttributeValue()
    {
        return array(
            array(
                'TST_DOCTYPE0',
                "tst_notfound",
                "-",
                "DOC0115"
            ) ,
            array(
                'TST_DOCTYPE0',
                "tst_frame",
                "-",
                "DOC0117"
            ) ,
            array(
                'TST_DOCTYPE0',
                "tst_ints",
                "23",
                "VALUE0002"
            ) ,
            array(
                'TST_DOCTYPE0',
                "tst_int",
                "23.4",
                "VALUE0001"
            ) ,
            array(
                'TST_DOCTYPE0',
                "tst_ints",
                array(
                    "234",
                    "Deux"
                ) ,
                "VALUE0001"
            ) ,
            array(
                'TST_DOCTYPE0',
                "tst_RELS2",
                array(
                    array(
                        "23"
                    ) ,
                    "24"
                ) ,
                "VALUE0003"
            ) ,
            array(
                'TST_DOCTYPE0',
                "tst_int",
                23.0,
                "VALUE0005"
            ) ,
            array(
                'TST_DOCTYPE0',
                "tst_time",
                "10:70:00",
                "VALUE0001"
            ) ,
            array(
                'TST_DOCTYPE0',
                "tst_time",
                "10:aa:00",
                "VALUE0001"
            ) ,
            array(
                'TST_DOCTYPE0',
                "tst_time",
                "23",
                "VALUE0001"
            ) ,
            array(
                'TST_DOCTYPE0',
                "tst_double",
                array(
                    "23"
                ) ,
                "VALUE0006"
            ) ,
            array(
                'TST_DOCTYPE0',
                "tst_double",
                "23a",
                "VALUE0001"
            ) ,
            array(
                'TST_DOCTYPE1',
                "tst_double",
                new \DateTime() ,
                "VALUE0005"
            ) ,
            array(
                'TST_DOCTYPE1',
                "tst_title",
                new \DateTime() ,
                "VALUE0005"
            ) ,
            array(
                'TST_DOCTYPE1',
                "tst_t_numbers",
                new \DateTime() ,
                "VALUE0008"
            ) ,
            array(
                'TST_DOCTYPE1',
                "tst_t_numbers",
                array(
                    new \DateTime()
                ) ,
                "VALUE0009"
            ) ,
            array(
                'TST_DOCTYPE1',
                "tst_t_numbers",
                array(
                    23,
                    45
                ) ,
                "VALUE0009"
            ) ,
            array(
                'TST_DOCTYPE1',
                "tst_t_numbers",
                array(
                    array(
                        23,
                        45
                    )
                ) ,
                "VALUE0007"
            )
        );
    }
    public function dataSetAttributeValue()
    {
        return array(
            array(
                array(
                    "tst_title" => null,
                    "tst_int" => null,
                    "tst_money" => null,
                    "tst_double" => null,
                    "tst_date" => null,
                    "tst_time" => null,
                    "tst_rel" => null,
                    "tst_timestamp" => null,
                    "tst_enum" => null,
                    "tst_color" => null,
                    
                    "tst_enums" => array() ,
                    "tst_texts" => array() ,
                    "tst_ints" => array() ,
                    "tst_moneys" => array() ,
                    "tst_doubles" => array() ,
                    "tst_dates" => array() ,
                    "tst_times" => array() ,
                    "tst_timestamps" => array() ,
                    "tst_enumms" => array() ,
                    "tst_colors" => array() ,
                    
                    "tst_t_text" => array()
                )
            ) ,
            array(
                array(
                    "tst_title" => "test trois",
                    "tst_int" => 4,
                    "tst_money" => 4.67,
                    "tst_double" => 3.14159,
                    "tst_time" => "12:54:00",
                    "tst_rel" => "12",
                    "tst_enum" => "a",
                    "tst_color" => "#ff23e6",
                    
                    "tst_enums" => array(
                        "a",
                        "b",
                        "c"
                    ) ,
                    "tst_texts" => array(
                        "un cheval",
                        "deux poulains"
                    ) ,
                    "tst_ints" => array(
                        23,
                        567
                    ) ,
                    "tst_moneys" => array(
                        23.0,
                        56.5,
                        0.0, -2.0
                    ) ,
                    "tst_doubles" => array(
                        3.1415,
                        2.718,
                        null,
                        1.72
                    ) ,
                    
                    "tst_times" => array(
                        "12:00:00"
                    ) ,
                    
                    "tst_enumms" => array(
                        "a",
                        "c",
                        "b"
                    ) ,
                    "tst_colors" => array(
                        "#ffaa00",
                        "#dd8756"
                    ) ,
                    //
                    //                    "tst_t_text" => array()
                    
                )
            )
        );
    }
    public function dataModifyAttributeValue()
    {
        return array(
            array(
                'TST_DOCTYPE1',
                array(
                    "tst_title" => null,
                    "tst_int" => null,
                    "tst_money" => null,
                    "tst_double" => null,
                    "tst_date" => null,
                    "tst_time" => null,
                    "tst_rel" => null,
                    "tst_timestamp" => null,
                    "tst_enum" => null,
                    "tst_color" => null,
                    
                    "tst_enums" => array() ,
                    "tst_texts" => array() ,
                    "tst_ints" => array() ,
                    "tst_moneys" => array() ,
                    "tst_doubles" => array() ,
                    "tst_dates" => array() ,
                    "tst_times" => array() ,
                    "tst_timestamps" => array() ,
                    "tst_enumms" => array() ,
                    "tst_colors" => array() ,
                    
                    "tst_t_text" => array()
                )
            ) ,
            
            array(
                'TST_DOCTYPE2',
                array(
                    "tst_title" => null,
                    "tst_int" => null,
                    "tst_money" => null,
                    "tst_double" => null,
                    "tst_date" => null,
                    "tst_time" => null,
                    "tst_rel" => null,
                    "tst_timestamp" => null,
                    "tst_enum" => null,
                    "tst_color" => null,
                    
                    "tst_enums" => array() ,
                    "tst_texts" => array() ,
                    "tst_ints" => array() ,
                    "tst_moneys" => array() ,
                    "tst_doubles" => array() ,
                    "tst_dates" => array() ,
                    "tst_times" => array() ,
                    "tst_timestamps" => array() ,
                    "tst_enumms" => array() ,
                    "tst_colors" => array() ,
                    
                    "tst_t_text" => array()
                )
            ) ,
            array(
                'TST_DOCTYPE2',
                array(
                    "tst_title" => "test trois",
                    "tst_int" => 4,
                    "tst_money" => 4.67,
                    "tst_htmltext" => '<p>Mon premier paragraphe</p>',
                    "tst_double" => 3.14159,
                    "tst_time" => "12:54:00",
                    "tst_rel" => "12",
                    "tst_enum" => "a",
                    "tst_color" => "#ff23e6",
                    
                    "tst_enums" => array(
                        "a",
                        "b",
                        "c"
                    ) ,
                    "tst_texts" => array(
                        "un cheval",
                        "deux poulains"
                    ) ,
                    "tst_ints" => array(
                        23,
                        567
                    ) ,
                    "tst_moneys" => array(
                        23.0,
                        56.5,
                        0.0, -2.0
                    ) ,
                    "tst_doubles" => array(
                        3.1415,
                        2.718,
                        null,
                        1.72
                    ) ,
                    "tst_times" => array(
                        "10:00:00",
                        "20:59:59"
                    ) ,
                    "tst_enumms" => array(
                        "a",
                        "c",
                        "b"
                    ) ,
                    "tst_colors" => array(
                        "#ffaa00",
                        "#dd8756"
                    ) ,
                    //
                    //                    "tst_t_text" => array()
                    
                )
            )
        );
    }
    public function dataGetAttributeValue()
    {
        return array(
            array(
                'TST_DOCTYPE0',
                array(
                    "tst_title" => null,
                    "tst_int" => null,
                    "tst_money" => null,
                    "tst_double" => null,
                    "tst_date" => null,
                    "tst_time" => null,
                    "tst_rel" => null,
                    "tst_timestamp" => null,
                    "tst_enum" => null,
                    "tst_color" => null,
                    
                    "tst_enums" => array() ,
                    "tst_texts" => array() ,
                    "tst_ints" => array() ,
                    "tst_moneys" => array() ,
                    "tst_doubles" => array() ,
                    "tst_dates" => array() ,
                    "tst_times" => array() ,
                    "tst_timestamps" => array() ,
                    "tst_enumms" => array() ,
                    "tst_colors" => array() ,
                    
                    "tst_t_text" => array()
                )
            ) ,
            array(
                'TST_DOCTYPE1',
                array(
                    "tst_title" => "Titre Un",
                    "TST_TITLE" => "Titre Un",
                    "tst_longtext" => "Et\nLa suite...",
                    "tst_int" => 1,
                    "tst_money" => 2.54,
                    "tst_double" => 3.1415926,
                    "tst_time" => "01:00:00",
                    "tst_enum" => "a",
                    "tst_color" => "#f3f",
                    "tst_enums" => array(
                        "a",
                        "b",
                        "c"
                    ) ,
                    "tst_texts" => array(
                        "Un",
                        "Deux"
                    ) ,
                    "tst_ints" => array(
                        0
                    ) ,
                    "tst_moneys" => array(
                        3.0
                    ) ,
                    "tst_doubles" => array(-54.0
                    ) ,
                    "tst_times" => array(
                        "10:00:00"
                    ) ,
                    "tst_enumms" => array(
                        "a"
                    ) ,
                    "tst_colors" => array(
                        "#f3f"
                    ) ,
                    "tst_longtexts" => array(
                        "Un\nDeux",
                        "Trois\nQuatre"
                    ) ,
                    "tst_ints1" => array(
                        1,
                        2,
                        3
                    ) ,
                    "tst_doubles1" => array(
                        null,
                        null,
                        null
                    ) ,
                    "tst_t_text" => array(
                        array(
                            "tst_texts" => "Un"
                        ) ,
                        (array(
                            "tst_texts" => "Deux"
                        ))
                    ) ,
                    "tst_t_ints" => array(
                        array(
                            "tst_ints" => 0
                        )
                    ) ,
                    "tst_t_numbers" => array(
                        array(
                            "tst_ints1" => 1,
                            "tst_doubles1" => null
                        ) ,
                        array(
                            "tst_ints1" => 2,
                            "tst_doubles1" => null
                        ) ,
                        array(
                            "tst_ints1" => 3,
                            "tst_doubles1" => null
                        )
                    )
                )
            ) ,
            array(
                'TST_DOCTYPE2',
                array(
                    "tst_title" => "Titre Deux",
                    "tst_longtext" => "Texte long. Html tag <BR>.",
                    "tst_int" => 0,
                    "tst_money" => 3.0,
                    "tst_double" => - 54.0,
                    "tst_time" => "14:17:43",
                    "tst_enum" => "c",
                    "tst_color" => "#50ED42",
                    "tst_enums" => array(
                        "c",
                        null,
                        "b"
                    ) ,
                    "tst_texts" => array(
                        "Un cheval noir",
                        "Et un autre rouge"
                    ) ,
                    "tst_ints" => array(
                        45,
                        3654, -34
                    ) ,
                    "tst_moneys" => array(
                        2.54,
                        3.0,
                        2.72
                    ) ,
                    "tst_doubles" => array(
                        3.1415926,
                        2.7182818,
                        1.61803398875
                    ) ,
                    "tst_times" => array(
                        "04:07:03"
                    ) ,
                    "tst_enumms" => array(
                        "c"
                    ) ,
                    "tst_colors" => array(
                        "#50ED42"
                    ) ,
                    "tst_ints1" => array(
                        3,
                        null,
                        null
                    ) ,
                    "tst_doubles1" => array(
                        null,
                        5.6,
                        7.8
                    ) ,
                    "tst_t_ints" => array(
                        array(
                            "tst_ints" => 45
                        ) ,
                        array(
                            "tst_ints" => 3654
                        ) ,
                        array(
                            "tst_ints" => - 34
                        )
                    )
                )
            )
        );
    }
}
?>