<?php
/*
 * @author Anakeen
 * @package Dcp\Pu
*/

namespace Dcp\Pu;

require_once 'PU_testcase_dcp_commonfamily.php';

class TestSearch extends TestCaseDcpCommonFamily
{
    
    public static function getCommonImportFile()
    {
        return array(
            'PU_data_dcp_TestSearchGetOriginalQuery.ods'
        );
    }
    
    protected $famName = "TST_GETORIGINALQUERY1";
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
        $s = new \SearchDoc(self::$dbaccess, $family);
        $s->addFilter($criteria, $arg);
        $s->setObjectReturn(true);
        $s->search();
        
        $err = $s->getError();
        $this->assertEmpty($err, sprintf("Search error %s %s", $criteria, $arg));
    }
    
    protected function createDataSearch()
    {
        
        $fruits = array(
            array(
                "Pruneaux",
                "Poire"
            ) ,
            array(
                "Poire",
                "Pomme"
            ) ,
            array(
                "Mures",
                "Cerise"
            ) ,
            array(
                "Nèfle",
                "Kiwi",
                "Coing",
                "Pomme-Banane"
            ) ,
            array(
                "Banane"
            ) ,
            array(
                "Melon",
                "Raisin"
            ) ,
            array(
                "Corme",
                "__delete__"
            ) ,
            array(
                "Corme",
                "Orange",
                "Prune",
                "__delete__"
            ) ,
        );
        $d1 = null;
        foreach ($fruits as $socTitle) {
            foreach ($socTitle as $k => $title) {
                
                if ($k === 0) {
                    $d1 = createDoc(self::$dbaccess, $this->famName, false);
                    $d1->setTitle($title);
                    $err = $d1->add();
                    $this->assertEmpty($err, "Cannot create data");
                } else {
                    
                    if ($title === "__delete__") {
                        
                        $err = $d1->delete();
                        $this->assertEmpty($err, "Cannot delete data");
                    } else {
                        $d1->revise("Yo");
                        $d1->setTitle($title);
                        $err = $d1->modify();
                        $this->assertEmpty($err, "Cannot update data");
                    }
                }
            }
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
     * @param bool $latest
     * @param bool $distinct
     * @param string $trash
     * @param string $criteria filter
     * @param string $arg filter argument
     * @param string $family family name or id
     * @param array $expectTitles
     * @dataProvider countAllRevisionCriteria
     */
    public function testCountRevisionSearch($latest, $distinct, $trash, $criteria, $arg, $family, array $expectTitles)
    {
        $this->createDataSearch();
        $s = new \SearchDoc(self::$dbaccess, $family);
        if ($criteria) $s->addFilter($criteria, $arg);
        $s->setObjectReturn(true);
        $s->latest = $latest;
        $s->distinct = $distinct;
        $s->trash = $trash;
        $s->search();
        $err = $s->getError();
        $this->assertEmpty($err, sprintf("Search error %s %s", $criteria, $arg));
        
        $returnTitles = $this->getReturnTitles($s);
        sort($returnTitles);
        sort($expectTitles);
        $this->assertEquals(count($expectTitles) , $s->count() , sprintf("Count error %s %s \nFound: %s %s", $criteria, $arg, implode(",", $returnTitles) , print_r($s->getSearchInfo() , true)));
        $this->assertEquals($expectTitles, $returnTitles, sprintf("Not expected result %s %s \nFound : %s %s", $criteria, $arg, implode(", ", $returnTitles) , print_r($s->getSearchInfo() , true)));
    }
    
    private function getReturnTitles(\SearchDoc $s)
    {
        $dl = $s->getDocumentList();
        $titles = array();
        foreach ($dl as $doc) {
            
            $titles[] = $doc->getTitle();
        }
        
        return $titles;
    }
    /**
     * test basic search criteria in array mode
     * @param string $criteria filter
     * @param string $arg filter argument
     * @param string $family family name or id
     * @param integer $count expected results count
     * @return void
     * @dataProvider countCriteria
     */
    public function testArrayCountSearch($criteria, $arg, $family, $count)
    {
        $this->createDataSearch();
        $s = new \SearchDoc(self::$dbaccess, $family);
        if ($criteria) $s->addFilter($criteria, $arg);
        $s->setObjectReturn(false);
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
     * @depends testCountSearch
     */
    public function testOnlyCountSearch($criteria, $arg, $family, $count)
    {
        $this->createDataSearch();
        $s = new \SearchDoc(self::$dbaccess, $family);
        if ($criteria) $s->addFilter($criteria, $arg);
        $s->setObjectReturn(true);
        $c = - 2;
        try {
            $c = $s->onlyCount();
        }
        catch(\Dcp\Db\Exception $e) {
        }
        
        $err = $s->getError();
        $this->assertEmpty($err, sprintf("Search error %s %s", $criteria, $arg));
        
        $this->assertEquals($count, $s->count() , sprintf("Count must be %d (found %d) error %s %s", $count, $s->count() , $criteria, $arg));
        $this->assertEquals($count, $c, sprintf("Return count must be %d (found %d) error %s %s", $count, $s->count() , $criteria, $arg));
    }
    /**
     * test basic search criteria
     * @param string $criteria filter
     * @param string $arg filter argument
     * @param string $family family name or id
     * @param string $error expected error
     * @return void
     * @dataProvider countErrorCriteria
     * @depends testCountSearch
     */
    public function testOnlyCountErrorSearch($criteria, $arg, $family, $error)
    {
        $this->createDataSearch();
        $s = new \SearchDoc(self::$dbaccess, $family);
        if ($criteria) $s->addFilter($criteria, $arg);
        $s->setObjectReturn(true);
        $c = null;
        try {
            $c = $s->onlyCount();
        }
        catch(\Dcp\Db\Exception $e) {
        }
        $err = $s->getError();
        $this->assertContains($error, $err, sprintf("No good error %s", print_r($s->getSearchInfo() , true)));
        $count = - 1;
        $this->assertEquals($count, $s->count() , sprintf("Count must be %d (found %d) error %s %s", $count, $s->count() , $criteria, $arg));
        $this->assertEquals($count, $c, sprintf("Return count must be %d (found %d) error %s %s", $count, $s->count() , $criteria, $arg));
    }
    /**
     * test basic search criteria
     * @param string $criteria filter
     * @param string $arg filter argument
     * @param string $family family name or id
     * @param string $error expected error
     * @return void
     * @dataProvider countErrorCriteriaException
     * @depends testCountSearch
     */
    public function testOnlyCountErrorSearchException($criteria, $arg, $family, $error)
    {
        $this->createDataSearch();
        $s = new \SearchDoc(self::$dbaccess, $family);
        if ($criteria) $s->addFilter($criteria, $arg);
        $s->setObjectReturn(true);
        $exceptionError = '';
        try {
            $s->onlyCount();
        }
        catch(\Dcp\Db\Exception $e) {
            $exceptionError = $e->getMessage();
        }
        $getError = $s->getError();
        $this->assertContains($error, $exceptionError, sprintf("Exception error '%s' does not contains '%s' (%s)", $exceptionError, $getError, print_r($s->getSearchInfo() , true)));
        $this->assertContains($error, $getError, sprintf("getError() '%s' does not contains '%s' (%s)", $getError, $getError, print_r($s->getSearchInfo() , true)));
        $count = - 1;
        $this->assertEquals($count, $s->count() , sprintf("count() must be %d (found %d) error %s %s", $count, $s->count() , $criteria, $arg));
    }
    /**
     * test only count user search
     * @param array $data test specification
     * @return void
     * @dataProvider onlyCountWithNoViewControlCriteria
     * @depends testCountSearch
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
            $count = - 2;
            if (isset($test['search:noviewcontrol']) && $test['search:noviewcontrol']) {
                $s->overrideViewControl();
            }
            try {
                $count = $s->onlyCount();
            }
            catch(\Dcp\Db\Exception $e) {
            }
            $this->assertEquals($count, $test['expect:count'], sprintf("test#%s> Result size is %s while expecting %s. [%s]", $i, $count, $test['expect:count'], print_r($s->getSearchInfo() , true)));
            
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
     * @depends testCountSearch
     */
    public function testOnlyCountFilterSearch($data)
    {
        $this->requiresCoreParamEquals('CORE_LANG', 'fr_FR');
        
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
                $s->overrideViewControl();
            }
            if (isset($test['search:filters']) && is_array($test['search:filters'])) {
                foreach ($test['search:filters'] as $filter) {
                    $s->addFilter($filter);
                }
            }
            $count = $s->onlyCount();
            $this->assertEquals($count, $test['expect:count'], sprintf("test#%s> Result size is %s while expecting %s. [%s]", $i, $count, $test['expect:count'], print_r($s->getSearchInfo() , true)));
            
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
     * @dataProvider countCriteria
     * @---depends testCountSearch
     */
    public function testSliceSearch($criteria, $arg, $family)
    {
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
     * @param $data
     * @dataProvider errorCriteria
     */
    public function testErrorSearch($data)
    {
        try {
            $s = new \SearchDoc(self::$dbaccess, $data['family']);
            $s->addFilter($data['criteria'], $data['arg']);
            if (isset($data['collection'])) {
                $s->useCollection($data['collection']);
            }
            $s->setObjectReturn(true);
            $s->search();
            
            $err = $s->getError();
        }
        catch(\Exception $e) {
            $err = $e->getMessage();
        }
        $this->assertFalse($err == "", sprintf("Need detect Search error %s %s", $data['criteria'], $data['arg']));
        foreach ($data['expectErrors'] as $error) {
            $this->assertContains($error, $err, sprintf("no good error code"));
        }
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
    public function testCountAndResetSearch($criteria, $arg, $family, $count)
    {
        $this->createDataSearch();
        $s = new \SearchDoc(self::$dbaccess, $family);
        $s->onlyCount();
        
        if ($criteria) $s->addFilter($criteria, $arg);
        
        $t = $s->search();
        
        $err = $s->getError();
        $this->assertEmpty($err, sprintf("Search error %s %s", $criteria, $arg));
        
        $this->assertEquals($count, $s->count() , sprintf("Count must be %d (found %d) error %s %s", $count, $s->count() , $criteria, $arg));
        $this->assertEquals($count, count($t) , sprintf("Count must be %d (found %d) search mode error %s %s", $count, count($t) , $criteria, $arg));
    }
    /**
     * @param $test
     * @return void
     * @dataProvider dataGetOriginalQuery
     */
    public function testGetOriginalQuery($test)
    {
        $fam = empty($test['search:family']) ? '' : $test['search:family'];
        $s = new \SearchDoc(self::$dbaccess, $fam);
        if (isset($test['search:filter'])) {
            if (!is_array($test['search:filter'])) {
                $test['search:filter'] = array(
                    $test['search:filter']
                );
            }
            foreach ($test['search:filter'] as $filter) {
                $s->addFilter($filter);
            }
        }
        if (isset($test['search:collection'])) {
            $s->useCollection($test['search:collection']);
        }
        $s->setObjectReturn();
        
        $sql = $s->getOriginalQuery();
        $this->assertNotEmpty($sql, sprintf("Unexpected empty original query!"));
        
        $s->search();
        $err = $s->getError();
        $this->assertEmpty($err, sprintf("Unexpected SearchDoc error [%s]: %s", $sql, $err));
        
        $expected = array();
        while ($doc = $s->getNextDoc()) {
            $expected[] = $doc->name;
        }
        
        $err = simpleQuery(self::$dbaccess, $sql, $res);
        $this->assertEmpty($err, sprintf("Unexpected error in simpleQuery() [%s]: %s", $sql, $err));
        foreach ($res as & $row) {
            $row = $row['name'];
        }
        
        $missing = array_diff($expected, $res);
        $spurious = array_diff($res, $expected);
        $this->assertEmpty($missing, sprintf("Unexpected missing elements [%s]: {%s}", $sql, join(', ', $missing)));
        $this->assertEmpty($spurious, sprintf("Unexpected spurious elements [%s]: {%s}", $sql, join(', ', $spurious)));
    }
    /**
     * @param $famName
     * @dataProvider dataSearchGetValue
     */
    public function testSearchGetValue($famName, $docName, $expectedAttr)
    {
        $s = new \SearchDoc("", $famName);
        $s->setObjectReturn(true);
        $dl = $s->search()->getDocumentList();
        
        foreach ($dl as $doc) {
            if ($doc->name === $docName) {
                foreach ($expectedAttr as $attrid => $value) {
                    $this->assertEquals($value, $doc->getRawValue($attrid) , sprintf("attribute \"%s\"", $attrid));
                }
            }
        }
    }
    
    public function dataSearchGetValue()
    {
        return array(
            array(
                $this->famName,
                "TST_GETORIGINALQUERY2_3",
                array(
                    "s_title" => "0",
                    "s_text" => ""
                )
            ) ,
            array(
                $this->famName,
                "TST_GETORIGINALQUERY2_2",
                array(
                    "s_title" => "1£1",
                    "s_text" => "0"
                )
            ) ,
            array(
                "TST_GETORIGINALQUERY2",
                "TST_GETORIGINALQUERY2_2",
                array(
                    "s_title" => "1£1",
                    "s_text" => "0"
                )
            ) ,
            array(
                "",
                "TST_GETORIGINALQUERY2_2",
                array(
                    "s_title" => "1£1",
                    "s_text" => "0"
                )
            ) ,
            array(
                "",
                "TST_GETORIGINALQUERY2_1",
                array(
                    "s_title" => "2",
                    "s_text" => "Two£Four"
                )
            )
        );
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
                array(
                    'criteria' => "us_login ~* '%s'",
                    'arg' => "Garfield",
                    'family' => "IUSER2",
                    'expectErrors' => array(
                        'IUSER2'
                    )
                )
            ) ,
            // syntax error
            array(
                array(
                    'criteria' => "us_mail y '%s'",
                    'arg' => "Léopol",
                    'family' => "IUSER",
                    'expectErrors' => array(
                        'DB0005'
                    )
                )
            ) ,
            // injection error
            array(
                array(
                    'criteria' => "us_mail ~ '%s');update users set id=0 where id = -6;--",
                    'arg' => "Léopol",
                    'family' => "IUSER",
                    'expectErrors' => array()
                )
            ) ,
            // useCollection() and addFilter() conflict
            array(
                array(
                    'criteria' => "us_login ~* '%s'",
                    'arg' => 'Garfield',
                    'family' => 'IUSER',
                    'collection' => 'TST_GETORIGINALQUERY_SEARCH_SSEARCH_1',
                    'expectErrors' => array(
                        'SD0008'
                    )
                )
            )
        );
    }
    public function countCriteria()
    {
        return array(
            array(
                "title ~* '%s'",
                "Pomme",
                $this->famName,
                2
            ) ,
            array(
                "title is not null",
                "",
                $this->famName,
                6 + 3 + 3 // 3 from ods file
                
            ) ,
            array(
                "title ~ '%s'",
                "Poire|Pomme|Cerise|Banane",
                $this->famName,
                5
            ) ,
            array(
                "title = '%s'",
                "Pomme",
                $this->famName,
                1
            )
        );
    }
    
    public function countAllRevisionCriteria()
    {
        return array(
            array(
                "latest" => false,
                "distinct" => false,
                "trash" => "no",
                "title ~* '%s'",
                "Pomme",
                $this->famName,
                array(
                    "Pomme",
                    "Pomme-Banane"
                )
            ) ,
            array(
                "latest" => false,
                "distinct" => false,
                "trash" => "no",
                "title is not null",
                "",
                $this->famName,
                array(
                    "Pruneaux",
                    "Poire",
                    "Poire",
                    "Pomme",
                    "Mures",
                    "Cerise",
                    "Nèfle",
                    "Kiwi",
                    "Coing",
                    "Pomme-Banane",
                    "Banane",
                    "Melon",
                    "Raisin",
                    "Un",
                    "Deux",
                    "Trois",
                    "0",
                    "1£1",
                    "2"
                )
            ) ,
            array(
                "latest" => false,
                "distinct" => false,
                "trash" => "no",
                "title ~ '%s'",
                "Poire|Pomme|Cerise|Banane|Prune|Corme",
                $this->famName,
                array(
                    "Pruneaux",
                    "Poire",
                    "Poire",
                    "Pomme",
                    "Cerise",
                    "Pomme-Banane",
                    "Banane"
                )
            ) ,
            array(
                "latest" => false,
                "distinct" => false,
                "trash" => "no",
                "title = '%s'",
                "Poire",
                $this->famName,
                array(
                    "Poire",
                    "Poire"
                )
            ) ,
            array(
                "latest" => false,
                "distinct" => true,
                "trash" => "no",
                "title ~ '%s'",
                "Poire|Pomme|Cerise|Banane|Prune|Corme",
                $this->famName,
                array(
                    "Poire",
                    "Pomme",
                    "Cerise",
                    "Pomme-Banane",
                    "Banane"
                )
            ) ,
            array(
                "latest" => true,
                "distinct" => true,
                "trash" => "only",
                "title ~ '%s'",
                "Poire|Pomme|Cerise|Banane|Prune|Corme",
                $this->famName,
                array(
                    "Corme",
                    "Prune"
                )
            ) ,
            array(
                "latest" => false,
                "distinct" => false,
                "trash" => "only",
                "title ~ '%s'",
                "Poire|Pomme|Cerise|Orange|Prune|Corme",
                $this->famName,
                array(
                    "Corme",
                    "Corme",
                    "Prune",
                    "Orange"
                )
            ) ,
            array(
                "latest" => false,
                "distinct" => false,
                "trash" => "also",
                "title ~ '%s'",
                "Poire|Pomme|Cerise|Orange|Prune|Corme",
                $this->famName,
                array(
                    "Pruneaux",
                    "Poire",
                    "Poire",
                    "Pomme",
                    "Cerise",
                    "Pomme-Banane",
                    "Corme",
                    "Corme",
                    "Prune",
                    "Orange"
                )
            ) ,
            array(
                "latest" => false,
                "distinct" => true,
                "trash" => "also",
                "title ~ '%s'",
                "Pomme|Cerise|Orange|Prune|Corme|Kiwi",
                $this->famName,
                array(
                    "Pruneaux",
                    "Pomme",
                    "Cerise",
                    "Pomme-Banane",
                    "Corme",
                    "Prune"
                )
            ) ,
            array(
                "latest" => true,
                "distinct" => true,
                "trash" => "also",
                "title ~ '%s'",
                "Pomme|Cerise|Orange|Prune|Corme|Kiwi",
                $this->famName,
                array(
                    "Pruneaux",
                    "Pomme",
                    "Cerise",
                    "Pomme-Banane",
                    "Corme",
                    "Prune"
                )
            )
        );
    }
    
    public function countErrorCriteria()
    {
        return array(
            array(
                "title ~ '%s'",
                "Poire|Pomme|Cerise|Banane",
                "BASE_UNKNOW",
                "BASE_UNKNOW"
            )
        );
    }
    public function countErrorCriteriaException()
    {
        return array(
            array(
                "title_unknow ~* '%s'",
                "Pomme",
                $this->famName,
                "title_unknow"
            ) ,
            array(
                "title @? '%s'",
                "Pomme",
                $this->famName,
                "@?"
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
                            "expect:count" => 6
                        ) ,
                        array(
                            "sudo" => "anonymous",
                            "search:family" => "TST_SEARCH_NOVIEWCONTROL",
                            "expect:count" => 4
                        ) ,
                        array(
                            "import" => "PU_data_dcp_search.ods",
                            "sudo" => "anonymous",
                            "search:family" => "TST_SEARCH_NOVIEWCONTROL",
                            "search:noviewcontrol" => true,
                            "expect:count" => 6
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
    public function dataGetOriginalQuery()
    {
        return array(
            array(
                array(
                    'search:family' => '',
                    'search:filter' => "s_title ~ '^T'",
                    'search:collection' => 'TST_GETORIGINALQUERY_SEARCH_3'
                )
            ) ,
            array(
                array(
                    'search:family' => '',
                    'search:filter' => "s_title ~ '^T'",
                    'search:collection' => 'TST_GETORIGINALQUERY_SEARCH_4'
                )
            ) ,
            array(
                array(
                    'search:family' => '',
                    'search:filter' => "s_title ~ '^T'",
                    'search:collection' => 'TST_GETORIGINALQUERY_SEARCH_5'
                )
            )
        );
    }
}
