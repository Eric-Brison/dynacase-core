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

class TestExtendProfil extends TestCaseDcpCommonFamily
{
    /**
     * import TST_FAMSETVALUE family
     * @static
     * @return string
     */
    protected static function getCommonImportFile()
    {
        return "PU_data_dcp_extendprofil.ods";
    }
    /**
     * @dataProvider dataWorkflowExtendProfil
     */
    public function testWorkflowExtendProfil($wfName, $aclName, $login, $expectedControl)
    {
        $this->sudo($login);
        $d = new_doc(self::$dbaccess, $wfName);
        $err = $d->control($aclName);
        if ($expectedControl) {
            $this->assertEmpty($err, "acl $aclName not granted for $login : $err");
        } else {
            
            $this->assertNotEmpty($err, "acl $aclName granted for $login. It must not");
        }
        
        $this->exitSudo();
    }
    /**
     * @dataProvider dataDynamicWorkflowExtendProfil
     */
    public function testDynamicWorkflowExtendProfil($wfName, $docName, $aclName, $login, $expectedControl)
    {
        $this->sudo($login);
        
        $d = new_doc(self::$dbaccess, $docName);
        /**
         * @var \WDoc $w
         */
        $w = new_doc(self::$dbaccess, $wfName);
        $w->set($d);
        $err = $w->control($aclName);
        if ($expectedControl) {
            $this->assertEmpty($err, "acl $aclName not granted for $login : $err");
        } else {
            
            $this->assertNotEmpty($err, "acl $aclName granted for $login. It must not");
        }
        
        $this->exitSudo();
    }
    public function dataDynamicWorkflowExtendProfil()
    {
        return array(
            array(
                'WTST_WEXTPROFDYN',
                'TST_EXTPRF2',
                'T1',
                'sublue',
                false
            ) ,
            array(
                'WTST_WEXTPROFDYN',
                'TST_EXTPRF2',
                'T3',
                'sublue',
                true
            ) ,
            array(
                'WTST_WEXTPROFDYN',
                'TST_EXTPRF2',
                'T35',
                'sublue',
                true
            ) ,
            array(
                'WTST_WEXTPROFDYN',
                'TST_EXTPRF2',
                'T36',
                'sublue',
                true
            ) ,
            array(
                'WTST_WEXTPROFDYN',
                'TST_EXTPRF2',
                'T35',
                'sugreen',
                false
            ) ,
            array(
                'WTST_WEXTPROFDYN',
                'TST_EXTPRF2',
                'T35',
                'suryellow',
                false
            ) ,
            array(
                'WTST_WEXTPROFDYN',
                'TST_EXTPRF3',
                'T35',
                'sublue',
                false
            ) ,
            array(
                'WTST_WEXTPROFDYN',
                'TST_EXTPRF3',
                'T36',
                'sublue',
                false
            ) ,
            array(
                'WTST_WEXTPROFDYN',
                'TST_EXTPRF3',
                'T35',
                'suryellow',
                true
            )
        );
    }
    public function dataWorkflowExtendProfil()
    {
        return array(
            array(
                'WTST_WEXTPROF',
                'T1',
                'sublue',
                false
            ) ,
            array(
                'WTST_WEXTPROF',
                'T3',
                'sublue',
                true
            ) ,
            array(
                'WTST_WEXTPROF',
                'T3',
                'sugreen',
                false
            ) ,
            array(
                'WTST_WEXTPROF',
                'T3',
                'suryellow',
                false
            ) ,
            array(
                'WTST_WEXTPROF',
                'T4',
                'sublue',
                true
            ) ,
            array(
                'WTST_WEXTPROF',
                'T4',
                'sugreen',
                true
            ) ,
            array(
                'WTST_WEXTPROF',
                'T4',
                'suryellow',
                false
            ) ,
            array(
                'WTST_WEXTPROF',
                'T35',
                'suryellow',
                true
            ) ,
            array(
                'WTST_WEXTPROF',
                'view',
                'sublue',
                true
            ) ,
            array(
                'WTST_WEXTPROF',
                'view',
                'suryellow',
                false
            ) ,
            //TST_EXTPRF1
            
            //TST_EXTPRF2
            
            
        );
    }
}
?>