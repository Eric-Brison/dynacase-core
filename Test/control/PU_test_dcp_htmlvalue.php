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

class TestHtmlValue extends TestCaseDcpCommonFamily
{
    public $famName = "TST_FAMGETHTMLVALUE";
    /**
     * import TST_FAMSETVALUE family
     * @static
     * @return string
     */
    protected static function getCommonImportFile()
    {
        return "PU_data_dcp_gethtmlvaluefamily.ods";
    }
    /**
     * @dataProvider dataGetHtmlValues
     */
    public function testGetHtmlValues($docName, array $setValues, array $expectedValues)
    {
        $this->requiresCoreParamEquals('CORE_LANG', 'fr_FR');
        $d = new_doc(self::$dbaccess, $docName);
        $this->assertTrue($d->isAlive() , sprintf("cannot access %s document", $docName));
        foreach ($setValues as $attrid => $newValue) {
            $d->setAttributeValue($attrid, $newValue);
        }
        $d->store(); // verify database record
        foreach ($expectedValues as $attrid => $expectedValue) {
            $oriValue = $d->getrawValue($attrid);
            $value = $d->getHtmlAttrValue($attrid);
            
            $this->assertTrue($expectedValue === $value, sprintf("wrong value \"%s\" : \n\texpected \"%s\", \n\thas \"%s\" \n\tRaw is :\"%s\"", $attrid, print_r($expectedValue, true) , print_r($value, true) , print_r($oriValue, true)));
        }
    }
    /**
     * @dataProvider dataHtmlFormat
     */
    public function testHtmlFormat($docName, array $setValues, array $expectedValues, $target = "_self", $htmllink = true, $index = - 1, $useEntitities = true, $abstractMode = false)
    {
        $this->requiresCoreParamEquals('CORE_LANG', 'fr_FR');
        $d = new_doc(self::$dbaccess, $docName);
        $this->assertTrue($d->isAlive() , sprintf("cannot access %s document", $docName));
        foreach ($setValues as $attrid => $newValue) {
            $d->setAttributeValue($attrid, $newValue);
        }
        $d->store(); // verify database record
        $ht = new \DocHtmlFormat($d);
        foreach ($expectedValues as $attrid => $expectedValue) {
            $oriValue = $d->getrawValue($attrid);
            $oa = $d->getAttribute(($attrid));
            
            if ($index >= 0) {
                $mValue = $d->getMultipleRawValues($attrid);
                $oriValue = $mValue[$index];
            }
            $value = $ht->getHtmlValue($oa, $oriValue, $target, $htmllink, $index, $useEntitities, $abstractMode);
            
            $this->assertTrue($expectedValue === $value, sprintf("wrong value \"%s\" : \n\texpected \"%s\", \n\thas \"%s\" \n\tRaw is :\"%s\"", $attrid, print_r($expectedValue, true) , print_r($value, true) , print_r($oriValue, true)));
        }
    }
    
    public function dataHtmlFormat()
    {
        return array(
            array(
                'TST_DOCHTML0',
                "set" => array() ,
                "get" => array(
                    
                    "tst_text" => 'no text',
                    "tst_date" => 'no date',
                    "tst_time" => 'no time',
                    "tst_int" => 'no int',
                    "tst_longtext" => "no longtext",
                    "tst_double" => 'no double',
                    "tst_money" => 'no money',
                    "tst_timestamp" => 'no timestamp',
                    "tst_rel" => "no rel",
                    "tst_enum" => "no enum",
                    "tst_color" => 'no color',
                    "tst_enums" => "no enums",
                    "tst_texts" => "no text",
                    "tst_ints" => "no int",
                    "tst_moneys" => "no money",
                    "tst_doubles" => "no double",
                    "tst_times" => "no time",
                    "tst_enumms" => "no enum",
                    "tst_colors" => 'no color',
                    "tst_longtexts" => "no longtext",
                    "tst_ints1" => "",
                    "tst_doubles1" => "",
                    
                    "tst_ftext" => 'no text',
                    "tst_fdate" => 'no date',
                    "tst_ftime" => 'no time',
                    "tst_fint" => 'no int',
                    "tst_fdouble" => 'no double',
                    "tst_fmoney" => 'no money',
                    "tst_ftimestamp" => 'no timestamp',
                    "tst_fltext" => '',
                ) ,
                "target" => "_self",
                "htmllink" => 1,
                "index" => - 1,
                "useEntities" => true,
                "abstractMode" => false
            ) ,
            array(
                'TST_DOCHTML1',
                "set" => array() ,
                "get" => array(
                    "tst_text" => 'Document Un',
                    "tst_date" => '20/04/2013',
                    "tst_time" => '01:00',
                    "tst_int" => '1',
                    "tst_longtext" => "Et<br />\nLa suite en &eacute;t&eacute;&lt;b&gt;...",
                    "tst_double" => '3.1415926',
                    "tst_money" => '2,54',
                    "tst_timestamp" => '30/09/2013 10:00',
                    //"tst_rel"=>"x",
                    "tst_enum" => "A",
                    "tst_color" => '<span style="background-color:#f3f">#f3f</span>',
                    "tst_enums" => "A<br/>B<br/>C",
                    "tst_texts" => "Un<br/>Deux",
                    "tst_ints" => "0",
                    "tst_moneys" => "3,00",
                    "tst_doubles" => "-54",
                    "tst_times" => "10:00",
                    "tst_enumms" => "A",
                    "tst_colors" => '<span style="background-color:#ff33ff">#ff33ff</span><br/><span style="background-color:#45e098">#45e098</span>',
                    "tst_longtexts" => "Un<br />\nDeux<br/>Trois<br />\nQuatre",
                    "tst_ints1" => "1<br/>2<br/>3",
                    "tst_doubles1" => "<br/><br/>",
                    "tst_dates" => "20/04/2013",
                    
                    "tst_ftext" => '[Document Un]',
                    "tst_fdate" => 'samedi 20 avril 2013',
                    "tst_ftime" => ' 1h 00min 00s',
                    "tst_fdoubles" => "-54.000",
                    "tst_fint" => '001',
                    "tst_fdouble" => '3.142',
                    "tst_fmoney" => '2,54 €',
                    "tst_fdates" => "samedi 20 avril 2013",
                    "tst_ftimestamp" => 'lundi 30 septembre 2013 10h 00min 00s',
                    "tst_fltext" => '<a target="_self" title="" onmousedown="document.noselect=true;" href="http://www.here.com/Document%20Un.html" >Document Format Un</a>',
                    
                    "tst_ftexts" => "[Un]<br/>[Deux]",
                ) ,
                "target" => "_self",
                "htmllink" => 1,
                "index" => - 1,
                "useEntities" => true,
                "abstractMode" => false
            ) ,
            array(
                'TST_DOCHTML2',
                "set" => array() ,
                "get" => array(
                    "tst_text" => 'Document Deux',
                    "tst_date" => '23/05/2020',
                    "tst_time" => '14:17',
                    "tst_int" => '0',
                    "tst_longtext" => "Texte long. Html tag &lt;BR&gt;.",
                    "tst_double" => '-54',
                    "tst_money" => '3,00',
                    "tst_timestamp" => '30/09/2013 20:10',
                    //"tst_rel"=>"x",
                    "tst_enum" => "C",
                    "tst_color" => '<span style="background-color:#50ED42">#50ED42</span>',
                    "tst_enums" => "C<br/>no enums<br/>B",
                    "tst_texts" => "Un cheval noir<br/>Et un autre rouge",
                    "tst_ints" => "45<br/>3654<br/>-34",
                    "tst_moneys" => "2,54<br/>3,00<br/>2,72",
                    "tst_doubles" => "3.1415926<br/>2.7182818<br/>1.61803398875",
                    "tst_times" => "04:07",
                    "tst_dates" => '23/05/2020<br/>13/04/2017',
                    "tst_enumms" => "C",
                    "tst_colors" => '<span style="background-color:#50ED42">#50ED42</span>',
                    "tst_longtexts" => "Alpha<br />\nB&eacute;ta<br/>A<br />\nB<br />\nC",
                    "tst_ints1" => "3<br/><br/>",
                    "tst_doubles1" => "<br/>5.6<br/>7.8",
                    
                    "tst_ftext" => '[Document Deux]',
                    "tst_fdate" => 'samedi 23 mai 2020',
                    "tst_fdates" => "samedi 23 mai 2020<br/>jeudi 13 avril 2017",
                    "tst_fint" => '000',
                    "tst_ftexts" => '[Un cheval noir]<br/>[Et un autre rouge]',
                    "tst_fmoneys" => '2,54 $<br/>3,00 $<br/>2,72 $',
                    "tst_fdoubles" => "3.142<br/>2.718<br/>1.618",
                    "tst_ftime" => "14h 17min 43s",
                    "tst_fdouble" => '-54.000',
                    "tst_fmoney" => '3,00 €',
                    "tst_ftimestamp" => 'lundi 30 septembre 2013 20h 10min 41s',
                    "tst_fltext" => '<a target="_self" title="" onmousedown="document.noselect=true;" href="http://www.here.com/Document%20Deux.html" >Document Format Deux</a>',
                ) ,
                "target" => "_self",
                "htmllink" => 1,
                "index" => - 1,
                "useEntities" => true,
                "abstractMode" => false
            ) ,
            
            array(
                'TST_DOCHTML0',
                "set" => array(
                    "tst_text" => "Two",
                    "tst_fltext" => 'One',
                ) ,
                "get" => array(
                    "tst_fltext" => '<a target="_self" title="" onmousedown="document.noselect=true;" href="http://www.here.com/Two.html" >One</a>',
                ) ,
                "target" => "_self",
                "htmllink" => 1,
                "index" => - 1,
                "useEntities" => true,
                "abstractMode" => false
            ) ,
            array(
                'TST_DOCHTML0',
                "set" => array(
                    "tst_text" => "Two",
                    "tst_fltext" => 'One',
                ) ,
                "get" => array(
                    "tst_fltext" => 'One',
                ) ,
                "target" => "_self",
                "htmllink" => 0,
                "index" => - 1,
                "useEntities" => true,
                "abstractMode" => false
            ) ,
            array(
                'TST_DOCHTML0',
                "set" => array(
                    "tst_text" => "Hétérogénéité",
                ) ,
                "get" => array(
                    "tst_text" => 'H&eacute;t&eacute;rog&eacute;n&eacute;it&eacute;',
                ) ,
                "target" => "_self",
                "htmllink" => 0,
                "index" => - 1,
                "useEntities" => true,
                "abstractMode" => false
            ) ,
            array(
                'TST_DOCHTML0',
                "set" => array(
                    "tst_text" => "Hétérogénéité",
                ) ,
                "get" => array(
                    "tst_text" => 'Hétérogénéité',
                ) ,
                "target" => "_self",
                "htmllink" => 0,
                "index" => - 1,
                "useEntities" => false,
                "abstractMode" => false
            ) ,
            array(
                'TST_DOCHTML0',
                "set" => array(
                    "tst_texts" => array(
                        "Un",
                        "Deux",
                        "Trois"
                    ) ,
                ) ,
                "get" => array(
                    "tst_texts" => 'Deux',
                ) ,
                "target" => "_self",
                "htmllink" => 0,
                "index" => 1,
                "useEntities" => false,
                "abstractMode" => false
            ) ,
            array(
                'TST_DOCHTML0',
                "set" => array(
                    "tst_texts" => array(
                        "Un",
                        "Deux",
                        "Trois"
                    ) ,
                ) ,
                "get" => array(
                    "tst_texts" => 'Un',
                ) ,
                "target" => "_self",
                "htmllink" => 0,
                "index" => 0,
                "useEntities" => false,
                "abstractMode" => false
            )
        );
    }
    
    public function dataGetHtmlValues()
    {
        return array(
            array(
                'TST_DOCHTML0',
                "set" => array() ,
                "get" => array(
                    "tst_text" => 'no text',
                    "tst_date" => 'no date',
                    "tst_time" => 'no time',
                    "tst_int" => 'no int',
                    "tst_longtext" => "no longtext",
                    "tst_double" => 'no double',
                    "tst_money" => 'no money',
                    "tst_timestamp" => 'no timestamp',
                    "tst_file" => 'no file',
                    "tst_rel" => "no rel",
                    "tst_enum" => "no enum",
                    "tst_color" => 'no color',
                    "tst_enums" => "no enums",
                    "tst_texts" => "no text",
                    "tst_ints" => "no int",
                    "tst_moneys" => "no money",
                    "tst_doubles" => "no double",
                    "tst_times" => "no time",
                    "tst_enumms" => "no enum",
                    "tst_colors" => 'no color',
                    "tst_longtexts" => "no longtext",
                    "tst_ints1" => "",
                    "tst_doubles1" => "",
                    
                    "tst_ftext" => 'no text',
                    "tst_fdate" => 'no date',
                    "tst_ftime" => 'no time',
                    "tst_fint" => 'no int',
                    "tst_fdouble" => 'no double',
                    "tst_fmoney" => 'no money',
                    "tst_ftimestamp" => 'no timestamp',
                    "tst_fltext" => '',
                )
            ) ,
            array(
                'TST_DOCHTML1',
                "set" => array() ,
                "get" => array(
                    "tst_text" => 'Document Un',
                    "tst_date" => '20/04/2013',
                    "tst_time" => '01:00',
                    "tst_int" => '1',
                    "tst_longtext" => "Et<br />\nLa suite en &eacute;t&eacute;&lt;b&gt;...",
                    "tst_double" => '3.1415926',
                    "tst_money" => '2,54',
                    "tst_timestamp" => '30/09/2013 10:00',
                    //"tst_rel"=>"x",
                    "tst_enum" => "A",
                    "tst_color" => '<span style="background-color:#f3f">#f3f</span>',
                    "tst_enums" => "A<br/>B<br/>C",
                    "tst_texts" => "Un<br/>Deux",
                    "tst_ints" => "0",
                    "tst_moneys" => "3,00",
                    "tst_doubles" => "-54",
                    "tst_times" => "10:00",
                    "tst_enumms" => "A",
                    "tst_colors" => '<span style="background-color:#ff33ff">#ff33ff</span><br/><span style="background-color:#45e098">#45e098</span>',
                    "tst_longtexts" => "Un<br />\nDeux<br/>Trois<br />\nQuatre",
                    "tst_ints1" => "1<br/>2<br/>3",
                    "tst_doubles1" => "<br/><br/>",
                    "tst_dates" => "20/04/2013",
                    
                    "tst_ftext" => '[Document Un]',
                    "tst_fdate" => 'samedi 20 avril 2013',
                    "tst_ftime" => ' 1h 00min 00s',
                    "tst_fdoubles" => "-54.000",
                    "tst_fint" => '001',
                    "tst_fdouble" => '3.142',
                    "tst_fmoney" => '2,54 €',
                    "tst_fdates" => "samedi 20 avril 2013",
                    "tst_ftimestamp" => 'lundi 30 septembre 2013 10h 00min 00s',
                    "tst_fltext" => '<a target="_self" title="" onmousedown="document.noselect=true;" href="http://www.here.com/Document%20Un.html"  oncontextmenu="popdoc(event,\'http://www.here.com/Document%20Un.html\');return false;" >Document Format Un</a>',
                    
                    "tst_ftexts" => "[Un]<br/>[Deux]",
                )
            ) ,
            array(
                'TST_DOCHTML2',
                "set" => array() ,
                "get" => array(
                    "tst_text" => 'Document Deux',
                    "tst_date" => '23/05/2020',
                    "tst_time" => '14:17',
                    "tst_int" => '0',
                    "tst_longtext" => "Texte long. Html tag &lt;BR&gt;.",
                    "tst_double" => '-54',
                    "tst_money" => '3,00',
                    "tst_timestamp" => '30/09/2013 20:10',
                    //"tst_rel"=>"x",
                    "tst_enum" => "C",
                    "tst_color" => '<span style="background-color:#50ED42">#50ED42</span>',
                    "tst_enums" => "C<br/>no enums<br/>B",
                    "tst_texts" => "Un cheval noir<br/>Et un autre rouge",
                    "tst_ints" => "45<br/>3654<br/>-34",
                    "tst_moneys" => "2,54<br/>3,00<br/>2,72",
                    "tst_doubles" => "3.1415926<br/>2.7182818<br/>1.61803398875",
                    "tst_times" => "04:07",
                    "tst_dates" => '23/05/2020<br/>13/04/2017',
                    "tst_enumms" => "C",
                    "tst_colors" => '<span style="background-color:#50ED42">#50ED42</span>',
                    "tst_longtexts" => "Alpha<br />\nB&eacute;ta<br/>A<br />\nB<br />\nC",
                    "tst_ints1" => "3<br/><br/>",
                    "tst_doubles1" => "<br/>5.6<br/>7.8",
                    
                    "tst_ftext" => '[Document Deux]',
                    "tst_fdate" => 'samedi 23 mai 2020',
                    "tst_fdates" => "samedi 23 mai 2020<br/>jeudi 13 avril 2017",
                    "tst_fint" => '000',
                    "tst_ftexts" => '[Un cheval noir]<br/>[Et un autre rouge]',
                    "tst_fmoneys" => '2,54 $<br/>3,00 $<br/>2,72 $',
                    "tst_fdoubles" => "3.142<br/>2.718<br/>1.618",
                    "tst_ftime" => "14h 17min 43s",
                    "tst_fdouble" => '-54.000',
                    "tst_fmoney" => '3,00 €',
                    "tst_ftimestamp" => 'lundi 30 septembre 2013 20h 10min 41s',
                    "tst_fltext" => '<a target="_self" title="" onmousedown="document.noselect=true;" href="http://www.here.com/Document%20Deux.html"  oncontextmenu="popdoc(event,\'http://www.here.com/Document%20Deux.html\');return false;" >Document Format Deux</a>',
                ) ,
            ) ,
            array(
                'TST_DOCHTML3',
                "set" => array() ,
                "get" => array(
                    "tst_text" => 'Document Deux Bis',
                    "tst_date" => '23/05/2020',
                    "tst_time" => '14:17',
                    "tst_int" => '0',
                    "tst_longtext" => "Texte long. Html tag &lt;BR&gt;.",
                    "tst_double" => '-54',
                    "tst_money" => '3,00',
                    "tst_timestamp" => '30/09/2013 20:10',
                    //"tst_rel"=>"x",
                    "tst_enum" => "C",
                    "tst_color" => '<span style="background-color:#50ED42">#50ED42</span>',
                    "tst_enums" => "C<br/>no enums<br/>B",
                    "tst_texts" => "Un cheval noir<br/>Et un autre rouge",
                    "tst_ints" => "45<br/>3654<br/>-34",
                    "tst_moneys" => "2,54<br/>3,00<br/>2,72",
                    "tst_doubles" => "3.1415926<br/>2.7182818<br/>1.61803398875",
                    "tst_times" => "04:07",
                    "tst_dates" => '23/05/2020<br/>13/04/2017',
                    "tst_enumms" => "C",
                    "tst_colors" => '<span style="background-color:#50ED42">#50ED42</span>',
                    "tst_longtexts" => "Alpha<br />\nB&eacute;ta<br/>A<br />\nB<br />\nC",
                    "tst_ints1" => "3<br/><br/>",
                    "tst_doubles1" => "<br/>5.6<br/>7.8",
                    
                    "tst_ftext" => '[Document Deux Bis]',
                    "tst_fdate" => 'samedi 23 mai 2020',
                    "tst_fdates" => "samedi 23 mai 2020<br/>jeudi 13 avril 2017",
                    "tst_fint" => '000',
                    "tst_ftexts" => '[Un cheval noir]<br/>[Et un autre rouge]',
                    "tst_fmoneys" => '2,54 $<br/>3,00 $<br/>2,72 $',
                    "tst_fdoubles" => "3.142<br/>2.718<br/>1.618",
                    "tst_ftime" => "14h 17min 43s",
                    "tst_fdouble" => '-54.000',
                    "tst_fmoney" => '3,00 €',
                    "tst_ftimestamp" => 'lundi 30 septembre 2013 20h 10min 41s',
                    "tst_fltext" => '<a target="_self" title="" onmousedown="document.noselect=true;" href="http://www.here.com/Document%20Deux%20Bis.html"  oncontextmenu="popdoc(event,\'http://www.here.com/Document%20Deux%20Bis.html\');return false;" >Document Format Deux Bis</a>',
                ) ,
            ) ,
            array(
                'TST_DOCHTML0',
                "set" => array(
                    
                    "tst_text" => '\'"a|à|á|â|ã|ä|å|o|ò|ó|ô|õ|ö|ø|e|è|é|ê|ë|c|ç|i|ì|í|î|ï|u|ù|ú|û|ü|y|ÿ|n|ñ|<|>|&',
                    "tst_htmltext" => '<p>été comme hivers</p><br/><p>2&gt;1</p>',
                ) ,
                "get" => array(
                    "tst_text" => "'&quot;a|&agrave;|&aacute;|&acirc;|&atilde;|&auml;|&aring;|o|&ograve;|&oacute;|&ocirc;|&otilde;|&ouml;|&oslash;|e|&egrave;|&eacute;|&ecirc;|&euml;|c|&ccedil;|i|&igrave;|&iacute;|&icirc;|&iuml;|u|&ugrave;|&uacute;|&ucirc;|&uuml;|y|&yuml;|n|&ntilde;|&lt;|&gt;|&amp;",
                    "tst_htmltext" => '<div class="htmltext"><p>été comme hivers</p><br/><p>2&gt;1</p></div>',
                )
            )
        );
    }
}
?>