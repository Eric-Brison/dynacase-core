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
     * @param string $criteria filter
     * @param string $arg filter argument
     * @param string $family family name or id
     * @return void
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
     * @param string $criteria filter
     * @param string $arg filter argument
     * @param string $family family name or id
     * @param integer $count expected results count
     * @return void
     * @dataProvider countCriteria
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
     * @param string $criteria filter
     * @param string $arg filter argument
     * @param string $family family name or id
     * @param integer $count expected results count
     * @return void
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
     * test only count user search
     * @param array $data test specification
     * @return void
     * @dataProvider onlyCountWithNoViewControlCriteria
     * @---depends testCountSearch
     */
    public function testOnlyCountWithNoViewControlSearch($data)
    {
        
        if (isset($data['import'])) {
            $this->importDocument($data['import']);
        }
        
        $this->assertTrue(isset($data['tests']) , sprintf("Missing 'tests'."));
        
        foreach ($data['tests'] as $i => & $test) {
            if (isset($test['sudo'])) {
                $this->sudo($test['sudo']);
            }
            
            $fam = new_doc(self::$dbaccess, $test['search:family']);
            $this->assertTrue($fam->isAlive() , sprintf("test#%s> Family '%s' not found.", $i, $test['search:family']));
            
            $s = new \SearchDoc(self::$dbaccess, $test['search:family']);
            $s->setObjectReturn();
            if (isset($test['search:noviewcontrol']) && $test['search:noviewcontrol']) {
                $s->noViewControl();
            }
            $count = $s->onlyCount();
            $this->assertEquals($count, $test['expect:count'], sprintf("test#%s> Result size is %s while expecting %s.", $i, $count, $test['expect:count']));
            
            if (isset($test['sudo'])) {
                $this->exitSudo();
            }
        }
        unset($test);
    }
    /**
     * test search filters
     * @param array $data test specification
     * @return void
     * @dataProvider onlyCountFilterCriteria
     * @---depends testCountSearch
     */
    public function testOnlyCountFilterSearch($data)
    {
        if (isset($data['import'])) {
            $this->importDocument($data['import']);
        }
        
        $this->assertTrue(isset($data['tests']) , sprintf("Missing 'tests'."));
        
        foreach ($data['tests'] as $i => & $test) {
            if (isset($test['sudo'])) {
                $this->sudo($test['sudo']);
            }
            
            $fam = new_doc(self::$dbaccess, $test['search:family']);
            $this->assertTrue($fam->isAlive() , sprintf("test#%s> Family '%s' not found.", $i, $test['search:family']));
            
            $s = new \SearchDoc(self::$dbaccess, $test['search:family']);
            $s->setObjectReturn();
            if (isset($test['search:noviewcontrol']) && $data['search:noviewcontrol']) {
                $s->noViewControl();
            }
            if (isset($test['search:filters']) && is_array($test['search:filters'])) {
                foreach ($test['search:filters'] as $filter) {
                    $s->addFilter($filter);
                }
            }
            $count = $s->onlyCount();
            $this->assertEquals($count, $test['expect:count'], sprintf("test#%s> Result size is %s while expecting %s.", $i, $count, $test['expect:count']));
            
            if (isset($test['sudo'])) {
                $this->exitSudo();
            }
        }
        unset($test);
    }
    /**
     * test basic search criteria
     * @param string $criteria filter
     * @param string $arg filter argument
     * @param string $family family name or id
     * @param integer $count expected results count
     * @return void
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
     * @param string $criteria filter
     * @param string $arg filter argument
     * @param string $family family name or id
     * @param integer $count expected results count
     * @return void
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
     * @param string $criteria filter
     * @param string $arg filter argument
     * @param string $family family name or id
     * @return void
     * @dataProvider errorCriteria
     */
    public function testErrorSearch($criteria, $arg, $family, $expectErrors = array())
    {
        try {
            $s = new \SearchDoc(self::$dbaccess, $family);
            $s->addFilter($criteria, $arg);
            $s->setObjectReturn(true);
            $s->search();
            
            $err = $s->getError();
        }
        catch(\Exception $e) {
            $err = $e->getMessage();
        }
        $this->assertFalse($err == "", sprintf("Need detect Search error %s %s", $criteria, $arg));
        foreach ($expectErrors as $error) {
            $this->assertContains($error, $err, sprintf("no good error code"));
        }
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
                "IUSER2",
                array(
                    'IUSER2'
                )
            ) ,
            // syntax error
            array(
                "us_mail y '%s'",
                "Léopol",
                "IUSER",
                array(
                    'DB0005'
                )
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
    public function onlyCountWithNoViewControlCriteria()
    {
        return array(
            array(
                array(
                    "import" => "PU_data_dcp_search_noviewcontrol.ods",
                    "tests" => array(
                        array(
                            "search:family" => "TST_SEARCH_NOVIEWCONTROL",
                            "expect:count" => 5
                        ) ,
                        array(
                            "sudo" => "anonymous",
                            "search:family" => "TST_SEARCH_NOVIEWCONTROL",
                            "expect:count" => 3
                        ) ,
                        array(
                            "import" => "PU_data_dcp_search.ods",
                            "sudo" => "anonymous",
                            "search:family" => "TST_SEARCH_NOVIEWCONTROL",
                            "search:noviewcontrol" => true,
                            "expect:count" => 5
                        )
                    )
                )
            )
        );
    }
    public function onlyCountFilterCriteria()
    {
        return array(
            array(
                array(
                    "import" => "PU_data_dcp_search_filters.ods",
                    "tests" => array(
                        array(
                            "search:family" => "TST_SEARCH_FILTERS",
                            "search:filters" => array(
                                "a_text ~* '^TST_SEARCH_FILTERS_'",
                                "extract(year from a_timestamp) = '1970'"
                            ) ,
                            "expect:count" => 2
                        ) ,
                        array(
                            "search:family" => "TST_SEARCH_FILTERS",
                            "search:filters" => array(
                                "extract(year from a_timestamp) = '1970'",
                                "a_text IN (select * from (values ('TST_SEARCH_FILTERS_01')) as foo(name))"
                            ) ,
                            "expect:count" => 1
                        )
                    )
                )
            )
        );
    }
}
?>