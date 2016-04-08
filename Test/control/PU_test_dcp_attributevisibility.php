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

class TestAttributeVisibility extends TestCaseDcpCommonFamily
{
    /**
     * import TST_FAMSETVALUE family
     * @static
     * @return string
     */
    protected static function getCommonImportFile()
    {
        return "PU_data_dcp_visibilityfamily.ods";
    }
    /**
     * @dataProvider dataVisibilities
     */
    public function testVisibility($maskId, array $visibilities)
    {
        $d = createDoc(self::$dbaccess, "TST_FAMVISIBILITY");
        $this->assertTrue(is_object($d) , "cannot create TST_FAMVISIBILITY document");
        
        if ($maskId) {
            $d->ApplyMask($maskId);
        }
        foreach ($visibilities as $attrid => $extpectVis) {
            $oa = $d->getAttribute($attrid);
            $this->assertTrue(is_object($oa) , sprintf("cannot find %s attribute", $attrid));
            $this->assertEquals($extpectVis, $oa->mvisibility, sprintf("wrong visibility for %s", $attrid));
        }
    }
    
    public function dataVisibilities()
    {
        return array(
            array(
                "mask" => '',
                "vis" => array(
                    "SOU_F_S" => "S",
                    "SOU_SW" => "S",
                    "SOU_T_SU" => "U",
                    "SOU_SUW" => "S",
                    "SOU_T_WU" => "U",
                    "SOU_WUW" => "W",
                    "SOU_T_HU" => "H",
                    "SOU_HUW" => "H",
                    "SOU_IW" => "I",
                    "SOU_IH" => "I",
                    "SOU_IR" => "I",
                    "SOU_IO" => "I",
                    "SOU_IS" => "I",
                    "SOU_RW" => "R",
                    "SOU_RH" => "H",
                    "SOU_RR" => "R",
                    "SOU_RO" => "H",
                    "SOU_RS" => "R"
                )
            ) ,
            array(
                "mask" => 'TST_S_VIS',
                "vis" => array(
                    "SOU_F_S" => "S",
                    "SOU_SW" => "S",
                    "SOU_T_SU" => "U",
                    "SOU_SUW" => "S",
                    "SOU_T_WU" => "U",
                    "SOU_WUW" => "S",
                    "SOU_T_HU" => "U",
                    "SOU_HUW" => "S",
                    "SOU_IW" => "S", // SW
                    "SOU_IH" => "H", // SH
                    "SOU_IR" => "R", // SR
                    "SOU_IO" => "S", // SO
                    "SOU_IS" => "S", // SS
                    "SOU_RW" => "S", // SW
                    "SOU_RH" => "H", // SH
                    "SOU_RR" => "R", // SR
                    "SOU_RO" => "S", // SO
                    "SOU_RS" => "S"
                    // SS
                    
                )
            ) ,
            array(
                "mask" => 'TST_R_VIS',
                "vis" => array(
                    "SOU_F_S" => "S",
                    "SOU_SW" => "S",
                    "SOU_T_SU" => "U",
                    "SOU_SUW" => "S",
                    "SOU_T_WU" => "R",
                    "SOU_WUW" => "R",
                    "SOU_T_HU" => "R",
                    "SOU_HUW" => "R",
                    "SOU_IW" => "R", // RW
                    "SOU_IH" => "H", // RH
                    "SOU_IR" => "R", // RR
                    "SOU_IO" => "H", // RO
                    "SOU_IS" => "R", // RS
                    "SOU_RW" => "R", // RW
                    "SOU_RH" => "H", // RH
                    "SOU_RR" => "R", // RR
                    "SOU_RO" => "H", // RO
                    "SOU_RS" => "R"
                    // RS
                    
                )
            ) ,
            array(
                "mask" => 'TST_H_VIS',
                "vis" => array(
                    "SOU_F_S" => "S",
                    "SOU_SW" => "S",
                    "SOU_T_SU" => "U",
                    "SOU_SUW" => "S",
                    "SOU_T_WU" => "H",
                    "SOU_WUW" => "H",
                    "SOU_T_HU" => "H",
                    "SOU_HUW" => "H",
                    "SOU_IW" => "H", // HW
                    "SOU_IH" => "H", // HH
                    "SOU_IR" => "H", // HR
                    "SOU_IO" => "H", // HO
                    "SOU_IS" => "H", // HS
                    "SOU_RW" => "H", // HW
                    "SOU_RH" => "H", // HH
                    "SOU_RR" => "H", // HR
                    "SOU_RO" => "H", // HO
                    "SOU_RS" => "H"
                    // HS
                    
                )
            ) ,
            array(
                "mask" => 'TST_W_VIS',
                "vis" => array(
                    "SOU_F_S" => "S",
                    "SOU_SW" => "S",
                    "SOU_T_SU" => "U",
                    "SOU_SUW" => "S",
                    "SOU_T_WU" => "U",
                    "SOU_WUW" => "W",
                    "SOU_T_HU" => "U",
                    "SOU_HUW" => "W",
                    "SOU_IW" => "W", // WW
                    "SOU_IH" => "H", // WH
                    "SOU_IR" => "R", // WR
                    "SOU_IO" => "O", // WO
                    "SOU_IS" => "S", // WS
                    "SOU_RW" => "W", // WW
                    "SOU_RH" => "H", // WH
                    "SOU_RR" => "R", // WR
                    "SOU_RO" => "O", // WO
                    "SOU_RS" => "S"
                    // WS
                    
                )
            )
        );
    }
}
?>