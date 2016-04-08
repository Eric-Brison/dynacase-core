<?php
/*
 * @author Anakeen
 * @package Dcp\Pu
*/

namespace Dcp\Pu;

require_once 'PU_testcase_dcp.php';
include_once 'WHAT/Lib.Http.php';
include_once 'FDL/enum_choice.php';

class TestGetText extends TestCaseDcp
{
    /**
     * @dataProvider dataUnderscore
     */
    public function testUnderscore($text, $lang, $expectedText)
    {
        setLanguage($lang);
        $this->assertEquals($expectedText, _($text));
    }
    /**
     * @dataProvider data3Underscore
     */
    public function test3Underscore($text, $textp, $lang, $num, $expectedText)
    {
        setLanguage($lang);
        $this->assertEquals($expectedText, sprintf(n___($text, $textp, $num) , $num));
    }
    /**
     * @dataProvider dataContext
     */
    public function testContext($text, $ctx, $lang, $expectedText)
    {
        setLanguage($lang);
        $this->assertEquals($expectedText, ___($text, $ctx));
    }
    /**
     * @dataProvider dataPluralContext
     */
    public function testPluralContext($text, $textp, $ctx, $lang, $num, $expectedText)
    {
        setLanguage($lang);
        $this->assertEquals($expectedText, sprintf(n___($text, $textp, $num, $ctx) , $num));
    }
    /**
     * @dataProvider dataTextlayout
     */
    public function testTextlayout($text, $lang, $expectedText)
    {
        setLanguage($lang);
        $lay = new \Layout("", self::getAction());
        $lay->template = $text;
        $genText = $lay->gen();
        
        $this->assertEquals($expectedText, $genText);
    }
    
    public function dataTextlayout()
    {
        return array(
            array(
                "<p>[TEXT:dcptest:Hello]</p>",
                "fr_FR",
                "<p>Bonjour</p>"
            ) ,
            
            array(
                "<p>[TEXT(dcpctx1):dcptest:Test locale]</p>",
                "fr_FR",
                "<p>test avec contexte un</p>"
            ) ,
            array(
                "<p>[TEXT(dcpctx2):dcptest:Test locale]</p>",
                "fr_FR",
                "<p>test avec contexte deux</p>"
            )
        );
    }
    public function dataPluralContext()
    {
        $i18n = _("dcptest:Hello");
        $i18n = _("dcptest:Good Bye");
        $i18n = n___("dcptest:%d symbol", "dcptest:%d symbols", 45);
        $i18n = n___("%d symbol", "%d symbols", 45, "dcpctx1");
        $i18n = ___("dcptest:Test locale");
        $i18n = ___("dcptest:Test locale", "dcpctx1");
        $i18n = ___("dcptest:Test locale", "dcpctx2");
        return array(
            array(
                "dcptest:%d symbol",
                "dcptest:%d symbols",
                "",
                "fr_FR",
                0,
                "0 symbole simple"
            ) ,
            array(
                "%d symbol",
                "%d symbols",
                "dcpctx1",
                "en_US",
                0,
                "0 very complex symbols"
            ) ,
            array(
                "%d symbol",
                "%d symbols",
                "dcpctx1",
                "en_US",
                34,
                "34 very complex symbols"
            ) ,
            
            array(
                "%d symbol",
                "%d symbols",
                "dcpctx1",
                "fr_FR",
                0,
                "0 symbole très simple"
            ) ,
            array(
                "%d symbol",
                "%d symbols",
                "dcpctx1",
                "fr_FR",
                1,
                "1 symbole très simple"
            ) ,
            array(
                "%d symbol",
                "%d symbols",
                "dcpctx1",
                "fr_FR",
                10,
                "10 symboles très complexes"
            )
        );
    }
    public function dataContext()
    {
        return array(
            array(
                "dcptest:Hello",
                "",
                "fr_FR",
                "Bonjour"
            ) ,
            array(
                "dcptest:Good Bye",
                "",
                "fr_FR",
                "Au revoir"
            ) ,
            array(
                "dcptest:Hello",
                "",
                "en_US",
                "Hello world"
            ) ,
            array(
                "dcptest:Good Bye",
                "",
                "en_US",
                "Good bye world"
            ) ,
            array(
                "dcptest:Test locale",
                "",
                "fr_FR",
                "test sans contexte"
            ) ,
            array(
                "dcptest:No translation",
                "",
                "en_US",
                "dcptest:No translation"
            ) ,
            array(
                "dcptest:Test locale",
                "dcpctx1",
                "fr_FR",
                "test avec contexte un"
            ) ,
            array(
                "dcptest:Test locale",
                "dcpctx2",
                "fr_FR",
                "test avec contexte deux"
            ) ,
            array(
                "dcptest:Test locale",
                "",
                "en_US",
                "test without context"
            ) ,
            array(
                "dcptest:Test locale",
                "dcpctx1",
                "en_US",
                "test with first context"
            ) ,
            array(
                "dcptest:Test locale",
                "dcpctx2",
                "en_US",
                "test with second context"
            ) ,
            array(
                "dcptest:Test locale",
                "dcpctx3",
                "en_US",
                "dcptest:Test locale"
            )
        );
    }
    public function data3Underscore()
    {
        $i18n = n___("dcptest:%d symbol", "dcptest:%d symbols",1);
        $i18n = n___("dcptest:%.02f symbol", "dcptest:%02f symbols",1);
        return array(
            array(
                "dcptest:%d symbol",
                "dcptest:%d symbols",
                "fr_FR",
                0,
                "0 symbole simple"
            ) ,
            array(
                "dcptest:%d symbol",
                "dcptest:%d symbols",
                "fr_FR", -1,
                "-1 symbole simple"
            ) ,
            array(
                "dcptest:%d symbol",
                "dcptest:%d symbols",
                "fr_FR", -3,
                "-3 symboles complexes"
            ) ,
            array(
                "dcptest:%d symbol",
                "dcptest:%d symbols",
                "fr_FR",
                1,
                "1 symbole simple"
            ) ,
            array(
                "dcptest:%d symbol",
                "dcptest:%d symbols",
                "fr_FR",
                3,
                "3 symboles complexes"
            ) ,
            array(
                "dcptest:%.02f symbol",
                "dcptest:%.02f symbols",
                "fr_FR",
                1.4,
                "1.40 symbole simple"
            ) ,
            array(
                "dcptest:%.02f symbol",
                "dcptest:%.02f symbols",
                "fr_FR",
                1.6,
                "1.60 symbole simple"
            ) ,
            array(
                "dcptest:%.02f symbol",
                "dcptest:%.02f symbols",
                "fr_FR", -0.6,
                "-0.60 symbole simple"
            ) ,
            array(
                "dcptest:%.02f symbol",
                "dcptest:%.02f symbols",
                "fr_FR", -0.2,
                "-0.20 symbole simple"
            ) ,
            array(
                "dcptest:%.02f symbol",
                "dcptest:%.02f symbols",
                "fr_FR", -1.2,
                "-1.20 symbole simple"
            ) ,
            /*
            array(
                "dcptest:%.02f symbol",
                "dcptest:%.02f symbols",
                "fr_FR",
                -1.4,
                "-1.40 symbole simple"
            ) ,
            array(
                "dcptest:%.02f symbol",
                "dcptest:%.02f symbols",
                "fr_FR",
                -2.4,
                "-2.40 symboles complexes"
            ) ,*/
            array(
                "dcptest:%.02f symbol",
                "dcptest:%.02f symbols",
                "fr_FR",
                2.5,
                "2.50 symboles complexes"
            ) ,
            array(
                "dcptest:%.02f symbol",
                "dcptest:%.02f symbols",
                "en_US",
                2.5,
                "2.50 complex symbols"
            ) ,
            array(
                "dcptest:%.02f symbol",
                "dcptest:%.02f symbols",
                "en_US",
                0.5,
                "0.50 complex symbols"
            ) ,
            array(
                "dcptest:%.02f symbol",
                "dcptest:%.02f symbols",
                "en_US",
                1,
                "1.00 simple symbol"
            ) ,
            /*
            array(
                "dcptest:%.02f symbol",
                "dcptest:%.02f symbols",
                "en_US",
                1.3,
                "1.30 complex symbols"
            ) ,
            array(
                "dcptest:%.02f symbol",
                "dcptest:%.02f symbols",
                "en_US",
                1.6,
                "1.60 complex symbols"
            ) ,*/
            array(
                "dcptest:%d symbol",
                "dcptest:%d symbols",
                "en_US",
                0,
                "0 complex symbols"
            ) ,
            array(
                "dcptest:%d symbol",
                "dcptest:%d symbols",
                "en_US",
                1,
                "1 simple symbol"
            ) ,
            array(
                "dcptest:%d symbol",
                "dcptest:%d symbols",
                "en_US", -1,
                "-1 simple symbol"
            ) ,
            array(
                "dcptest:%d symbol",
                "dcptest:%d symbols",
                "en_US",
                31,
                "31 complex symbols"
            )
        );
    }
    
    public function dataUnderscore()
    {
        return array(
            array(
                "dcptest:Hello",
                "fr_FR",
                "Bonjour"
            ) ,
            array(
                "dcptest:Good Bye",
                "fr_FR",
                "Au revoir"
            ) ,
            array(
                "dcptest:Hello",
                "en_US",
                "Hello world"
            ) ,
            array(
                "dcptest:Good Bye",
                "en_US",
                "Good bye world"
            )
        );
    }
}
?>