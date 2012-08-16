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

class TestWorflowTransition extends TestCaseDcpCommonFamily
{
    /**
     * import TST_DEFAULTFAMILY1 family
     * @static
     * @return string
     */
    protected static function getCommonImportFile()
    {
        return "PU_data_dcp_impworkflowfamilym0m3.ods";
    }
    /**
     * @dataProvider dataTransitionCondition
     * @param $start
     * @param $end
     * @param $passExpectedError
     */
    public function testTransitionCondition($start, $end, $passExpectedError)
    {
        $wf = new_doc(self::$dbaccess, "WTST_M0M3");
        $this->assertTrue($wf->isAlive() , "cannot find document WTST_M0M3");
        
        $d = createDoc(self::$dbaccess, "TST_WFFAMM0M3");
        $d->setTitle('zou');
        $d->wid = $wf->id;
        $d->state = $start;
        $err = $d->store();
        $this->assertEmpty($err, "cannot create TST_WFFAMM0M3 document");
        
        $err = $d->setState($end);
        if ($passExpectedError == '') {
            $this->assertEmpty($err, sprintf("transition %s -> %s is not passed : %s", $start, $end, $err));
        } else {
            
            $this->assertContains($passExpectedError, $err, sprintf("transition %s -> %s is passed and must not", $start, $end));
        }
    }
    /**
     * @dataProvider dataTransitionPostAction
     * @param $start
     * @param $end
     * @param $passExpectedMsg
     */
    public function testTransitionPostAction($start, $end, $passExpectedMsg)
    {
        $wf = new_doc(self::$dbaccess, "WTST_M0M3");
        $this->assertTrue($wf->isAlive() , "cannot find document WTST_M0M3");
        
        $d = createDoc(self::$dbaccess, "TST_WFFAMM0M3");
        $d->setTitle('zou');
        $d->wid = $wf->id;
        $d->state = $start;
        $err = $d->store();
        $this->assertEmpty($err, "cannot create TST_WFFAMM0M3 document");
        
        $err = $d->setState($end, '', false, true, true, true, true, true, true, $msg);
        $this->assertEmpty($err, sprintf("transition %s -> %s is not passed : %s", $start, $end, $err));
        
        if ($passExpectedMsg == '') {
            $this->assertEmpty($err, sprintf("transition %s -> %s is not passed : %s", $start, $end, $err));
        } else {
            $this->assertContains($passExpectedMsg, $msg, sprintf("transition %s -> %s is passed and must not", $start, $end));
        }
    }
    public function dataTransitionCondition()
    {
        return array(
            array(
                \WTestM0M3::SA,
                \WTestM0M3::SC,
                ''
            ) ,
            array(
                \WTestM0M3::SA,
                \WTestM0M3::SB,
                'm0 forbidden'
            ) ,
            array(
                \WTestM0M3::SA,
                \WTestM0M3::SD,
                ''
            ) ,
            array(
                \WTestM0M3::SB,
                \WTestM0M3::SC,
                'm1 forbidden'
            ) ,
            array(
                \WTestM0M3::SB,
                \WTestM0M3::SD,
                ''
            )
        );
    }
    
    public function dataTransitionPostAction()
    {
        return array(
            array(
                \WTestM0M3::SA,
                \WTestM0M3::SC,
                ''
            ) ,
            array(
                \WTestM0M3::SA,
                \WTestM0M3::SD,
                'm3 pass'
            ) ,
            array(
                \WTestM0M3::SB,
                \WTestM0M3::SD,
                'm2 pass'
            )
        );
    }
}
?>