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

class TestAutoloader extends TestCaseDcpCommonFamily
{
    /**
     * import TST_DEFAULTFAMILY1 family
     * @static
     * @return string
     */
    protected static function getCommonImportFile()
    {
        return "PU_data_dcp_goodfamily3.ods";
    }
    /**
     * @dataProvider dataDAutoloadFamily
     */
    public function testAutoloadFamily($className)
    {
        
        $this->assertTrue(class_exists($className) , sprintf("class not found %s", $className));
    }
    
    public function dataDAutoloadFamily()
    {
        return array(
            array(
                "Account"
            ) ,
            array(
                "Doc"
            ) ,
            array(
                "DocHisto"
            ) ,
            array(
                "_TST_GOODFAMAL1"
            ) ,
            array(
                "_TST_GOODFAMAL2"
            ) ,
            array(
                "_BASE"
            ) ,
            array(
                "_IUSER"
            ) ,
            array(
                "_DIR"
            )
        );
    }
}
?>