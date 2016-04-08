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

class TestNewDoc extends TestCaseDcpCommonFamily
{
    
    private static $ids = array();
    
    public static function getCommonImportFile()
    {
        setLanguage("fr_FR");
        return "PU_data_dcp_newdoc.ods";
    }
    
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        $d = createDoc(self::$dbaccess, \Dcp\Family\Base::familyName);
        $d->setAttributeValue(\Dcp\AttributeIdentifiers\Base::ba_title, "x1-" . $d->revision);
        $d->store();
        
        $d->setLogicalName("TST_X1");
        self::$ids[$d->name][$d->revision] = array(
            "id" => $d->id,
            "title" => $d->getTitle()
        );
        
        $d = createDoc(self::$dbaccess, \Dcp\Family\Base::familyName);
        $d->setAttributeValue(\Dcp\AttributeIdentifiers\Base::ba_title, "x2-" . $d->revision);
        $d->store();
        $d->setLogicalName("TST_X2");
        self::$ids[$d->name][$d->revision] = array(
            "id" => $d->id,
            "title" => $d->getTitle()
        );
        $d->revise();
        
        $d->setAttributeValue(\Dcp\AttributeIdentifiers\Base::ba_title, "x2-" . $d->revision);
        $d->store();
        self::$ids[$d->name][$d->revision] = array(
            "id" => $d->id,
            "title" => $d->getTitle()
        );
        
        $d = createDoc(self::$dbaccess, \Dcp\Family\Base::familyName);
        $d->setAttributeValue(\Dcp\AttributeIdentifiers\Base::ba_title, "x3-" . $d->revision);
        $d->store();
        $d->setLogicalName("TST_X3");
        self::$ids[$d->name][$d->revision] = array(
            "id" => $d->id,
            "title" => $d->getTitle()
        );
        
        $d->revise();
        $d->setAttributeValue(\Dcp\AttributeIdentifiers\Base::ba_title, "x3-" . $d->revision);
        $d->store();
        self::$ids[$d->name][$d->revision] = array(
            "id" => $d->id,
            "title" => $d->getTitle()
        );
        $d->revise();
        $d->setAttributeValue(\Dcp\AttributeIdentifiers\Base::ba_title, "x3-" . $d->revision);
        self::$ids[$d->name][$d->revision] = array(
            "id" => $d->id,
            "title" => $d->getTitle()
        );
        $d->store();
        
        $d = createDoc(self::$dbaccess, \Dcp\Family\Base::familyName);
        $d->setAttributeValue(\Dcp\AttributeIdentifiers\Base::ba_title, "x4-" . $d->revision);
        $d->store();
        $d->setLogicalName("TST_X4");
        self::$ids[$d->name][$d->revision] = array(
            "id" => $d->id,
            "title" => $d->getTitle()
        );
        $d->revise();
        $d->setAttributeValue(\Dcp\AttributeIdentifiers\Base::ba_title, "x4-" . $d->revision);
        $d->store();
        self::$ids[$d->name][$d->revision] = array(
            "id" => $d->id,
            "title" => $d->getTitle()
        );
        $d->revise();
        $d->setAttributeValue(\Dcp\AttributeIdentifiers\Base::ba_title, "x4-" . $d->revision);
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
        $dM->setAttributeValue(\Dcp\AttributeIdentifiers\Dir::ba_title, "x4M-" . $dM->revision);
        $dM->setAttributeValue(\Dcp\AttributeIdentifiers\Dir::fld_allbut, "1");
        $dM->store();
        self::$ids[$dM->name][$dM->revision] = array(
            "id" => $dM->id,
            "title" => $d->getTitle()
        );
        $dM->revise();
        $dM->setAttributeValue(\Dcp\AttributeIdentifiers\Dir::ba_title, "x4M-" . $dM->revision);
        $dM->setAttributeValue(\Dcp\AttributeIdentifiers\Dir::fld_allbut, "2");
        $dM->store();
        self::$ids[$dM->name][$dM->revision] = array(
            "id" => $dM->id,
            "title" => $dM->getTitle()
        );
        // Need to reset cause phpunit reset dbid when clone global objects
        self::resetDocumentCache();
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
     * @dataProvider dataSharedNewDoc
     */
    public function testSharedNewDoc($docName, $expectedTitle, $someValues)
    {
        $nd = createDoc(self::$dbaccess, "TST_ND");
        $this->assertEquals("no", $nd->getrawValue("tst_shared"));
        $nd->store();
        $this->assertEquals("yes", $nd->getrawValue("tst_shared"));
        
        $d1 = new_doc(self::$dbaccess, $docName);
        $this->assertEquals("yes", $d1->getrawValue("tst_shared"));
        $this->assertTrue($d1->isAffected() , "document $docName not found");
        $this->assertTrue($d1->isAlive() , "document $docName not last revision");
        $this->assertEquals($expectedTitle, $d1->getTitle() , "wrong title for $docName");
        
        $d1->setAttributeValue(\Dcp\AttributeIdentifiers\Tst_nd::tst_title, $someValues[0]);
        $d2 = new_doc(self::$dbaccess, $docName);
        $this->assertEquals($someValues[0], $d2->getRawValue(\Dcp\AttributeIdentifiers\Tst_nd::tst_title));
        $d2->setAttributeValue(\Dcp\AttributeIdentifiers\Tst_nd::tst_title, $someValues[1]);
        $this->assertEquals($someValues[1], $d2->getRawValue(\Dcp\AttributeIdentifiers\Tst_nd::tst_title));
        
        $this->assertEquals("yes", $d2->getrawValue("tst_shared"));
        $d2->revise();
        
        $this->assertEquals("yes", $d2->getrawValue("tst_shared"));
        $d2->setAttributeValue(\Dcp\AttributeIdentifiers\Tst_nd::tst_title, $someValues[2]);
        $d3 = new_doc(self::$dbaccess, $docName);
        $this->assertEquals($someValues[2], $d3->getRawValue(\Dcp\AttributeIdentifiers\Tst_nd::tst_title));
        
        $d4 = new_doc(self::$dbaccess, $d3->initid, true);
        $this->assertEquals($someValues[2], $d4->getRawValue(\Dcp\AttributeIdentifiers\Tst_nd::tst_title));
        
        $d5 = new_doc(self::$dbaccess, $d3->initid);
        $this->assertEquals($someValues[1], $d5->getRawValue(\Dcp\AttributeIdentifiers\Tst_nd::tst_title));
        $d5->setAttributeValue(\Dcp\AttributeIdentifiers\Tst_nd::tst_title, $someValues[3]);
        
        $d6 = new_doc(self::$dbaccess, $d3->initid);
        
        $this->assertEquals($someValues[3], $d6->getRawValue(\Dcp\AttributeIdentifiers\Tst_nd::tst_title));
        $this->assertEquals($someValues[3], $d5->getRawValue(\Dcp\AttributeIdentifiers\Tst_nd::tst_title));
        $this->assertEquals($someValues[2], $d4->getRawValue(\Dcp\AttributeIdentifiers\Tst_nd::tst_title));
        $this->assertEquals($someValues[2], $d3->getRawValue(\Dcp\AttributeIdentifiers\Tst_nd::tst_title));
        $this->assertEquals($someValues[2], $d2->getRawValue(\Dcp\AttributeIdentifiers\Tst_nd::tst_title));
        $this->assertEquals($someValues[2], $d1->getRawValue(\Dcp\AttributeIdentifiers\Tst_nd::tst_title));
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
    /**
     * @dataProvider dataSimplegetTDoc
     */
    public function testSimpleGetTDoc($docName, $expectedValues)
    {
        $d = getTDoc(self::$dbaccess, $docName);
        foreach ($expectedValues as $attrid => $expectedValue) {
            $this->assertTrue(isset($d[strtolower($attrid) ]) , sprintf("attribut %s not found", $attrid));
            $this->assertEquals($expectedValue, $d[strtolower($attrid) ], sprintf("wrong value %s : %s", $attrid, print_r($d, true)));
        }
        $d = getLatestTDoc(self::$dbaccess, $d["initid"]);
        foreach ($expectedValues as $attrid => $expectedValue) {
            $this->assertTrue(isset($d[strtolower($attrid) ]) , sprintf("attribut for latest %s not found", $attrid));
            $this->assertEquals($expectedValue, $d[strtolower($attrid) ], sprintf("wrong value for latest %s : %s", $attrid, print_r($d, true)));
        }
    }
    /**
     * @dataProvider dataGetLatestRevisionNumber
     */
    public function testGetLatestRevisionNumber($docName, $expectedValues)
    {
        $d = getTDoc(self::$dbaccess, $docName);
        $this->assertEquals($expectedValues, getLatestRevisionNumber(self::$dbaccess, $d["initid"]) , "not good last revision number");
    }
    /**
     * @dataProvider dataGetRevTDoc
     */
    public function testGetRevTDoc($docName, $revision, $expectedValues)
    {
        $d = getTDoc(self::$dbaccess, $docName);
        
        $d = getRevTDoc(self::$dbaccess, $d["initid"], $revision);
        foreach ($expectedValues as $attrid => $expectedValue) {
            $this->assertTrue(isset($d[strtolower($attrid) ]) , sprintf("attribut for latest %s not found", $attrid));
            $this->assertEquals($expectedValue, $d[strtolower($attrid) ], sprintf("wrong value for latest %s : %s", $attrid, print_r($d, true)));
        }
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
            ) ,
            array(
                "TST_X4",
                2,
                "x4M-4"
            ) ,
            array(
                "TST_X4",
                3,
                "x4M-4"
            ) ,
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
    
    public function dataGetLatestRevisionNumber()
    {
        return array(
            array(
                "TST_X1",
                0
            ) ,
            array(
                "TST_X2",
                1
            ) ,
            array(
                "TST_X3",
                2
            ) ,
            array(
                "TST_X4",
                4
            )
        );
    }
    public function dataSimplegetTDoc()
    {
        return array(
            array(
                "TST_X1",
                array(
                    "title" => "x1-0"
                )
            ) ,
            array(
                "TST_X2",
                array(
                    "title" => "x2-1"
                )
            ) ,
            array(
                "TST_X3",
                array(
                    "title" => "x3-2"
                )
            ) ,
            array(
                "TST_X4",
                array(
                    "title" => "x4M-4",
                    "fld_allbut" => "2"
                )
            )
        );
    }
    public function dataGetRevTDoc()
    {
        return array(
            array(
                "TST_X1",
                0,
                array(
                    "title" => "x1-0"
                )
            ) ,
            array(
                "TST_X2",
                1,
                array(
                    "title" => "x2-1"
                )
            ) ,
            array(
                "TST_X3",
                2,
                array(
                    "title" => "x3-2"
                )
            ) ,
            array(
                "TST_X4",
                0,
                array(
                    "title" => "x4-0",
                    "ba_title" => "x4-0"
                )
            ) ,
            array(
                "TST_X4",
                2,
                array(
                    "title" => "x4-2",
                    "ba_title" => "x4-2"
                )
            ) ,
            array(
                "TST_X4",
                3,
                array(
                    "title" => "x4M-3",
                    "ba_title" => "x4M-3",
                    "fld_allbut" => "1"
                )
            ) ,
            array(
                "TST_X4",
                4,
                array(
                    "title" => "x4M-4",
                    "ba_title" => "x4M-4",
                    "fld_allbut" => "2"
                )
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
    public function dataSharedNewDoc()
    {
        return array(
            array(
                "TST_ND1",
                "Cornichon",
                array(
                    "Hello",
                    "World",
                    "new World",
                    "antique World"
                )
            )
        );
    }
}
