<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package DCP
*/

namespace PU;

require_once 'PU_testcase_dcp_document.php';

class TestSearch extends TestCaseDcpDocument
{
    /*protected function setUp()    {
        parent::setUp();
    }*/
    /**
     * test basic search criteria
     * @dataProvider loginCriteria
     */
    public function testBasicSearch($criteria, $arg, $family)
    {
        require_once "FDL/Class.SearchDoc.php";
        $s = new \SearchDoc(self::$dbaccess, $family);
        $s->addFilter($criteria, $arg);
        $s->setObjectReturn(true);
        $s->search();
        
        $err = $s->getError();
        $this->assertEmpty($err, sprintf("Search error %s %s", $criteria, $arg));
    }
    
    protected function createDataSearch()
    {
        
        $societies = array(
            "Poire",
            "Pomme",
            "Cerise",
            "Pomme-Banane",
            "Banane"
        );
        foreach ($societies as $socTitle) {
            $d1 = createDoc(self::$dbaccess, "SOCIETY", false);
            $d1->setTitle($socTitle);
            $err = $d1->add();
            if ($err != "") return false;
        }
        return true;
    }
    /**
     * test basic search criteria
     * @dataProvider countCriteria
     *
     */
    public function testCountSearch($criteria, $arg, $family, $count)
    {
        require_once "FDL/Class.SearchDoc.php";
        $this->createDataSearch();
        $s = new \SearchDoc(self::$dbaccess, $family);
        if ($criteria) $s->addFilter($criteria, $arg);
        $s->setObjectReturn(true);
        $s->search();
        
        $err = $s->getError();
        $this->assertEmpty($err, sprintf("Search error %s %s", $criteria, $arg));
        
        $this->assertEquals($count, $s->count() , sprintf("Count must be %d (found %d) error %s %s", $count, $s->count() , $criteria, $arg));
    }
    /**
     * test basic search criteria
     * @dataProvider countCriteria
     * @---depends testCountSearch
     */
    public function testOnlyCountSearch($criteria, $arg, $family, $count)
    {
        require_once "FDL/Class.SearchDoc.php";
        $this->createDataSearch();
        $s = new \SearchDoc(self::$dbaccess, $family);
        if ($criteria) $s->addFilter($criteria, $arg);
        $s->setObjectReturn(true);
        $c = $s->onlyCount();
        
        $err = $s->getError();
        $this->assertEmpty($err, sprintf("Search error %s %s", $criteria, $arg));
        
        $this->assertEquals($count, $s->count() , sprintf("Count must be %d (found %d) error %s %s", $count, $s->count() , $criteria, $arg));
        $this->assertEquals($count, $c, sprintf("Return count must be %d (found %d) error %s %s", $count, $s->count() , $criteria, $arg));
    }
    /**
     * test basic search criteria
     * @dataProvider countCriteria
     * @---depends testCountSearch
     */
    public function testSliceSearch($criteria, $arg, $family, $count)
    {
        require_once "FDL/Class.SearchDoc.php";
        $this->createDataSearch();
        $s = new \SearchDoc(self::$dbaccess, $family);
        if ($criteria) $s->addFilter($criteria, $arg);
        $s->setObjectReturn(true);
        $s->setSlice(2);
        $s->search();
        $c = $s->count();
        $err = $s->getError();
        $this->assertEmpty($err, sprintf("Search error %s %s", $criteria, $arg));
        $this->assertLessThanOrEqual(2, $c);
    }
    /**
     * test basic search criteria
     * @dataProvider countCriteria
     * @---depends testCountSearch
     */
    public function testStartSearch($criteria, $arg, $family, $count)
    {
        require_once "FDL/Class.SearchDoc.php";
        $this->createDataSearch();
        
        $s = new \SearchDoc(self::$dbaccess, $family);
        if ($criteria) $s->addFilter($criteria, $arg);
        $s->setObjectReturn(true);
        $c = $s->onlyCount();
        $call = $s->count();
        
        $s = new \SearchDoc(self::$dbaccess, $family);
        if ($criteria) $s->addFilter($criteria, $arg);
        $s->setObjectReturn(true);
        $s->setStart(2);
        $s->search();
        $cstart = $s->count();
        $err = $s->getError();
        $this->assertEmpty($err, sprintf("Search error %s %s", $criteria, $arg));
        $this->assertLessThanOrEqual($call, $cstart);
    }
    /**
     * test basic search criteria
     * @dataProvider errorCriteria
     */
    public function testErrorSearch($criteria, $arg, $family)
    {
        require_once "FDL/Class.SearchDoc.php";
        $s = new \SearchDoc(self::$dbaccess, $family);
        $s->addFilter($criteria, $arg);
        $s->setObjectReturn(true);
        $s->search();
        
        $err = $s->getError();
        $this->assertFalse($err == "", sprintf("Need detect Search error %s %s", $criteria, $arg));
    }
    public function loginCriteria()
    {
        return array(
            array(
                "us_login ~* '%s'",
                "Garfield",
                "IUSER"
            ) ,
            array(
                "us_mail='%s'",
                "Léopol",
                "IUSER"
            )
        );
    }
    public function errorCriteria()
    {
        return array(
            // family error
            array(
                "us_login ~* '%s'",
                "Garfield",
                "IUSER2"
            ) ,
            // syntax error
            array(
                "us_mail y '%s'",
                "Léopol",
                "IUSER"
            ) ,
            // injection error
            array(
                "us_mail ~ '%s');update users set id=0 where id = -6;--",
                "Léopol",
                "IUSER"
            )
        );
    }
    public function countCriteria()
    {
        return array(
            array(
                "title ~* '%s'",
                "Pomme",
                "SOCIETY",
                2
            ) ,
            array(
                "title ~ '%s'",
                "Poire|Pomme|Cerise|Banane",
                "SOCIETY",
                5
            ) ,
            array(
                "title = '%s'",
                "Pomme",
                "SOCIETY",
                1
            )
        );
    }
}
?>