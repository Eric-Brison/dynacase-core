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

class TestFormatInvisibleCollection extends TestCaseDcpCommonFamily
{
    /**
     * import TST_FMTCOL
     * @static
     * @return string
     */
    protected static function getCommonImportFile()
    {
        return array(
            "PU_data_dcp_invisible.ods"
        );
    }
    
    protected $famName = 'TST_INVISIBLEFAMILY1';
    /**
     * @dataProvider dataRenderInvisibleCollection
     */
    public function testRenderInvisibleCollection($login, $docName, array $expectedValues)
    {
        $this->sudo($login);
        $s = new \SearchDoc(self::$dbaccess, $this->famName);
        $s->setObjectReturn();
        $dl = $s->search()->getDocumentList();
        
        $fc = new \FormatCollection();
        $fc->useCollection($dl);
        $fc->relationNoAccessText = 'no grant';
        $fc->addProperty($fc::propName);
        foreach ($expectedValues as $aid => $value) {
            $fc->addAttribute($aid);
        }
        
        $r = $fc->render();
        foreach ($expectedValues as $aid => $value) {
            $this->assertEquals($value, $this->getRenderValue($r, $docName, $aid)->displayValue, sprintf("%s [%s]<>\n%s", $aid, $value, print_r($this->getRenderValue($r, $docName, $aid) , true)));
        }
        $this->exitSudo();
    }
    /**
     * @param array $r
     * @param $docName
     * @param $attrName
     * @return \StandardAttributeValue
     */
    private function getRenderValue(array $r, $docName, $attrName)
    {
        foreach ($r as $format) {
            if ($format["properties"]["name"] == $docName) {
                return $format["attributes"][$attrName];
            }
        }
        return null;
    }
    
    public function dataRenderInvisibleCollection()
    {
        return array(
            array( // MASK TST_INVMASK_E1
                "uinvisible_1",
                "TST_INVISIBLE_DOC1",
                array(
                    "tst_title" => "Titre 1",
                    "tst_text" => \FormatCollection::noAccessText,
                    "tst_number" => "1",
                    "tst_longtext" => \FormatCollection::noAccessText,
                    "tst_decimal" => \FormatCollection::noAccessText
                )
            ) ,
            array( // MASK TST_INVMASK_E2
                "uinvisible_1",
                "TST_INVISIBLE_DOC2",
                array(
                    "tst_title" => "Titre 2",
                    "tst_text" => \FormatCollection::noAccessText,
                    "tst_number" => \FormatCollection::noAccessText,
                    "tst_longtext" => "Deux long",
                    "tst_decimal" => "2,2"
                )
            ) ,
            array( // MASK TST_INVMASK_E1
                "uinvisible_1",
                "TST_INVISIBLE_DOC3",
                array(
                    "tst_title" => "Titre 3",
                    "tst_text" => \FormatCollection::noAccessText,
                    "tst_number" => "3",
                    "tst_longtext" => \FormatCollection::noAccessText,
                    "tst_decimal" => \FormatCollection::noAccessText
                )
            ) ,
            array( // MASK TST_INVMASK_E1
                "uinvisible_1",
                "TST_INVISIBLE_DOC4",
                array(
                    "tst_title" => "Titre 4",
                    "tst_text" => \FormatCollection::noAccessText,
                    "tst_number" => "4",
                    "tst_longtext" => \FormatCollection::noAccessText,
                    "tst_decimal" => \FormatCollection::noAccessText
                )
            ) ,
            array( // MASK TST_INVMASK_E1
                "uinvisible_1",
                "TST_INVISIBLE_DOC5",
                array(
                    "tst_title" => "Titre 5",
                    "tst_text" => \FormatCollection::noAccessText,
                    "tst_number" => \FormatCollection::noAccessText,
                    "tst_longtext" => \FormatCollection::noAccessText,
                    "tst_decimal" => \FormatCollection::noAccessText
                )
            ) ,
            array( // MASK TST_INVMASK_E2
                "uinvisible_2",
                "TST_INVISIBLE_DOC3",
                array(
                    "tst_title" => "Titre 3",
                    "tst_text" => \FormatCollection::noAccessText,
                    "tst_number" => \FormatCollection::noAccessText,
                    "tst_longtext" => "Trois long",
                    "tst_decimal" => "3,3"
                )
            ) ,
            array( // MASK TST_INVMASK_E2
                "uinvisible_2",
                "TST_INVISIBLE_DOC4",
                array(
                    "tst_title" => "Titre 4",
                    "tst_text" => \FormatCollection::noAccessText,
                    "tst_number" => \FormatCollection::noAccessText,
                    "tst_longtext" => "Quatre long",
                    "tst_decimal" => "4,4"
                )
            ) ,
            array( // MASK TST_INVMASK_E1
                "uinvisible_3",
                "TST_INVISIBLE_DOC3",
                array(
                    "tst_title" => "Titre 3",
                    "tst_text" => \FormatCollection::noAccessText,
                    "tst_number" => "3",
                    "tst_longtext" => \FormatCollection::noAccessText,
                    "tst_decimal" => \FormatCollection::noAccessText
                )
            ) ,
            array( // MASK TST_INVMASK_E4
                "uinvisible_3",
                "TST_INVISIBLE_DOC4",
                array(
                    "tst_title" => "Titre 4",
                    "tst_text" => "Quatre",
                    "tst_number" => "4",
                    "tst_longtext" => "Quatre long",
                    "tst_decimal" => "4,4"
                )
            ) ,
            array( // no mask
                "uinvisible_4",
                "TST_INVISIBLE_DOC3",
                array(
                    "tst_title" => "Titre 3",
                    "tst_text" => \FormatCollection::noAccessText,
                    "tst_number" => \FormatCollection::noAccessText,
                    "tst_longtext" => \FormatCollection::noAccessText,
                    "tst_decimal" => \FormatCollection::noAccessText
                )
            ) ,
            array( // MASK TST_INVMASK_E4
                "uinvisible_4",
                "TST_INVISIBLE_DOC4",
                array(
                    "tst_title" => "Titre 4",
                    "tst_text" => "Quatre",
                    "tst_number" => "4",
                    "tst_longtext" => "Quatre long",
                    "tst_decimal" => "4,4"
                )
            )
        );
    }
}
?>
