<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */

namespace PU;
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package DCP
 */

require_once 'PU_testcase_dcp_commonfamily.php';

class TestAttributeValue extends TestCaseDcpCommonFamily
{
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
        $d = createDoc(self::$dbaccess, "TST_FAMSETVALUE");
        $this->assertTrue(is_object($d) , "cannot create TST_FAMSETVALUE document");
        
        $err = $d->setValue($attrid, $value);
        $this->assertEmpty($err, sprintf("setvalue error : %s", $err));
        if ($converted === false) $converted = $value;
        
        $this->assertEquals($converted, $d->getValue($attrid) , "setvalue / getvalue : not the same");
        $err = $d->store();
        $this->assertEmpty($err, sprintf("store error : %s", $err));
        return $d;
    }
    /**
     * @dataProvider wrongValues
     */
    public function testWrongSetValue($attrid, $value)
    {
        $d = createDoc(self::$dbaccess, "TST_FAMSETVALUE");
        $this->assertTrue(is_object($d) , "cannot create TST_FAMSETVALUE document");
        
        $err = $d->setValue($attrid, $value);
        $this->assertNotEmpty($err, sprintf("setvalue error : %s", $err));
        $this->assertEmpty($d->getValue($attrid));
        
        return $d;
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
                '20/11/2011'
            ) ,
            array(
                'TST_DATE',
                '2011-11-21',
                "21/11/2011"
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
                '21/11/2011 12:34'
            ) ,
            array(
                'TST_TIMESTAMP',
                '2011-11-21T12:34',
                '21/11/2011T12:34'
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