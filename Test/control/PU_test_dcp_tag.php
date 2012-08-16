<?php

namespace Dcp\Pu;
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package Dcp\Pu
 */

require_once 'PU_testcase_dcp_document.php';

class TestTag extends TestCaseDcpDocument
{
    protected static $outputDir;
    
    
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
        
        self::importDocument("testTag.ods");
    }
    public static function tearDownAfterClass()
    {
        self::rollbackTransaction();
    
    }
    
    /**
     * @dataProvider dataTags
     */
    public function testAddUTag($docName, $login, $otherLogin, $tag, $value)
    {
        $this->resetDocumentCache();
        
        $this->sudo($login);
        $df = new_doc(self::$dbaccess, $docName);
        $this->assertTrue($df->isAlive(), "document $docName is not alive");
        $err = $df->addUTag(\Doc::getSystemUserId(), $tag, $value);
        $this->assertEmpty($err, sprintf("utag error"));
        
        $utag = $df->getUTag($tag);
        
        $this->assertTrue($utag !== false, sprintf("utag %s not retrieved", $tag));
        $this->assertEquals($value, $utag->comment);
        
        $this->exitSudo($login);
        
        // test it is only for login not aoth people
        $this->sudo($otherLogin);
        
        $this->resetDocumentCache();
        $df = new_doc(self::$dbaccess, $docName);
        $this->assertTrue($df->isAlive(), "document $docName is not alive");
        $utag = $df->getUTag($tag);
        
        $this->assertTrue($utag === false, sprintf("utag %s of other user is retrieved", $tag));
        
        $this->exitSudo($otherLogin);
    }
    /**
     * 
     * @dataProvider dataTags
     * @---depends testAddUTag
     */
    public function testChangeUTag($docName, $login, $otherLogin, $tag, $value)
    {
        $this->resetDocumentCache();
        
        $this->sudo($login);
        $df = new_doc(self::$dbaccess, $docName);
        $this->assertTrue($df->isAlive(), "document $docName is not alive");
        $err = $df->addUTag(\Doc::getSystemUserId(), $tag, "__first__");
        $this->assertEmpty($err, sprintf("utag error"));
        
        $err = $df->addUTag(\Doc::getSystemUserId(), $tag, $value);
        $this->assertEmpty($err, sprintf("utag error"));
        
        $utag = $df->getUTag($tag);
        
        $this->assertTrue($utag !== false, sprintf("utag %s not retrieved", $tag));
        $this->assertEquals($value, $utag->comment);
        
        $this->exitSudo($login);
    }
    /**
     * 
     * @dataProvider dataTags
     * @---depends testAddUTag
     */
    public function testDelUTag($docName, $login, $otherLogin, $tag, $value)
    {
        $this->resetDocumentCache();
        
        $this->sudo($login);
        $df = new_doc(self::$dbaccess, $docName);
        $this->assertTrue($df->isAlive(), "document $docName is not alive");
        $err = $df->addUTag(\Doc::getSystemUserId(), $tag, $value);
        $this->assertEmpty($err, sprintf("utag error"));
        
        $utag = $df->getUTag($tag);
        
        $this->assertTrue($utag !== false, sprintf("utag %s not retrieved", $tag));
        $this->assertEquals($value, $utag->comment);
        
        $err=$df->delUTag(\Doc::getSystemUserId(), $tag);
        $this->assertEmpty($err, sprintf("utag del error"));
        $utag = $df->getUTag($tag);
        
        $this->assertTrue($utag === false, sprintf("utag %s not deleted", $tag));
        
        $this->exitSudo($login);
    }
  /**
     * 
     * @dataProvider dataTags
     * @---depends testAddUTag
     */
    public function testDelUTags($docName, $login, $otherLogin, $tag, $value)
    {
        $this->resetDocumentCache();
        
        $this->sudo($login);
        $df = new_doc(self::$dbaccess, $docName);
        $this->assertTrue($df->isAlive(), "document $docName is not alive");
        $err = $df->addUTag(\Doc::getSystemUserId(), $tag, $value);
        $this->assertEmpty($err, sprintf("utag error"));
        
        $utag = $df->getUTag($tag);
        
        $this->assertTrue($utag !== false, sprintf("utag %s not retrieved", $tag));
        $this->assertEquals($value, $utag->comment);
        
        $err=$df->delUTags(\Doc::getSystemUserId());
        $this->assertEmpty($err, sprintf("utag del error"));
        $utag = $df->getUTag($tag);
        
        $this->assertTrue($utag === false, sprintf("utag %s not deleted", $tag));
        
        $this->exitSudo($login);
    }
   /**
     * @dataProvider dataTags
     */
    public function testAddATag($docName, $login, $otherLogin, $tag, $none)
    {
        $this->resetDocumentCache();
        
        $this->sudo($login);
        $df = new_doc(self::$dbaccess, $docName);
        $this->assertTrue($df->isAlive(), "document $docName is not alive");
        $err = $df->addATag($tag);
        $this->assertEmpty($err, sprintf("utag error"));
        
        $atag = $df->getATag($tag);
        
        $this->assertTrue($atag , sprintf("atag %s not retrieved", $tag)); 
        $this->exitSudo($login);
        
        // test it is only for login not aoth people
        $this->sudo($otherLogin);
        
        $this->resetDocumentCache();
        $df = new_doc(self::$dbaccess, $docName);
        $this->assertTrue($df->isAlive(), "document $docName is not alive");
        $atag = $df->getATag($tag);
        
        $this->assertTrue($atag, sprintf("atag %s of other user is not retrieved", $tag));
        
        $this->exitSudo($otherLogin);
    }
    public function dataTags()
    {
        return array(
            array(
                "TST_BASETAG",
                "john",
                "jane",
                "one",
                "un"
            ),
            array(
                "TST_BASETAG",
                "jane",
                "john",
                "two",
                "deux"
            ),
            array(
                "TST_BASETAG",
                "jane",
                "john",
                "empty",
                ""
            )
        )
        ;
    }

}
?>