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
        $this->assertTrue($df->isAlive() , "document $docName is not alive");
        $err = $df->addUTag(\Doc::getSystemUserId() , $tag, $value);
        $this->assertEmpty($err, sprintf("utag error"));
        
        $utag = $df->getUTag($tag);
        
        $this->assertTrue($utag !== false, sprintf("utag %s not retrieved", $tag));
        $this->assertEquals($value, $utag->comment);
        
        $this->exitSudo();
        // test it is only for login not aoth people
        $this->sudo($otherLogin);
        
        $this->resetDocumentCache();
        $df = new_doc(self::$dbaccess, $docName);
        $this->assertTrue($df->isAlive() , "document $docName is not alive");
        $utag = $df->getUTag($tag);
        
        $this->assertTrue($utag === false, sprintf("utag %s of other user is retrieved", $tag));
        
        $this->exitSudo();
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
        $this->assertTrue($df->isAlive() , "document $docName is not alive");
        $err = $df->addUTag(\Doc::getSystemUserId() , $tag, "__first__");
        $this->assertEmpty($err, sprintf("utag error"));
        
        $err = $df->addUTag(\Doc::getSystemUserId() , $tag, $value);
        $this->assertEmpty($err, sprintf("utag error"));
        
        $utag = $df->getUTag($tag);
        
        $this->assertTrue($utag !== false, sprintf("utag %s not retrieved", $tag));
        $this->assertEquals($value, $utag->comment);
        
        $this->exitSudo();
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
        $this->assertTrue($df->isAlive() , "document $docName is not alive");
        $err = $df->addUTag(\Doc::getSystemUserId() , $tag, $value);
        $this->assertEmpty($err, sprintf("utag error"));
        
        $utag = $df->getUTag($tag);
        
        $this->assertTrue($utag !== false, sprintf("utag %s not retrieved", $tag));
        $this->assertEquals($value, $utag->comment);
        
        $err = $df->delUTag(\Doc::getSystemUserId() , $tag);
        $this->assertEmpty($err, sprintf("utag del error"));
        $utag = $df->getUTag($tag);
        
        $this->assertTrue($utag === false, sprintf("utag %s not deleted", $tag));
        
        $this->exitSudo();
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
        $this->assertTrue($df->isAlive() , "document $docName is not alive");
        $err = $df->addUTag(\Doc::getSystemUserId() , $tag, $value);
        $this->assertEmpty($err, sprintf("utag error"));
        
        $utag = $df->getUTag($tag);
        
        $this->assertTrue($utag !== false, sprintf("utag %s not retrieved", $tag));
        $this->assertEquals($value, $utag->comment);
        
        $err = $df->delUTags(\Doc::getSystemUserId());
        $this->assertEmpty($err, sprintf("utag del error"));
        $utag = $df->getUTag($tag);
        
        $this->assertTrue($utag === false, sprintf("utag %s not deleted", $tag));
        
        $this->exitSudo();
    }
    /**
     * @dataProvider dataATags
     */
    public function testAddATag($docName, array $tags)
    {
        $this->resetDocumentCache();
        
        $df = new_doc(self::$dbaccess, $docName);
        $this->assertTrue($df->isAlive() , "document $docName is not alive");
        foreach ($tags as $tag) {
            $err = $df->addATag($tag);
            $this->assertEmpty($err, sprintf("utag error"));
        }
        
        foreach ($tags as $tag) {
            $atag = $df->getATag($tag);
            
            $this->assertTrue($atag, sprintf("atag %s not retrieved : \n found [%s]", $tag, $df->atags));
        }
        // test it is only for login not aoth people
        $this->resetDocumentCache();
        $df = new_doc(self::$dbaccess, $docName);
        $this->assertTrue($df->isAlive() , "document $docName is not alive");
        foreach ($tags as $tag) {
            $atag = $df->getATag($tag);
            $this->assertTrue($atag, sprintf("atag %s from new is not retrieved", $tag));
        }
    }
    /**
     * @dataProvider dataDeleteATag
     */
    public function testDeleteATag($docName, array $addTags, array $delTags, array $expectedTags)
    {
        $this->resetDocumentCache();
        
        $df = new_doc(self::$dbaccess, $docName);
        $this->assertTrue($df->isAlive() , "document $docName is not alive");
        foreach ($addTags as $tag) {
            $err = $df->addATag($tag);
            $this->assertEmpty($err, sprintf("utag error"));
        }
        
        foreach ($addTags as $tag) {
            $atag = $df->getATag($tag);
            $this->assertTrue($atag, sprintf("atag %s not retrieved : \n found [%s]", $tag, $df->atags));
        }
        
        foreach ($delTags as $tag) {
            $err = $df->delATag($tag);
            $this->assertEmpty($err, sprintf("utag delete error : $err"));
        }
        // test it is only for login not aoth people
        $this->resetDocumentCache();
        $df = new_doc(self::$dbaccess, $docName);
        $this->assertTrue($df->isAlive() , "document $docName is not alive");
        
        $nbTags = (!$df->atags) ? 0 : count(explode("\n", $df->atags));
        $this->assertEquals(count($expectedTags) , $nbTags, sprintf(" found [%s]", $df->atags));
        
        foreach ($expectedTags as $tag) {
            $atag = $df->getATag($tag);
            $this->assertTrue($atag, sprintf("atag %s from new is not retrieved", $tag));
        }
    }
    /**
     * @dataProvider dataAddAErrorTag
     */
    public function testAddAErrorTag($docName, $tag, $error)
    {
        $this->resetDocumentCache();
        
        $df = new_doc(self::$dbaccess, $docName);
        $this->assertTrue($df->isAlive() , "document $docName is not alive");
        
        $err = $df->addATag($tag);
        $this->assertContains($error, $err, sprintf("atag error"));
    }
    /**
     * @dataProvider dataImportATag
     */
    public function testImportATag($docName, array $expectedTags)
    {
        $df = new_doc(self::$dbaccess, $docName);
        $this->assertTrue($df->isAlive() , "document $docName is not alive");
        
        foreach ($expectedTags as $tag) {
            $atag = $df->getATag($tag);
            $this->assertTrue($atag, sprintf("atag %s not retrieved : \n found [%s]", $tag, $df->atags));
        }
        $tags = [];
        if ($df->atags) {
            $tags = explode("\n", $df->atags);
        }
        $this->assertEquals(count($tags) , count($expectedTags) , sprintf("wrong count for [%s]", $df->atags));
    }
    
    public function dataImportATag()
    {
        return array(
            array(
                "TST_BASETAG1",
                ["Red",
                "Blue"]
            ) ,
            array(
                "TST_BASETAG2",
                ["Yellow"]
            ) ,
            array(
                "TST_BASETAG3",
                ["Green"]
            ) ,
            array(
                "TST_BASETAG4",
                []
            )
        );
    }
    
    public function dataAddAErrorTag()
    {
        return array(
            array(
                "TST_BASETAG",
                "MY_TAG\nMY",
                "DOC0121"
            ) ,
            array(
                "TST_BASETAG",
                "",
                "DOC0122"
            )
        );
    }
    
    public function dataDeleteATag()
    {
        return array(
            array(
                "TST_BASETAG",
                array(
                    "MY_TAG"
                ) ,
                array(
                    "MY_TAG"
                ) ,
                array()
            ) ,
            array(
                "TST_BASETAG",
                array(
                    "MY_TAG"
                ) ,
                array() ,
                array(
                    "MY_TAG"
                )
            ) ,
            array(
                "TST_BASETAG",
                array(
                    "MY_TAG1",
                    "MY_TAG:2",
                    'MY_TAG$3'
                ) ,
                array(
                    'MY_TAG$3'
                ) ,
                array(
                    "MY_TAG1",
                    "MY_TAG:2"
                )
            ) ,
            array(
                "TST_BASETAG",
                array(
                    "MY_TAG1",
                    'MY_TAG/2',
                    'MY_TAG^3',
                    '{a:123}'
                ) ,
                array(
                    'MY_TAG1'
                ) ,
                array(
                    'MY_TAG/2',
                    'MY_TAG^3',
                    '{a:123}'
                )
            ) ,
            array(
                "TST_BASETAG",
                array(
                    "MY_TAG1",
                    'MY_TAG/2',
                    'MY_TAG^3',
                    '{a:123}'
                ) ,
                array(
                    'MY_TAG/2',
                    'MY_TAG^3',
                    '{a:123}',
                    'MY_TAG1'
                ) ,
                array()
            ) ,
            array(
                "TST_BASETAG",
                array(
                    "MY_TAG1",
                    'MY_TAG/2',
                    'MY_TAG^3',
                    '{a:123}'
                ) ,
                array(
                    'MY_TAG/2',
                    'MY_TAG^3',
                    'MY_TAG1',
                ) ,
                array(
                    '{a:123}'
                )
            ) ,
            array(
                "TST_BASETAG",
                array(
                    "MY_TAG1",
                    'MY_TAG/2',
                    'MY_TAG^3',
                    '{a:123}'
                ) ,
                array(
                    'MY_TAG/2',
                ) ,
                array(
                    "MY_TAG1",
                    'MY_TAG^3',
                    '{a:123}'
                )
            )
        );
    }
    
    public function dataATags()
    {
        return array(
            array(
                "TST_BASETAG",
                array(
                    "MY_TAG"
                )
            ) ,
            array(
                "TST_BASETAG",
                array(
                    "MY_TAG",
                    "MY_TAGTWO"
                )
            ) ,
            array(
                "TST_BASETAG",
                array(
                    "MY_TAG",
                    "MY_TAG:a"
                )
            ) ,
            array(
                "TST_BASETAG",
                array(
                    "MY_TAG",
                    "MY_TAG:"
                )
            ) ,
            array(
                "TST_BASETAG",
                array(
                    "MY_TAG:",
                    "MY_TAG"
                )
            ) ,
            array(
                "TST_BASETAG",
                array(
                    "MY_TAG 2",
                    "MY_TAG-3"
                )
            )
        );
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
            ) ,
            array(
                "TST_BASETAG",
                "jane",
                "john",
                "two",
                "deux"
            ) ,
            array(
                "TST_BASETAG",
                "jane",
                "john",
                "empty",
                ""
            )
        );
    }
}
?>