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

class TestNewDoc extends TestCaseDcpCommonFamily
{
    
    private static $ids = array();
    
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        $d = createDoc(self::$dbaccess, \Dcp\Family\Base::familyName);
        $d->setValue(\Dcp\AttributeIdentifiers\Base::ba_title, "x1-" . $d->revision);
        $d->store();
        $d->setLogicalName("TST_X1");
        self::$ids[$d->name][$d->revision] = array(
            "id" => $d->id,
            "title" => $d->getTitle()
        );
        
        $d = createDoc(self::$dbaccess, \Dcp\Family\Base::familyName);
        $d->setValue(\Dcp\AttributeIdentifiers\Base::ba_title, "x2-" . $d->revision);
        $d->store();
        $d->setLogicalName("TST_X2");
        self::$ids[$d->name][$d->revision] = array(
            "id" => $d->id,
            "title" => $d->getTitle()
        );
        $d->revise();
        $d->setValue(\Dcp\AttributeIdentifiers\Base::ba_title, "x2-" . $d->revision);
        $d->store();
        self::$ids[$d->name][$d->revision] = array(
            "id" => $d->id,
            "title" => $d->getTitle()
        );
        
        $d = createDoc(self::$dbaccess, \Dcp\Family\Base::familyName);
        $d->setValue(\Dcp\AttributeIdentifiers\Base::ba_title, "x3-" . $d->revision);
        $d->store();
        $d->setLogicalName("TST_X3");
        self::$ids[$d->name][$d->revision] = array(
            "id" => $d->id,
            "title" => $d->getTitle()
        );
        $d->revise();
        $d->setValue(\Dcp\AttributeIdentifiers\Base::ba_title, "x3-" . $d->revision);
        $d->store();
        self::$ids[$d->name][$d->revision] = array(
            "id" => $d->id,
            "title" => $d->getTitle()
        );
        $d->revise();
        $d->setValue(\Dcp\AttributeIdentifiers\Base::ba_title, "x3-" . $d->revision);
        self::$ids[$d->name][$d->revision] = array(
            "id" => $d->id,
            "title" => $d->getTitle()
        );
        $d->store();
        
        $d = createDoc(self::$dbaccess, \Dcp\Family\Base::familyName);
        $d->setValue(\Dcp\AttributeIdentifiers\Base::ba_title, "x4-" . $d->revision);
        $d->store();
        $d->setLogicalName("TST_X4");
        self::$ids[$d->name][$d->revision] = array(
            "id" => $d->id,
            "title" => $d->getTitle()
        );
        $d->revise();
        $d->setValue(\Dcp\AttributeIdentifiers\Base::ba_title, "x4-" . $d->revision);
        $d->store();
        self::$ids[$d->name][$d->revision] = array(
            "id" => $d->id,
            "title" => $d->getTitle()
        );
        $d->revise();
        $d->setValue(\Dcp\AttributeIdentifiers\Base::ba_title, "x4-" . $d->revision);
        self::$ids[$d->name][$d->revision] = array(
            "id" => $d->id,
            "title" => $d->getTitle()
        );
        $d->store();
        $d->locked = - 1;
        $d->modify(); // close document
        $dM = createDoc(self::$dbaccess, \Dcp\Family\Dir::familyName);
        // need to change its family BASE to DIR
        $dM->initid = $d->initid;
        $dM->name = $d->name;
        $dM->revision = $d->revision + 1;
        $dM->setValue(\Dcp\AttributeIdentifiers\Dir::ba_title, "x4M-" . $dM->revision);
        $dM->store();
        self::$ids[$dM->name][$dM->revision] = array(
            "id" => $dM->id,
            "title" => $d->getTitle()
        );
        $dM->revise();
        $dM->setValue(\Dcp\AttributeIdentifiers\Dir::ba_title, "x4M-" . $dM->revision);
        $dM->store();
        self::$ids[$dM->name][$dM->revision] = array(
            "id" => $dM->id,
            "title" => $dM->getTitle()
        );
    }
    /**
     * @dataProvider dataSimpleNewDoc
     */
    public function testSimpleNewDoc($docName, $expectedTitle)
    {
        $d = new_doc(self::$dbaccess, $docName);
        $this->assertTrue($d->isAffected() , "document $docName not found");
        $this->assertTrue($d->isAlive() , "document $docName not last revision\n" . print_r(self::$ids[$docName], true));
        $this->assertEquals($expectedTitle, $d->getTitle() , "wrong title for $docName\n" . print_r(self::$ids[$docName], true));
        // print_r("test".print_r(self::$ids[$docName], true) );
        
    }
    /**
     * @dataProvider dataReviseNewDoc
     */
    public function testReviseNewDoc($docName, $revision, $expectedTitle)
    {
        $id = self::$ids[$docName][$revision]["id"];
        $d = new_doc(self::$dbaccess, $id);
        $this->assertTrue($d->isAffected() , "document $docName not found");
        $this->assertEquals($d->id, $id, "document $docName (rev $revision) not match id");
        $this->assertEquals($expectedTitle, $d->getTitle() , "wrong title for $docName");
    }
    /**
     * @dataProvider dataLatestNewDoc
     */
    public function testLatestNewDoc($docName, $revision, $expectedTitle)
    {
        $id = self::$ids[$docName][$revision]["id"];
        $d = new_doc(self::$dbaccess, $id, true);
        $this->assertTrue($d->isAffected() , "document $docName not found");
        $this->assertTrue($d->isAlive() , "document $docName #$id not last revision" . print_r(self::$ids[$docName], true));
        $this->assertEquals($expectedTitle, $d->getTitle() , "wrong title for $docName");
    }
    
    public function dataLatestNewDoc()
    {
        return array(
            array(
                "TST_X1",
                0,
                "x1-0"
            ) ,
            array(
                "TST_X2",
                0,
                "x2-1"
            ) ,
            array(
                "TST_X2",
                1,
                "x2-1"
            ) ,
            array(
                "TST_X3",
                0,
                "x3-2"
            ) ,
            array(
                "TST_X3",
                1,
                "x3-2"
            ) ,
            array(
                "TST_X3",
                2,
                "x3-2"
            ) ,
            array(
                "TST_X4",
                0,
                "x4M-4"
            ) ,
            array(
                "TST_X4",
                1,
                "x4M-4"
            ),
            array(
                "TST_X4",
                2,
                "x4M-4"
            ),
            array(
                "TST_X4",
                3,
                "x4M-4"
            ),
            array(
                "TST_X4",
                4,
                "x4M-4"
            )
        );
    }
    
    public function dataReviseNewDoc()
    {
        return array(
            array(
                "TST_X1",
                0,
                "x1-0"
            ) ,
            array(
                "TST_X2",
                0,
                "x2-0"
            ) ,
            array(
                "TST_X2",
                1,
                "x2-1"
            ) ,
            array(
                "TST_X3",
                2,
                "x3-2"
            ) ,
            array(
                "TST_X4",
                2,
                "x4-2"
            ) ,
            array(
                "TST_X4",
                3,
                "x4M-3"
            )
        );
    }
    
    public function dataSimpleNewDoc()
    {
        return array(
            array(
                "TST_X1",
                "x1-0"
            ) ,
            array(
                "TST_X2",
                "x2-1"
            ) ,
            array(
                "TST_X3",
                "x3-2"
            ) ,
            array(
                "TST_X4",
                "x4M-4"
            )
        );
    }
}
