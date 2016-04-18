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
        $this->requiresCoreParamEquals('CORE_LANG', 'fr_FR');
        $d = createDoc(self::$dbaccess, $this->famName);
        $this->assertTrue(is_object($d) , "cannot create TST_FAMSETVALUE document");
        
        $err = $d->setValue($attrid, $value);
        $this->assertEmpty($err, sprintf("setvalue error : %s", $err));
        if ($converted === false) $converted = $value;
        
        $this->assertEquals($converted, $d->getRawValue($attrid) , "setvalue / getvalue $attrid : not the same");
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
        $this->assertEmpty($d->getRawValue($attrid));
        
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
            $this->assertEquals($expectedCount, count($d->getMultipleRawValues($aid)) , sprintf("count for %s incorrect %d <> %d", $aid, $expectedCount, count($d->getMultipleRawValues($aid))));
        }
        
        if ($secondValues) {
            foreach ($secondValues as $aid => $value) {
                $err.= $d->setValue($aid, $value);
            }
            $this->assertEmpty($err, sprintf("setvalue error : %s", $err));
            
            foreach ($secondValues as $aid => $value) {
                $oa = $d->getAttribute($aid);
                $this->assertEquals($secondCount, count($d->getMultipleRawValues($aid)) , sprintf("second count for %s incorrect %d <> %d : %s", $aid, $secondCount, count($d->getMultipleRawValues($aid)) , print_r($d->getArrayRawValues($oa->fieldSet->id) , true)));
            }
        }
        return $d;
    }
    /**
     * @dataProvider dataOldValue
     */
    public function testOldValue(array $before, array $after, array $notchanged)
    {
        $this->requiresCoreParamEquals('CORE_LANG', 'fr_FR');
        $d = createDoc(self::$dbaccess, $this->famName);
        $this->assertTrue(is_object($d) , "cannot create TST_FAMSETVALUE document");
        $err = '';
        foreach ($before as $aid => $value) {
            $err.= $d->setValue($aid, $value);
        }
        $d->store();
        self::resetDocumentCache();
        // new instance
        $d = new_doc(self::$dbaccess, $d->id);
        $this->assertEmpty($err, sprintf("before setvalue error : %s", $err));
        foreach ($after as $aid => $value) {
            $err.= $d->setValue($aid, $value);
        }
        $this->assertEmpty($err, sprintf("after setvalue error : %s", $err));
        
        foreach ($notchanged as $aid => $value) {
            $this->assertEquals($value, $d->getOldRawValue($aid) , "wrong old value $aid" . print_r($d->getValues() , true));
        }
    }
    /**
     * @dataProvider dataAttributeOldValue
     */
    public function testOldAttributeValue(array $before, array $after, array $notchanged)
    {
        $this->requiresCoreParamEquals('CORE_LANG', 'fr_FR');
        $d = createDoc(self::$dbaccess, $this->famName);
        $this->assertTrue(is_object($d) , "cannot create TST_FAMSETVALUE document");
        $err = '';
        foreach ($before as $aid => $value) {
            $d->setAttributeValue($aid, $value);
        }
        $d->store();
        self::resetDocumentCache();
        // new instance
        $d = new_doc(self::$dbaccess, $d->id);
        foreach ($after as $aid => $value) {
            $d->setAttributeValue($aid, $value);
        }
        
        foreach ($notchanged as $aid => $value) {
            $this->assertEquals($value, $d->getOldRawValue($aid) , "wrong old attribute value $aid" . print_r(array(
                "values" => $d->getValues() ,
                "old" => $d->getOldRawValues()
            ) , true));
        }
    }
    public static function dataAttributeOldValue()
    {
        return array_merge(self::dataOldValue() , array(
            array(
                "before" => array(
                    "tst_title" => "T1",
                    "tst_int" => 2,
                    "tst_array" => array(
                        array(
                            "tst_coltext" => "Un",
                            "tst_colint" => 1,
                            "tst_coldate" => "2014-05-07"
                        ) ,
                    ) ,
                ) ,
                "after" => array(
                    "tst_title" => "T1",
                    "tst_int" => 3,
                    "tst_array" => array(
                        array(
                            "tst_coltext" => "Deux",
                            "tst_colint" => 2,
                            "tst_coldate" => "2014-05-08"
                        )
                    ) ,
                ) ,
                "cnanged" => array(
                    "tst_title" => null,
                    "tst_int" => 2,
                    "tst_coltext" => "Un",
                    "tst_colint" => 1,
                    "tst_coldate" => "2014-05-07",
                )
            ) ,
            array(
                "before" => array(
                    "tst_title" => "T1",
                    "tst_int" => 2,
                    "tst_array" => array(
                        array(
                            "tst_coltext" => "Un",
                            "tst_colint" => 1,
                            "tst_coldate" => "2014-05-07"
                        ) ,
                    ) ,
                ) ,
                "after" => array(
                    "tst_title" => "T1",
                    "tst_int" => 3,
                    "tst_array" => array(
                        array(
                            "tst_coltext" => "Deux",
                            "tst_colint" => 1,
                            "tst_coldate" => "2014-05-07"
                        )
                    ) ,
                ) ,
                "cnanged" => array(
                    "tst_title" => null,
                    "tst_int" => 2,
                    "tst_coltext" => "Un",
                    "tst_colint" => null,
                    "tst_coldate" => null,
                )
            ) ,
            array(
                "before" => array(
                    "tst_title" => "T1",
                    "tst_int" => 2,
                    "tst_array" => array(
                        array(
                            "tst_coltext" => "Un",
                            "tst_colint" => 1,
                            "tst_coldate" => "2014-05-07"
                        ) ,
                        array(
                            "tst_coltext" => "Deux",
                            "tst_colint" => 2,
                            "tst_coldate" => "2014-05-08"
                        )
                    ) ,
                ) ,
                "after" => array(
                    "tst_title" => "T1",
                    "tst_int" => 3,
                    "tst_array" => array(
                        array(
                            "tst_coltext" => "Trois",
                            "tst_colint" => 2,
                            "tst_coldate" => "2014-05-08"
                        ) ,
                    ) ,
                ) ,
                "cnanged" => array(
                    "tst_title" => null,
                    "tst_int" => 2,
                    "tst_coltext" => "Un\nDeux",
                    "tst_colint" => "1\n2",
                    "tst_coldate" => "2014-05-07\n2014-05-08",
                )
            ) ,
            array(
                "before" => array(
                    "tst_title" => "T1",
                    "tst_int" => 31,
                    "tst_file" => "text/css|123|toto.css"
                ) ,
                "after" => array(
                    "tst_title" => "T2",
                    "tst_int" => 32,
                    "tst_file" => "text/css|234|titi.css"
                ) ,
                "cnanged" => array(
                    "tst_title" => "T1",
                    "tst_int" => 31,
                    "tst_file" => "text/css|123|toto.css"
                )
            ) ,
            array(
                "before" => array(
                    "tst_title" => "T1",
                    "tst_int" => 31,
                    "tst_array" => array(
                        array(
                            "tst_coltext" => "Un",
                            "tst_colint" => 1,
                            "tst_coldate" => "2014-05-07",
                            "tst_files" => "text/css|123|un.css"
                        ) ,
                        array(
                            "tst_coltext" => "Deux",
                            "tst_colint" => 2,
                            "tst_coldate" => "2014-05-08",
                            "tst_files" => "text/css|234|deux.css"
                        )
                    ) ,
                ) ,
                "after" => array(
                    "tst_title" => "T2",
                    "tst_int" => 3,
                    "tst_array" => array(
                        array(
                            "tst_coltext" => "Un",
                            "tst_colint" => 1,
                            "tst_coldate" => "2014-05-07",
                            "tst_files" => "text/css|123|un.css"
                        ) ,
                        array(
                            "tst_coltext" => "Deux",
                            "tst_colint" => 2,
                            "tst_coldate" => "2014-05-08",
                            "tst_files" => "text/css|234|deux.css"
                        ) ,
                        array(
                            "tst_coltext" => "Trois",
                            "tst_colint" => 3,
                            "tst_coldate" => "2014-05-08",
                            "tst_files" => "text/css|345|trois.css"
                        ) ,
                    ) ,
                ) ,
                "cnanged" => array(
                    "tst_title" => "T1",
                    "tst_int" => 31,
                    "tst_coltext" => "Un\nDeux",
                    "tst_colint" => "1\n2",
                    "tst_coldate" => "2014-05-07\n2014-05-08",
                    "tst_files" => "text/css|123|un.css\ntext/css|234|deux.css"
                )
            )
        ));
    }
    public static function dataOldValue()
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
            ) ,
            array(
                array(
                    "tst_col1" => array(
                        "Un",
                        "Deux",
                        "Trois"
                    ) ,
                    "tst_col2" => "",
                    "tst_col3" => array(
                        "2012-06-20",
                        "2012-06-21",
                        "2012-06-22"
                    )
                ) ,
                3,
                array(
                    "tst_col1" => array(
                        "Un",
                        "Deux"
                    ) ,
                    "tst_col3" => array(
                        "2012-06-20"
                    )
                ) ,
                2
            )
        );
    }
    
    public function goodValues()
    {
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
                '2011-11-20'
            ) ,
            array(
                'TST_DATE',
                '2011-11-21',
                '2011-11-21'
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
                '2011-11-21 12:34'
            ) ,
            array(
                'TST_TIMESTAMP',
                '2011-11-21T12:34',
                '2011-11-21T12:34'
            ) ,
            array(
                'TST_HTML',
                '<p>Hello</p>'
            ) ,
            array(
                'TST_HTML',
                '<p>&ecirc;tre &amp; Co <i>12 &euro;</i></p>',
                '<p>être &amp; Co <i>12 €</i></p>'
            ) ,
            array(
                'TST_HTML',
                '<p>L\'avenir est "ici"</p>'
            ) ,
            array(
                'TST_HTML',
                '<p>L&apos;avenir est &quot;ici&quot;</p>',
                '<p>L&apos;avenir est "ici"</p>'
            ) ,
            array(
                'TST_HTMLCLEAN',
                '<p>L&apos;avenir est &quot;ici&quot;</p>',
                '<p>L\'avenir est "ici"</p>'
            ) ,
            array(
                'TST_HTML',
                '<p>L&#39;avenir est &quot;ici&quot;</p>',
                '<p>L\'avenir est "ici"</p>'
            ) ,
            array(
                'TST_HTMLCLEAN',
                '<p>L&#39;avenir est &quot;ici&quot;</p>',
                '<p>L\'avenir est "ici"</p>'
            ) ,
            array(
                'TST_HTML',
                '<style>p.zou:{color:red;}</style><p class="zou">C\'est rouge</p>',
                '<style>p.zou:{color:red;}</style><p class="zou">C\'est rouge</p>'
            ) ,
            array(
                'TST_HTML',
                '<p onload="alert(1)">Hou la là</p>',
                '<p >Hou la là</p>'
            ) ,
            array(
                'TST_HTMLCLEAN',
                '<p onload="alert(1)">Hou la là</p>',
                '<p>Hou la là</p>'
            ) ,
            array(
                'TST_HTML',
                '<p onload=\'alert(1)\'>Hou la là quote</p>',
                '<p >Hou la là quote</p>'
            ) ,
            array(
                'TST_HTMLCLEAN',
                '<p onload=\'alert(1)\'>Hou la là quote</p>',
                '<p>Hou la là quote</p>'
            ) ,
            array(
                'TST_HTML',
                '<p onload = "alert(1)">Hou la là espaces</p>',
                '<p >Hou la là espaces</p>'
            ) ,
            array(
                'TST_HTMLCLEAN',
                '<p onload = "alert(1)">Hou la là espaces</p>',
                '<p>Hou la là espaces</p>'
            ) ,
            array(
                'TST_HTML',
                "<p onload\n=\n\t 'alert(1)' style='color:blue'>Hou la là espaces compliqués</p>",
                "<p  style='color:blue'>Hou la là espaces compliqués</p>"
            ) ,
            array(
                'TST_HTMLCLEAN',
                "<p onload\n=\n\t 'alert(1)' style='color:blue'>Hou la là espaces compliqués</p>",
                "<p>Hou la là espaces compliqués</p>"
            ) ,
            array(
                'TST_HTML',
                '<p onload=\'alert(1)\'>Hou la là</p>',
                '<p >Hou la là</p>'
            ) ,
            array(
                'TST_HTMLCLEAN',
                '<p onload=\'alert(1)\'>Hou la là</p>',
                '<p>Hou la là</p>'
            ) ,
            array(
                'TST_HTML',
                '<script>alert("oh");</script><p data-oh="2" onclick="alert(1)" data-yo="1">Hou la là</p>',
                'alert("oh");<p data-oh="2"  data-yo="1">Hou la là</p>'
            ) ,
            array(
                'TST_HTMLCLEAN',
                '<script>alert("oh");</script><p data-oh="2" onclick="alert(1)" data-yo="1">Hou la là</p>',
                'alert("oh");<p data-oh="2" data-yo="1">Hou la là</p>'
            ) ,
            array(
                'TST_HTML',
                '<a href="javascript:alert(1)">Hou la là</a>',
                '<a href="nojavascript...alert(1)">Hou la là</a>'
            ) ,
            array(
                'TST_HTML',
                '<a href="vbscript:alert(1)">Hou la là</a>',
                '<a href="novbscript...alert(1)">Hou la là</a>'
            ) ,
            array(
                'TST_HTML',
                '<xml:a href="#Yo">Pas de domaine</xml:a>',
                'Pas de domaine'
            ) ,
            array(
                'TST_HTML',
                '<a href="#Yo">Yo</a>'
            ) ,
            array(
                'TST_HTML',
                '<p data-onload="alert(1)">&Ccedil;&agrave; et l&agrave;</p>',
                '<p data-onload="alert(1)">Çà et là</p>'
            ) ,
            array(
                'TST_HTML',
                '<div class="special"><span width="30px">Hello</span></div>'
            ) ,
            array(
                'TST_HTMLCLEAN',
                '<div class="special"><span width="30px">Hello</span></div>',
                '<div>Hello</div>'
            ) ,
            array(
                'TST_HTMLCLEAN',
                '<div><font color="#007755">Hello</font></div>',
                '<div>Hello</div>'
            ) ,
            array(
                'TST_HTML',
                '<div>A<iframe style="color:red" onload="alert(1)" src="about:blank"/>Z</div>',
                '<div>A<iframe style="color:red"  src="about:blank"/>Z</div>'
            ) ,
            array(
                'TST_HTMLCLEAN',
                '<div>A<iframe style="color:red" onload="alert(1)" src="about:blank"/>Z</div>',
                '<div>A<iframe src="about:blank"></iframe>Z</div>'
            ) ,
            array(
                'TST_HTML',
                '<div>A<iframe style="color:red" onload="alert(1)" src="about:blank"/>Z</div>',
                '<div>A<iframe style="color:red"  src="about:blank"/>Z</div>'
            ) ,
            array(
                'TST_HTMLCLEAN',
                '<div>A<iframe style="color:red" onload="alert(1)" src="about:blank"/>Z</div>',
                '<div>A<iframe src="about:blank"></iframe>Z</div>'
            ) ,
            array(
                'TST_HTMLCLEAN',
                '<style>p.zou:{color:red;}</style><p class="zou">C\'est rouge</p>',
                '<p>C\'est rouge</p>'
            ) ,
            array(
                'TST_HTMLCLEAN',
                '<style>p.zou:{color:red;}</style><p class=\'zou\'>C\'est rouge</p>',
                '<p>C\'est rouge</p>'
            ) ,
            array(
                'TST_HTML',
                '<p>Hello <em>world',
                '<p>Hello <em>world'
            ) ,
            array(
                'TST_HTMLCLEAN',
                '<p>Hello <em>world',
                '<p>Hello <em>world</em></p>'
            ) ,
            array(
                'TST_HTMLCLEAN',
                '<video src="foo.ogg"><track kind="subtitles" src="foo.en.vtt" srclang="en" label="English"></track><track kind="subtitles" src="foo.sv.vtt" srclang="sv" label="Svenska"></track></video>',
                '<video src="foo.ogg"><track kind="subtitles" src="foo.en.vtt" srclang="en" label="English"></track><track kind="subtitles" src="foo.sv.vtt" srclang="sv" label="Svenska"></track></video>'
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
            ) ,
            array(
                'TST_COL2',
                'a'
            ) ,
            array(
                'TST_HTML',
                '<p'
            ) ,
            array(
                'TST_HTML',
                '<p style=">Hello</p>'
            ) ,
            array(
                'TST_HTMLCLEAN',
                '<p'
            ) ,
            array(
                'TST_HTMLCLEAN',
                '<p style=">Hello</p>'
            )
        );
    }
}
