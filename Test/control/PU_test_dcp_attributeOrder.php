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

class TestAttributeOrder extends TestCaseDcpCommonFamily
{
    /**
     * import some documents
     * @static
     * @return string
     */
    protected static function getCommonImportFile()
    {
        return array(
            "PU_data_dcp_orderfamilies.ods"
        );
    }
    /**
     * @dataProvider dataOrderAttribute
     * @return \Doc
     */
    public function testOrderAttribute($family, $expectedOrders)
    {
        /**
         * @var \DocFam $fam
         */
        $fam = new_doc(self::$dbaccess, $family);
        $this->assertTrue($fam->isAlive() , sprintf("family %s not alive", $family));
        
        $attributes = $fam->getAttributes();
        $orders = [];
        $k = 0;
        foreach ($attributes as $attribute) {
            if ($attribute && $attribute->id !== \Adoc::HIDDENFIELD) {
                $orders[$expectedOrders[$k]] = $attribute->id;
                $k++;
            }
        }
        /**
         * @var \BasicAttribute $prevAttr
         */
        $prevAttr = null;
        $k = 0;
        foreach ($orders as $attrid) {
            $this->assertEquals(strtolower($expectedOrders[$k]) , $attrid, sprintf("Not good found %s > %s : %s", $attrid, $expectedOrders[$k], print_r($orders, true)));
            $k++;
        }
    }
    public function dataOrderAttribute()
    {
        $aOrder = array(
            "TST_AF1000",
            "TST_A2000",
            "TST_A3000",
            "TST_A4000",
            "TST_AA5000",
            "TST_A6000",
            "TST_A7000",
            "TST_AT8000",
            "TST_AF9000",
            "TST_A10000",
            "TST_A11000",
            "TST_A12000",
            "TST_AF13000",
            "TST_A14000",
            "TST_A15000",
            "TST_AA16000",
            "TST_A17000",
            "TST_A18000",
            "TST_A19000"
        );
        $bOrder = array(
            "TST_BC500",
            "TST_B550",
            "TST_B600",
            "TST_AF1000",
            "TST_A2000",
            "TST_B2500",
            "TST_A3000",
            "TST_A4000",
            "TST_AA5000",
            "TST_A6000",
            "TST_A7000",
            "TST_B7100",
            "TST_B7200",
            "TST_BT7300",
            "TST_BF7400",
            "TST_B7500",
            "TST_B7600",
            "TST_AT8000",
            "TST_AF9000",
            "TST_A10000",
            "TST_A11000",
            "TST_A12000",
            "TST_AF13000",
            "TST_A14000",
            "TST_A15000",
            "TST_AA16000",
            "TST_A17000",
            "TST_A18000",
            "TST_A19000",
            "TST_BT20000",
            "TST_BF21000",
            "TST_B22000",
            "TST_B23000"
        );
        $cOrder = array(
            "TST_BC500",
            "TST_B550",
            "TST_B600",
            "TST_AF1000",
            "TST_A2000",
            "TST_B2500",
            "TST_A3000",
            "TST_A4000",
            "TST_AA5000",
            "TST_A6000",
            "TST_A7000",
            "TST_B7100",
            "TST_B7200",
            "TST_BT7300",
            "TST_BF7400",
            "TST_B7500",
            "TST_B7600",
            "TST_AT8000",
            "TST_AF9000",
            "TST_A10000",
            "TST_A11000",
            "TST_A12000",
            "TST_AF13000",
            "TST_A14000",
            "TST_A15000",
            "TST_AA16000",
            "TST_A17000",
            "TST_A18000",
            "TST_A19000",
            "TST_CT19500",
            "TST_CF19600",
            "TST_C19700",
            "TST_C19800",
            "TST_BT20000",
            "TST_BF21000",
            "TST_B22000",
            "TST_B23000"
        );
        $dOrder = array(
            "TST_BC500",
            "TST_B600",
            "TST_B550",
            "TST_AF1000",
            "TST_A2000",
            "TST_B2500",
            "TST_A3000",
            "TST_A4000",
            "TST_AA5000",
            "TST_A6000",
            "TST_A7000",
            "TST_B7100",
            "TST_B7200",
            "TST_BT7300",
            "TST_BF7400",
            "TST_B7500",
            "TST_B7600",
            "TST_AT8000",
            "TST_AF13000",
            "TST_A14000",
            "TST_A15000",
            "TST_AA16000",
            "TST_A17000",
            "TST_A18000",
            "TST_A19000",
            "TST_AF9000",
            "TST_A10000",
            "TST_A11000",
            "TST_A12000",
            "TST_BT20000",
            "TST_BF21000",
            "TST_B22000",
            "TST_B23000"
        );
        $eOrder = array_merge($cOrder, ["TST_ET25000", "TST_EF25100", "TST_E25200"]);
        return array(
            
            array(
                "TST_ORDERAUTOA",
                $aOrder
            ) ,
            array(
                "TST_ORDERAUTOB",
                $bOrder
            ) ,
            array(
                "TST_ORDERAUTOC",
                $cOrder
            ) ,
            array(
                "TST_ORDERAUTOD",
                $dOrder
            ) ,
            array(
                "TST_ORDERAUTOE",
                $eOrder
            ) ,
            array(
                "TST_ORDERRELA",
                $aOrder
            ) ,
            array(
                "TST_ORDERRELB",
                $bOrder
            ) ,
            array(
                "TST_ORDERRELC",
                $cOrder
            ) ,
            array(
                "TST_ORDERRELD",
                $dOrder
            ) ,
            array(
                "TST_ORDERRELE",
                $eOrder
            ) ,
            array(
                "TST_ORDERNUMA",
                $aOrder
            ) ,
            array(
                "TST_ORDERNUMB",
                $bOrder
            ) ,
            array(
                "TST_ORDERNUMC",
                $cOrder
            ) ,
            array(
                "TST_ORDERNUMD",
                $dOrder
            ) ,
            array(
                "TST_ORDERNUME",
                $eOrder
            )
        );
    }
}
