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
    /**
     * @param string $tagToAdd
     * @return \Doc|\DocFam
     * @dataProvider dataTags
     */
    public function testAddTag($tagToAdd)
    {
        /**
         * @var \DocFam $doc
         */
        $doc = createDoc("", "TST_FAMILYTAG1");
        $this->assertTrue(is_object($doc));
        $err = $doc->store();
        $this->assertEmpty($err, "cannot create good doc: " . $err);
        $err = $doc->tag()->addTag($tagToAdd);
        $this->assertEmpty($err, sprintf("An error occured when trying to add tag %s to document TST_FAMILYTAG1: %s", $tagToAdd, $err));
        $tags = $doc->tag()->getTag();
        $this->assertContains($tagToAdd, $doc->tag()->getTagsValue($tags) , sprintf("Can't find tag %s in document tags [%s]", $tagToAdd, implode(", ", $doc->tag()->getTagsValue($tags))));
        return $doc;
    }
    /**
     * @param string $tagToDelete
     * @dataProvider dataTags
     */
    public function testDeleteTag($tagToDelete)
    {
        $doc = $this->testAddTag($tagToDelete);
        if ($doc->isAlive()) {
            $err = $doc->tag()->delTag($tagToDelete);
            $this->assertEmpty($err, sprintf("An error occured when trying to delete tag %s to document TST_FAMILYTAG1: %s", $tagToDelete, $err));
        }
    }
    /**
     * @param array $tags
     * @dataProvider dataMultipleTags
     */
    public function testGetAllTags(array $tags)
    {
        /**
         * @var \DocFam $doc
         */
        $doc = createDoc("", "TST_FAMILYTAG1");
        $this->assertTrue(is_object($doc));
        $err = $doc->store();
        $this->assertEmpty($err, "cannot create good doc: " . $err);
        foreach ($tags as $tagToAdd) {
            $err = $doc->tag()->addTag($tagToAdd);
            $this->assertEmpty($err, sprintf("An error occured when trying to add tag %s to document TST_FAMILYTAG1: %s", $tagToAdd, $err));
        }
        $findTags = $doc->tag()->getTagsValue($doc->tag()->getAllTags());
        foreach ($tags as $tag) {
            $this->assertContains($tag, $findTags, sprintf("Can't find tag %s in document tags [%s]", $tag, implode(", ", $findTags)));
        }
    }
    /**
     * @param $oldName
     * @param $newName
     * @dataProvider dataRenameTags
     */
    public function testRenameTag($oldName, $newName)
    {
        $doc = $this->testAddTag($oldName);
        if ($doc->isAlive()) {
            $err = $doc->tag()->renameTag($oldName, $newName);
            $this->assertEmpty($err, sprintf("An error occured when trying to rename tag %s to document TST_FAMILYTAG1 in %s: %s", $oldName, $newName, $err));
            $this->assertNotContains($oldName, $doc->tag()->getTagsValue($doc->tag()->getTag()) , sprintf("Found tag %s in document tags [%s]. It should be renamed", $oldName, implode(", ", $doc->tag()->getTagsValue($doc->tag()->getTag()))));
            $this->assertContains($newName, $doc->tag()->getTagsValue($doc->tag()->getTag()) , sprintf("Can't find tag %s in document tags [%s]", $newName, implode(", ", $doc->tag()->getTagsValue($doc->tag()->getTag()))));
        }
    }
    
    public function dataRenameTags()
    {
        return array(
            array(
                "one",
                "two"
            ) ,
            array(
                "été",
                "un truc plus long"
            )
        );
    }
    
    public function dataMultipleTags()
    {
        return array(
            array(
                array(
                    "one",
                    "two",
                    "été",
                    "et un tag plus long"
                )
            )
        );
    }
    
    public function dataTags()
    {
        return array(
            array(
                "one"
            ) ,
            array(
                "two"
            ) ,
            array(
                "été"
            ) ,
            array(
                "et un tag plus long"
            )
        );
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
                ""
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
