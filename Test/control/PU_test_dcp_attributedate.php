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

class TestAttributeDate extends TestCaseDcpCommonFamily
{
    const testFamily = 'TST_DATETRANSFERT';

    /**
     * import TST_DATETRANSFERT family
     * @static
     * @return string
     */
    protected static function getCommonImportFile()
    {
        return "PU_data_dcp_datefamily.ods";
    }

    /**
     * @dataProvider dataTransfert
     */
    public function testDateTransfert($docid, array $expectedValues)
    {
        if (getLcdate() != 'iso') $this->markTestIncomplete("database date must be configured as iso format");
        $origin = new_doc(self::$dbaccess, $docid);
        $this->assertTrue($origin->isAlive(), "cannot find $docid document");

        $target = createDoc(self::$dbaccess, self::testFamily);
        $this->assertTrue(is_object($target), sprintf("cannot create %s ", self::testFamily));

        $target->transfertValuesFrom($origin);
        $err = $target->add();
        $this->assertEmpty($err, sprintf("cannot add %s ", self::testFamily));
        $newId = $target->id;
        clearCacheDoc();
        $target = new_Doc(self::$dbaccess, $newId);
        $this->verifyValues($target, $expectedValues);


    }

    /**
     * @dataProvider dataTransfert
     * @depends testDateTransfert
     *
     */
    public function testDateCopy($docid, array $expectedValues)
    {
        $origin = new_doc(self::$dbaccess, $docid);
        $this->assertTrue($origin->isAlive(), "cannot find $docid document");
        $target = $origin->copy();
        $target->transfertValuesFrom($origin);
        $this->verifyValues($target, $expectedValues);
        $this->verifyHtmlValues($origin, $target, array_keys($expectedValues));

    }

    private function verifyValues(\Doc $test, array $expectedValues)
    {
        foreach ($expectedValues as $k => $expectValue) {
            if (is_array($expectValue)) $targetValue = $test->getTValue($k);
            else $targetValue = $test->getValue($k);
            $this->assertEquals($expectValue, $targetValue, sprintf("wrong value %s", $k));
        }
    }

    private function verifyHtmlValues(\Doc $origin, \Doc $target, array $attributeIds)
    {
        foreach ($attributeIds as $attrid) {
            $tv=$origin->getHtmlAttrValue($attrid);
            $to=$target->getHtmlAttrValue($attrid);
            $this->assertEquals($tv, $to, sprintf("wrong html value %s", $attrid));
        }
    }

    public function dataTransfert()
    {
        return array(
            array(
                'TST_DATEORIGIN1',
                array("tst_date" => '2012-02-29',
                    "tst_ts" => '2012-02-29 12:23:00',
                    'tst_dates' => array('2012-02-29', '2012-03-13'),
                    'tst_tss' => array('2012-02-29 00:00', '2012-03-13 13:45:56'))
            )
        );
    }


}

?>