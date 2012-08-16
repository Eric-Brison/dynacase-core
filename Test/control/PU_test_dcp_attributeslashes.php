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

class TestAttributeSlashes extends TestCaseDcpCommonFamily
{
    /**
     * import TST_SLASHFAMILY1 family
     * @static
     * @return string
     */
    protected static function getCommonImportFile()
    {
        return "PU_data_dcp_attributeslashes.ods";
    }
    
    protected $famName = 'TST_SLASHFAMILY1';
    /**
     * @dataProvider dataOption
     */
    public function testOption($attrid, $optname, $expectedvalue)
    {
        static $d = null;
        if ($d === null) {
            $d = createDoc(self::$dbaccess, $this->famName);
            $this->assertTrue(is_object($d) , sprintf("cannot create %s document", $this->famName));
        }
        $oa = $d->getAttribute($attrid);
        $this->assertNotEmpty($oa, sprintf("attribute %s not found in %s family", $attrid, $this->famName));
        $value = $oa->getOption($optname);
        $this->assertEquals($expectedvalue, $value, sprintf("not the expected default value attribute %s", $attrid));
        
        return $d;
    }
    /**
     * @dataProvider dataPhpfunc
     */
    public function testPhpfunc($attrid, $expectedvalue)
    {
        static $d = null;
        if ($d === null) {
            $d = createDoc(self::$dbaccess, $this->famName);
            $this->assertTrue(is_object($d) , sprintf("cannot create %s document", $this->famName));
        }
        /**
         * @var \NormalAttribute $oa
         */
        $oa = $d->getAttribute($attrid);
        $this->assertNotEmpty($oa, sprintf("attribute %s not found in %s family", $attrid, $this->famName));
        $value = $oa->phpfunc;
        $this->assertEquals($expectedvalue, $value, sprintf("not the expected default value attribute %s", $attrid));
        
        return $d;
    }
    
    public function dataOption()
    {
        return array(
            array(
                "TST_TITLE",
                "etitle",
                "test"
            ) ,
            array(
                "TST_NUMBER1",
                "etitle",
                '"number"'
            ) ,
            array(
                "TST_NUMBER2",
                "etitle",
                'A\\B'
            ) ,
            array(
                "TST_NUMBER3",
                "etitle",
                'C\\\\D'
            ) ,
            array(
                "TST_NUMBER4",
                "quote",
                'test"'
            ) ,
            array(
                "TST_NUMBER4",
                "other",
                '\\"test\\"'
            ) ,
            array(
                "TST_TEXT4",
                "jsonconf",
                '{"addPlugins": ["docattr"],"doclink": {"famId": "DIR", "filter" : "title=\\\\\'Comptes\\\\\'"}}'
            )
        );
    }
    
    public function dataPhpfunc()
    {
        return array(
            array(
                "TST_NUMBER1",
                "::isOne()",
            ) ,
            array(
                "TST_NUMBER2",
                '::oneMore("3")'
            ) ,
            array(
                "TST_TEXT3",
                '::commaConcat(TST_TEXT1, TST_TEXT2)'
            )
        );
    }
}
?>