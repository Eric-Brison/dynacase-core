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

class TestGetDocValue extends TestCaseDcpCommonFamily
{
    public $famName = "TST_FAMGETDOCVALUE";
    /**
     * import TST_FAMSETVALUE family
     * @static
     * @return string
     */
    protected static function getCommonImportFile()
    {
        return "PU_data_dcp_getdocvaluefamily.ods";
    }
    /**
     * @dataProvider dataDirectGetDocValue
     */
    public function testDirectGetDocValue($docName, $attrid, $expectValue)
    {
        $d = createDoc(self::$dbaccess, $this->famName);
        $this->assertTrue(is_object($d) , sprintf("cannot create %s document", $this->famName));
        $docid = getIdFromName(self::$dbaccess, $docName);
        $value = $d->getDocValue($docid, $attrid);
        
        $this->assertEquals($expectValue, $value, "getDocValue $attrid wrong value");
    }
    /**
     * @dataProvider dataDirectGetDocValueLogicalName
     */
    public function testDirectGetDocValueLogicalName($docName, $attrid, $expectValue)
    {
        $d = createDoc(self::$dbaccess, $this->famName);
        $this->assertTrue(is_object($d) , sprintf("cannot create %s document", $this->famName));
        $value = $d->getDocValue($docName, $attrid);
        
        $this->assertEquals($expectValue, $value, "getDocValue $attrid wrong value");
    }
    /**
     * @dataProvider dataLatestGetDocValue
     */
    public function testLatestGetDocValue($docName, $attrid, $expectValue)
    {
        $d = new_doc(self::$dbaccess, $docName);
        $this->assertTrue($d->isAlive() , sprintf("cannot find %s document", $docName));
        $d->revise('test');
        $d->setValue($attrid, $expectValue);
        $d->store();
        
        $value = $d->getDocValue($d->initid, $attrid, 'notFound', true);
        
        $this->assertEquals($expectValue, $value, "getDocValue $attrid wrong value from initid");
        
        $value = $d->getDocValue($d->id, $attrid, 'notFound', true);
        
        $this->assertEquals($expectValue, $value, "getDocValue $attrid wrong value from id");
    }
    /**
     * @dataProvider dataFirstGetDocValue
     */
    public function testFirstGetDocValue($docName, $attrid, $newvalue, $expectValue)
    {
        $d = new_doc(self::$dbaccess, $docName);
        $this->assertTrue($d->isAlive() , sprintf("cannot find %s document", $docName));
        $d->revise('test');
        $d->setValue($attrid, $newvalue);
        $d->store();
        
        $value = $d->getDocValue($d->initid, $attrid, 'notFound', false);
        
        $this->assertEquals($expectValue, $value, "getDocValue $attrid wrong value");
    }
    public function dataFirstGetDocValue()
    {
        
        return array(
            array(
                'TST_FGV1',
                "tst_title",
                "Titre nouveau",
                "Titre Un"
            ) ,
            array(
                'TST_FGV2',
                "tst_int",
                "34",
                "2"
            )
        );
    }
    
    public function dataDirectGetDocValue()
    {
        
        return array(
            array(
                'TST_FGV1',
                "tst_title",
                "Titre Un"
            ) ,
            array(
                'TST_FGV2',
                "tst_int",
                "2"
            )
        );
    }
    public function dataDirectGetDocValueLogicalName()
    {
        
        return array(
            array(
                'TST_FGV1',
                "tst_title",
                "Titre Un"
            ) ,
            array(
                'TST_FGV2',
                "tst_int",
                "2"
            )
        );
    }
    public function dataLatestGetDocValue()
    {
        
        return array(
            array(
                'TST_FGV1',
                "tst_title",
                "Titre Un bis"
            ) ,
            array(
                'TST_FGV2',
                "tst_int",
                "21"
            )
        );
    }
}
?>
