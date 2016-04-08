<?php
/*
 * @author Anakeen
 * @package Dcp\Pu
*/

namespace Dcp\Pu;

require_once 'PU_testcase_dcp_commonfamily.php';

class TestGetTextualValue extends TestCaseDcpCommonFamily
{
    static function getCommonImportFile()
    {
        return array(
            'PU_data_dcp_gettextualvaluefamily.ods'
        );
    }
    /**
     * @dataProvider data_getTextualValue()
     */
    public function test_getTextualValue(array $configuration, array $data)
    {
        $doc = new_Doc(self::$dbaccess, $data['docid']);
        if (isset($data['set'])) {
            foreach ($data['set'] as $attrName => $value) {
                $err = $doc->setValue($attrName, $value);
                $this->assertTrue(($err == '') , sprintf("Unexpected error setting value '%s' for attribute '%s' on document '%s': %s", var_export($value, true) , $attrName, $doc->name, $err));
            }
        }
        foreach ($data['get'] as $attrName => $expectedValue) {
            $value = $doc->getTextualAttrValue($attrName, -1, $configuration);
            $this->assertTrue(($value == $expectedValue) , sprintf("Unexpected value '%s' for attribute '%s' on document '%s': expected value = '%s'", $value, $attrName, $data['docid'], $expectedValue));
        }
    }
    public function data_getTextualValue()
    {
        return array(
            array(
                array() ,
                array(
                    'docid' => 'TST_GETTEXTUALVALUE1',
                    'set' => array(
                        'TST_HTMLTEXT' => '<p class="foo">Foo</p>',
                        'TST_HTMLTEXTS' => array(
                            '<p class="foo">Foo</p>',
                            '<p class="bar">Bar</p>'
                        )
                    ) ,
                    'get' => array(
                        'TST_TITLE' => "Titre Un",
                        'TST_LONGTEXT' => "Et\nLa suite...",
                        'TST_MONEY' => "2.54",
                        'TST_DOUBLE' => "3.142",
                        'TST_INT' => "1",
                        'TST_DATE' => "2013-04-20",
                        'TST_TIME' => "01:00:00",
                        'TST_HTMLTEXT' => '<p class="foo">Foo</p>',
                        'TST_TIMESTAMP' => "2013-09-30 10:00:00",
                        'TST_REL' => "",
                        'TST_ENUM' => "A",
                        'TST_COLOR' => "#f3f",
                        'TST_ENUMMS' => "A",
                        'TST_ACCOUNT' => "User One",
                        'TST_TEXTS' => "Un\nDeux",
                        'TST_MONEYS' => "3",
                        'TST_DOUBLES' => "-54.000",
                        'TST_INTS' => "0",
                        'TST_DATES' => "2013-04-20",
                        'TST_TIMES' => "10:00",
                        'TST_HTMLTEXTS' => '<p class="foo">Foo</p>' . "\n" . '<p class="bar">Bar</p>',
                        'TST_TIMESTAMPS' => "2013-09-30 10:00",
                        'TST_RELS' => "",
                        'TST_ENUMS' => "A\nB\nC",
                        'TST_COLORS' => "#f3f",
                        'TST_LONGTEXTS' => "Un Deux\nTrois Quatre",
                        'TST_INTS1' => "1\n2\n3",
                        'TST_DOUBLES1' => "\n\n",
                        'TST_RELS2' => "User One, , User One\n, User One\n\nUser One",
                        'TST_ACCOUNTS' => "User One\nUser Two"
                    )
                )
            ) ,
            array(
                array() ,
                array(
                    'docid' => 'TST_GETTEXTUALVALUE2',
                    'set' => array(
                        'TST_HTMLTEXT' => '<p class="foo">Foo</p>',
                        'TST_HTMLTEXTS' => array(
                            '<p class="foo">Foo</p>',
                            '<p class="bar">Bar</p>'
                        )
                    ) ,
                    'get' => array(
                        'TST_TITLE' => "Titre Deux",
                        'TST_LONGTEXT' => "Texte long. Html tag <BR>.",
                        'TST_MONEY' => "3",
                        'TST_DOUBLE' => "-54",
                        'TST_INT' => "0",
                        'TST_DATE' => "2020-05-23",
                        'TST_TIME' => "14:17:43",
                        'TST_HTMLTEXT' => '<p class="foo">Foo</p>',
                        'TST_TIMESTAMP' => "2013-09-30 20:10:41",
                        'TST_REL' => "User One",
                        'TST_ENUM' => "C",
                        'TST_COLOR' => "#50ED42",
                        'TST_ENUMMS' => "C",
                        'TST_ACCOUNT' => "User Two",
                        'TST_TEXTS' => "Un cheval noir\nEt un autre rouge",
                        'TST_MONEYS' => "2.54\n3\n2.72",
                        'TST_DOUBLES' => "3.142\n2.718\n1.618",
                        'TST_INTS' => "45\n3654\n-34",
                        'TST_DATES' => "2020-05-23\n2017-04-13",
                        'TST_TIMES' => "04:07:03",
                        'TST_HTMLTEXTS' => '<p class="foo">Foo</p>' . "\n" . '<p class="bar">Bar</p>',
                        'TST_TIMESTAMPS' => "2013-09-30 20:10:41\n2014-05-23",
                        'TST_RELS' => "User One\nUser Two",
                        'TST_ENUMS' => "C\n\nB",
                        'TST_COLORS' => "#50ED42",
                        'TST_LONGTEXTS' => "Alpha Béta\nA B C",
                        'TST_INTS1' => "3\n\n",
                        'TST_DOUBLES1' => "\n5.6\n7.8",
                        'TST_RELS2' => "User Two, User One\nUser One\nUser Two",
                        'TST_ACCOUNTS' => "User One\nUser Two"
                    )
                )
            ) ,
            array(
                array(
                    "longtextMultipleBrToCr" => "<BR>"
                ) ,
                array(
                    'docid' => 'TST_GETTEXTUALVALUE2',
                    'set' => array(
                        'TST_HTMLTEXT' => '<p class="foo">Foo</p>',
                        'TST_HTMLTEXTS' => array(
                            '<p class="foo">Foo</p>',
                            '<p class="bar">Bar</p>'
                        )
                    ) ,
                    'get' => array(
                        'TST_TITLE' => "Titre Deux",
                        'TST_LONGTEXT' => "Texte long. Html tag <BR>.",
                        'TST_MONEY' => "3",
                        'TST_DOUBLE' => "-54",
                        'TST_INT' => "0",
                        'TST_DATE' => "2020-05-23",
                        'TST_TIME' => "14:17:43",
                        'TST_HTMLTEXT' => '<p class="foo">Foo</p>',
                        'TST_TIMESTAMP' => "2013-09-30 20:10:41",
                        'TST_REL' => "User One",
                        'TST_ENUM' => "C",
                        'TST_COLOR' => "#50ED42",
                        'TST_ENUMMS' => "C",
                        'TST_ACCOUNT' => "User Two",
                        'TST_TEXTS' => "Un cheval noir\nEt un autre rouge",
                        'TST_MONEYS' => "2.54\n3\n2.72",
                        'TST_DOUBLES' => "3.142\n2.718\n1.618",
                        'TST_INTS' => "45\n3654\n-34",
                        'TST_DATES' => "2020-05-23\n2017-04-13",
                        'TST_TIMES' => "04:07:03",
                        'TST_HTMLTEXTS' => '<p class="foo">Foo</p>' . "\n" . '<p class="bar">Bar</p>',
                        'TST_TIMESTAMPS' => "2013-09-30 20:10:41\n2014-05-23",
                        'TST_RELS' => "User One\nUser Two",
                        'TST_ENUMS' => "C\n\nB",
                        'TST_COLORS' => "#50ED42",
                        'TST_LONGTEXTS' => "Alpha<BR>Béta\nA<BR>B<BR>C",
                        'TST_INTS1' => "3\n\n",
                        'TST_DOUBLES1' => "\n5.6\n7.8",
                        'TST_RELS2' => "User Two, User One\nUser One\nUser Two",
                        'TST_ACCOUNTS' => "User One\nUser Two"
                    )
                )
            )
        );
    }
}
