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

require_once 'PU_testcase_dcp_document.php';
class TestTagFamily extends TestCaseDcpDocument
{
    
    protected function setUp()
    {
        $err = simpleQuery(self::$dbaccess, "savepoint z", $r);
    }
    
    protected function tearDown()
    {
        $err = simpleQuery(self::$dbaccess, "rollback to savepoint z", $r);
    }
    
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        
        self::connectUser();
        self::beginTransaction();
        
        self::importDocument("PU_data_dcp_tagfamily.ods");
    }
    
    public static function tearDownAfterClass()
    {
        self::rollbackTransaction();
    }
    /**
     * @param string $familyName Name of family
     * @param string $tagExpected Expected value of tag
     * @return void
     * @dataProvider dataTagableFamily
     */
    public function testTagableFamily($familyName, $tagExpected)
    {
        /**
         * @var $ndoc \DocFam
         */
        $ndoc = new_Doc(self::$dbaccess, $familyName);
        if ($ndoc->isAlive()) {
            //error_log(sprintf("DOC ID == %s --- doc tagable == %s", $ndoc->initid, $ndoc->tagable));
            $this->assertEquals($tagExpected, $ndoc->tagable, sprintf("Tag found [%s] is not tag expected [%s]", $ndoc->tagable, $tagExpected));
        }
    }
    /**
     * @param string $familyFile
     * @param string $expectedErrors
     * @dataProvider dataBadTagableFamily
     */
    public function testBadTagableFamily($familyFile, $expectedErrors)
    {
        $err = '';
        try {
            $this->importDocument($familyFile);
        }
        catch(\Exception $e) {
            $err = $e->getMessage();
        }
        $this->assertNotEmpty($err, "no import error detected");
        if (!is_array($expectedErrors)) $expectedErrors = array(
            $expectedErrors
        );
        foreach ($expectedErrors as $expectedError) {
            $this->assertContains($expectedError, $err, sprintf("not the correct error reporting : %s", $err));
        }
    }
    
    public function dataTagableFamily()
    {
        return array(
            array(
                "TST_FAMILYTAG1",
                "public"
            ) ,
            array(
                "TST_FAMILYTAG2",
                "no"
            ) ,
            array(
                "TST_FAMILYTAG3",
                ""
            )
        );
    }
    
    public function dataBadTagableFamily()
    {
        return array(
            array(
                "PU_data_dcp_tagfamilyerror.ods",
                "TAG0001"
            )
        );
    }
}
