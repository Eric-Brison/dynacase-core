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
                    "SOU_T_WU" => "U",
                    "SOU_WUW" => "W"
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
                    "SOU_T_WU" => "U",
                    "SOU_WUW" => "S"
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
                    "SOU_T_WU" => "R",
                    "SOU_WUW" => "R"
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
                    "SOU_T_WU" => "H",
                    "SOU_WUW" => "H"
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
                    "SOU_T_WU" => "U",
                    "SOU_WUW" => "W"
                )
            )
        );
    }
}
?>