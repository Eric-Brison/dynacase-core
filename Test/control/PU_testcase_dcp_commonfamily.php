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

require_once 'PU_testcase_dcp.php';

class TestCaseDcpCommonFamily extends TestCaseDcp
{
    
    protected function tearDown()
    {
        if (!self::$odb) {
            self::$odb = new \DbObj(self::$dbaccess);
        }
        self::$odb->rollbackPoint('testunit');
    }
    
    protected function setUp()
    {
        self::$odb->savePoint('testunit');
    }
    /**
     * return file to import before run test
     * could be an array if several files
     * @static
     * @return string|array
     */
    protected static function getCommonImportFile()
    {
        return '';
    }
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        
        self::connectUser();
        self::beginTransaction();
        
        $cf = static ::getCommonImportFile();
        if ($cf) {
            if (!is_array($cf)) $cf = array(
                $cf
            );
            foreach ($cf as $f) {
                self::importDocument($f);
            }
        }
    }
    
    public static function tearDownAfterClass()
    {
        self::rollbackTransaction();
    }
}
?>