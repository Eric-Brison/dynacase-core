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

class TestSetLogicalName extends TestCaseDcpCommonFamily
{
    /**
     * import TST_FAMSETVALUE family
     * @static
     * @return string
     */
    protected static function getCommonImportFile()
    {
        return "";
    }
    /**
     * @dataProvider dataSetLogicalName
     * @param string $oldname
     * @param string $newname
     */
    public function testExecuteSetLogicalName($oldname, $newname)
    {
        $doc = createDoc(self::$dbaccess, "BASE");
        $err = $doc->Add();
        $this->assertEmpty($err, sprintf("Error when creating document %s", $err));
        $this->assertTrue($doc->isAlive() , sprintf("document %s not alive", $doc->id));
        
        $err = $doc->setLogicalName($oldname);
        $this->assertEmpty($err, sprintf("Error when setting logical name %s for document %s : %s", $oldname, $doc->id, $err));
        clearCacheDoc();
        
        $new_doc = new_Doc(self::$dbaccess, $oldname);
        $this->assertTrue($new_doc->isAlive() , sprintf("document %s not alive", $oldname));
        
        $err = $doc->setLogicalName($newname, true);
        $this->assertEmpty($err, sprintf("Error when setting logical name %s for document %s : %s", $newname, $oldname, $err));
        clearCacheDoc();
        
        $new_doc = new_Doc(self::$dbaccess, $newname);
        $this->assertTrue($new_doc->isAlive() , sprintf("document %s not alive", $newname));
        $this->assertEquals($doc->id, $new_doc->id, sprintf("New logical name is not set to document"));
    }
    /**
     * @dataProvider dataBeforeAddSetLogicalName
     * @param string $logicalName
     */
    public function testBeforeAddSetLogicalName($logicalName)
    {
        $doc = createDoc(self::$dbaccess, "BASE");
        $err = $doc->setLogicalName($logicalName);
        $this->assertEmpty($err, sprintf("Error when setting logical name %s for document %s : %s", $logicalName, $doc->id, $err));
        $err = $doc->Add();
        $this->assertEmpty($err, sprintf("Error when creating document %s", $err));
        $this->assertTrue($doc->isAlive() , sprintf("document %s not alive", $doc->id));
        
        $this->assertEquals($logicalName, $doc->name, sprintf("New logical name is not set to document"));
        
        clearCacheDoc();
        
        $new_doc = new_Doc(self::$dbaccess, $logicalName);
        $this->assertTrue($new_doc->isAlive() , sprintf("document %s not alive", $logicalName));
    }
    /**
     * @dataProvider dataBeforeAddSetLogicalName
     * @param string $oldname
     */
    public function testErrorBeforeAddSetLogicalName($oldname)
    {
        $doc1 = createDoc(self::$dbaccess, "BASE");
        $err = $doc1->setLogicalName($oldname);
        $this->assertEmpty($err, sprintf("Error when setting logical name %s for document %s : %s", $oldname, $doc1->id, $err));
        $err = $doc1->Add();
        $this->assertEquals($oldname, $doc1->name, sprintf("New logical name is not set to document"));
        $this->assertEmpty($err, sprintf("Error when creating document %s", $err));
        $this->assertTrue($doc1->isAlive() , sprintf("document %s not alive", $doc1->id));
        
        $doc2 = createDoc(self::$dbaccess, "BASE");
        $err = $doc2->setLogicalName($oldname);
        $this->assertEmpty($err, sprintf("Error when setting logical name %s for document %s : %s", $oldname, $doc2->id, $err));
        $err = $doc2->Add();
        $this->assertNotEmpty($err, sprintf("Need error because duplicate name", $oldname, $doc2->id, $err));
    }
    /**
     * @dataProvider dataErrorSyntaxSetLogicalName
     * @param string $oldname
     */
    public function testErrorSyntaxSetLogicalName($oldname)
    {
        $doc1 = createDoc(self::$dbaccess, "BASE");
        $err = $doc1->setLogicalName($oldname);
        
        $this->assertNotEmpty($err, sprintf("setLogicalName: Need error invalid name", $oldname, $doc1->id, $err));
        
        $err = $doc1->Add();
        $this->assertNotEmpty($err, sprintf("Add: Need error invalid name", $oldname, $doc1->id, $err));
    }
    /**
     * @dataProvider dataRevisedSetLogicalName
     * @param string $oldname
     * @param string $newname
     */
    public function testRevisedSetLogicalName($oldname, $newname)
    {
        $doc = createDoc(self::$dbaccess, "BASE");
        $err = $doc->Add();
        
        $idRev0 = $doc->id;
        $this->assertEmpty($err, sprintf("Error when creating document %s", $err));
        $this->assertTrue($doc->isAlive() , sprintf("document %s not alive", $doc->id));
        
        $err = $doc->revise();
        $this->assertEmpty($err, sprintf("Error when revised document %s", $err));
        
        $idRev1 = $doc->id;
        $err = $doc->revise();
        $this->assertEmpty($err, sprintf("Error when revised document %s", $err));
        
        $err = $doc->setLogicalName($oldname);
        $this->assertEmpty($err, sprintf("Error when setting logical name %s for document %s : %s", $oldname, $doc->id, $err));
        clearCacheDoc();
        
        $new_doc = new_Doc(self::$dbaccess, $oldname);
        $this->assertTrue($new_doc->isAlive() , sprintf("document %s not alive", $oldname));
        
        $new_doc = new_Doc(self::$dbaccess, $idRev0);
        $this->assertEquals($oldname, $new_doc->name, sprintf("no good revised name #0"));
        $new_doc = new_Doc(self::$dbaccess, $idRev1);
        $this->assertEquals($oldname, $new_doc->name, sprintf("no good revised name #1"));
        
        $err = $doc->setLogicalName($newname, true);
        $this->assertEmpty($err, sprintf("Error when setting logical name %s for document %s : %s", $newname, $oldname, $err));
        clearCacheDoc();
        
        $new_doc = new_Doc(self::$dbaccess, $newname);
        $this->assertTrue($new_doc->isAlive() , sprintf("document %s not alive", $newname));
        $this->assertEquals($doc->id, $new_doc->id, sprintf("New logical name is not set to document"));
        
        $new_doc = new_Doc(self::$dbaccess, $idRev0);
        $this->assertEquals($newname, $new_doc->name, sprintf("no good revised new name #0"));
        $new_doc = new_Doc(self::$dbaccess, $idRev1);
        $this->assertEquals($newname, $new_doc->name, sprintf("no good revised new name #1"));
    }
    public function dataErrorSyntaxSetLogicalName()
    {
        return array(
            array(
                'TST_TEST 2'
            ) ,
            array(
                'TST:TEST'
            ) ,
            array(
                'élève'
            ) ,
            array(
                '123'
            ) ,
            array(
                '1a'
            )
        );
    }
    
    public function dataBeforeAddSetLogicalName()
    {
        return array(
            array(
                'TST_TEST-2'
            ) ,
            array(
                'T3'
            )
        );
    }
    public function dataSetLogicalName()
    {
        return array(
            array(
                'TST_TEST_ONE',
                'TST_NEW_TESTs_ONE'
            ) ,
            array(
                'tst_test-one',
                'tst_new-test-one'
            )
        );
    }
    public function dataRevisedSetLogicalName()
    {
        return array(
            array(
                'TST_TEST_TWO',
                'TST_NEW_TESTs_TWO'
            ) ,
            array(
                'tst_test-two',
                'tst_new-test-two'
            )
        );
    }
}
