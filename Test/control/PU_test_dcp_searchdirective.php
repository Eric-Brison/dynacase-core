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

require_once 'PU_testcase_dcp_commonfamily.php';
/**
 * test some SearchDoc option like generalFilter
 */
class TestSearchDirective extends TestCaseDcpCommonFamily
{
    /**
     * import TST_FULLSERACHFAM1 family and some documents
     * @static
     * @return string
     */
    protected static function getCommonImportFile()
    {
        return "PU_data_dcp_fullsearchfamily1.ods";
    }
    
    protected $famName = 'TST_FULLSEARCHFAM1';
    /**
     * @dataProvider dataGeneralFilter
     */
    public function testGeneralFilter($filter, array $expectedDocName)
    {
        $s = new \SearchDoc(self::$dbaccess, $this->famName);
        if ($filter) $s->addGeneralFilter($filter);
        $s->setObjectReturn(true);
        $s->search();
        
        $err = $s->getError();
        $this->assertEmpty($err, "search error : $err");
        $dl = $s->getDocumentList();
        // print_r($s->getSearchInfo());
        if (count($expectedDocName) != $s->count()) {
            $this->assertEquals(count($expectedDocName) , $s->count() , "not correct count " . $this->getFilterResult($dl));
        }
        $index = 0;
        /**
         * @var \Doc $doc
         */
        foreach ($dl as $doc) {
            $this->assertEquals($expectedDocName[$index], $doc->name);
            $index++;
        }
    }
    /**
     * @dataProvider dataGeneralFilter
     * @depends testGeneralFilter
     */
    public function testArrayGeneralFilter($filter, array $expectedDocName)
    {
        $s = new \SearchDoc(self::$dbaccess, $this->famName);
        if ($filter) $s->addGeneralFilter($filter);
        $s->setObjectReturn(false);
        $s->search();
        
        $err = $s->getError();
        $this->assertEmpty($err, "search error : $err");
        $dl = $s->getDocumentList();
        // print_r($s->getSearchInfo());
        if (count($expectedDocName) != $s->count()) {
            $this->assertEquals(count($expectedDocName) , $s->count() , "not correct count " . $this->getFilterResult($dl));
        }
        $index = 0;
        /**
         * @var \Doc $doc
         */
        foreach ($dl as $doc) {
            $this->assertEquals($expectedDocName[$index], $doc["name"]);
            $index++;
        }
    }
    /**
     * @dataProvider dataGeneralSortFilter
     */
    public function testGeneralSortFilter($filter, $order, array $expectedDocName)
    {
        $s = new \SearchDoc(self::$dbaccess, $this->famName);
        if ($filter) $s->addGeneralFilter($filter);
        $s->setObjectReturn();
        $s->setPertinenceOrder($order);
        $s->search();
        
        $err = $s->getError();
        $this->assertEmpty($err, "search error : $err");
        $dl = $s->getDocumentList();
        
        if (count($expectedDocName) > $s->count()) {
            $this->assertLessThanOrEqual(count($expectedDocName) , $s->count() , "not correct count " . $this->getFilterResult($dl));
        }
        //   print_r($s->getSearchInfo());print $this->getFilterResult($dl);print_r($expectedDocName);
        //$this->assertEquals(count($expectedDocName) , $s->count() , "not correct count " . $this->getFilterResult($dl));
        $index = 0;
        /**
         * @var \Doc $doc
         */
        foreach ($dl as $doc) {
            if ($expectedDocName[$index]) {
                $this->assertEquals($expectedDocName[$index], $doc->name);
            }
            $index++;
        }
    }
    /**
     * @dataProvider dataSpellGeneralFilter
     */
    public function testSpellGeneralFilter($filter, array $expectedDocName)
    {
        $s = new \SearchDoc(self::$dbaccess, $this->famName);
        if ($filter) $s->addGeneralFilter($filter, "en");
        $s->setObjectReturn();
        $s->search();
        
        $err = $s->getError();
        $this->assertEmpty($err, "search error : $err");
        
        $dl = $s->getDocumentList();
        if (count($expectedDocName) != $s->count()) {
            $this->assertEquals(count($expectedDocName) , $s->count() , "not correct count " . $this->getFilterResult($dl));
        }
        //print_r($s->getSearchInfo());
        $this->assertEquals(count($expectedDocName) , $s->count() , "not correct count " . $this->getFilterResult($dl));
        $index = 0;
        /**
         * @var \Doc $doc
         */
        foreach ($dl as $doc) {
            $this->assertEquals($expectedDocName[$index], $doc->name);
            $index++;
        }
    }
    /**
     * @param $filter
     * @dataProvider dataErrorGeneralFilter
     */
    public function testErrorGeneralFilter($filter)
    {
        $s = new \SearchDoc(self::$dbaccess, $this->famName);
        $s->addGeneralFilter($filter);
        $s->setObjectReturn();
        $s->search();
        
        $err = $s->getError();
        $this->assertNotEmpty($err, "search error must not be empty");
    }
    /**
     * test "usefor" system search
     * @param string $dirId The identifer of the collection to use
     * @param array $existsNameList List of documents name that must be returned by the search
     * @param array $notExistsNameList List of documents name that must NOT be returned by the search
     * @return void
     * @dataProvider dataUseforSystemSearchDocWithCollection
     */
    public function testUseforSystemSearchDocWithCollection($dirId, $existsNameList, $notExistsNameList)
    {
        $dir = new_Doc(self::$dbaccess, $dirId);
        $this->assertTrue($dir->isAlive() , sprintf("Could not get search with id '%s'.", $dirId));
        
        $search = new \SearchDoc(self::$dbaccess, 0);
        $search->setObjectReturn();
        $search->useCollection($dirId);
        $search->search();
        
        $res = array();
        while ($doc = $search->nextDoc()) {
            $res[] = $doc->name;
        }
        
        if (count($existsNameList) > 0) {
            foreach ($existsNameList as $name) {
                $this->assertTrue(in_array($name, $res) , sprintf("Missing document with name '%s' in search with collection '%s': returned documents name = {%s}", $name, $dir->name, join(', ', $res)));
            }
        }
        
        if (count($notExistsNameList) > 0) {
            foreach ($notExistsNameList as $name) {
                $this->assertTrue(!in_array($name, $res) , sprintf("Found unexpected document with name '%s' in search with collection '%s': returned documents name = {%s}", $name, $dir->name, join(', ', $res)));
            }
        }
    }
    
    public function dataErrorGeneralFilter()
    {
        return array(
            array(
                "test ()"
            ) ,
            array(
                "(test) or (test"
            ) ,
            array(
                "(test) or )test("
            )
        );
    }
    
    public function dataUseforSystemSearchDocWithCollection()
    {
        return array(
            array(
                "TST_USEFOR_SYSTEM_SEARCH_NO",
                array(
                    "TST_USEFOR_N_1",
                    "TST_USEFOR_N_2",
                    "TST_USEFOR_N_3"
                ) ,
                array(
                    "TST_USEFOR_S_1",
                    "TST_USEFOR_S_2",
                    "TST_USEFOR_S_3"
                )
            ) ,
            array(
                "TST_USEFOR_SYSTEM_SEARCH_YES",
                array(
                    "TST_USEFOR_S_1",
                    "TST_USEFOR_S_2",
                    "TST_USEFOR_S_3",
                    "TST_USEFOR_N_1",
                    "TST_USEFOR_N_2",
                    "TST_USEFOR_N_3"
                ) ,
                array()
            ) ,
            array(
                "TST_USEFOR_SYSTEM_SEARCH_EMPTY",
                array(
                    "TST_USEFOR_S_1",
                    "TST_USEFOR_S_2",
                    "TST_USEFOR_S_3",
                    "TST_USEFOR_N_1",
                    "TST_USEFOR_N_2",
                    "TST_USEFOR_N_3"
                ) ,
                array()
            ) ,
            array(
                "TST_USEFOR_SYSTEM_DSEARCH_NO",
                array(
                    "TST_USEFOR_N_1",
                    "TST_USEFOR_N_2",
                    "TST_USEFOR_N_3"
                ) ,
                array(
                    "TST_USEFOR_S_1",
                    "TST_USEFOR_S_2",
                    "TST_USEFOR_S_3"
                )
            ) ,
            array(
                "TST_USEFOR_SYSTEM_DSEARCH_YES",
                array(
                    "TST_USEFOR_S_1",
                    "TST_USEFOR_S_2",
                    "TST_USEFOR_S_3",
                    "TST_USEFOR_N_1",
                    "TST_USEFOR_N_2",
                    "TST_USEFOR_N_3"
                ) ,
                array()
            ) ,
            array(
                "TST_USEFOR_SYSTEM_DSEARCH_EMPTY",
                array(
                    "TST_USEFOR_S_1",
                    "TST_USEFOR_S_2",
                    "TST_USEFOR_S_3",
                    "TST_USEFOR_N_1",
                    "TST_USEFOR_N_2",
                    "TST_USEFOR_N_3"
                ) ,
                array()
            )
        );
    }
    /**
     * Test 'searchcriteria' attributes option
     *
     * @param string $fam family id/name to search on
     * @param string $keyword search for this keyword
     * @param array $existsNameList List of documents name that should be returned by the search
     * @param array $notExistsNameList Lost of documents name that should NOT be returned by the search
     * @dataProvider dataOptionSearchCriteria
     */
    public function testOptionSearchCriteria($fam, $keyword, $existsNameList, $notExistsNameList)
    {
        $search = new \SearchDoc(self::$dbaccess, $fam);
        $search->addGeneralFilter($keyword);
        $search->setObjectReturn();
        $search->search();
        
        $res = array();
        while ($doc = $search->nextDoc()) {
            $res[] = $doc->name;
        }
        
        if (count($existsNameList) > 0) {
            foreach ($existsNameList as $name) {
                $this->assertTrue(in_array($name, $res) , sprintf("Document '%s' should be returned by search for '%s' on family '%s': returned documents name = {%s}", $name, $keyword, $fam, join(', ', $res)));
            }
        }
        
        if (count($notExistsNameList) > 0) {
            foreach ($notExistsNameList as $name) {
                $this->assertTrue(!in_array($name, $res) , sprintf("Document '%s' should NOT be returned by search for '%s' on family '%s': returned documents name = {%s}", $name, $keyword, $fam, join(', ', $res)));
            }
        }
    }
    public function dataOptionSearchCriteria()
    {
        return array(
            array(
                "TST_OPT_SEARCHCRITERIA",
                "foo",
                array(
                    "TST_OPT_SEARCHCRITERIA_DEFAULT",
                    "TST_OPT_SEARCHCRITERIA_VISIBLE",
                    "TST_OPT_SEARCHCRITERIA_PROTECTED"
                ) ,
                array(
                    "TST_OPT_SEARCHCRITERIA_HIDDEN"
                )
            ) ,
            array(
                "TST_OPT_SEARCHCRITERIA",
                "secret",
                array() ,
                array(
                    "TST_OPT_SEARCHCRITERIA_DEFAULT",
                    "TST_OPT_SEARCHCRITERIA_VISIBLE",
                    "TST_OPT_SEARCHCRITERIA_HIDDEN",
                    "TST_OPT_SEARCHCRITERIA_PROTECTED"
                )
            )
        );
    }
    /**
     * Test SearchDoc->onlyCount() method
     * @param string $fam family id or name
     * @param array $properties list of ($propertyName => $propertyValue) to be set on the SearchDoc object  (e.g. array("only" => true))
     * @param array $methods list of ($methodName) to be called on the SearchDoc object (e.g. array("noViewControl") to call $search->noViewControl())
     * @param array $filters list of SQL conditions/filters to be added with the $search->addFilter() method (e.g. array("foo <> 'bar'"))
     * @param int $expectedCount expected documents count
     * @return void
     * @dataProvider dataSearchDocOnlyCount
     */
    public function testSearchDocOnlyCount($fam, $properties, $methods, $filters, $expectedCount)
    {
        $search = new \SearchDoc(self::$dbaccess, $fam);
        if (is_array($properties)) {
            foreach ($properties as $prop => $value) {
                $search->$prop = $value;
            }
        }
        if (is_array($methods)) {
            foreach ($methods as $method) {
                $search->$method();
            }
        }
        if (is_array($filters)) {
            foreach ($filters as $filter) {
                $call = array(
                    $search,
                    "addFilter"
                );
                if (is_array($filter)) {
                    $args = $filter;
                } else {
                    $args = array(
                        $filter
                    );
                }
                call_user_func_array($call, $args);
            }
        }
        $count = $search->onlyCount();
        
        $this->assertTrue(($count == $expectedCount) , sprintf("onlyCount() returned '%s' while expecting '%s' (query = [%s]).", $count, $expectedCount, $search->getOriginalQuery()));
    }
    public function dataSearchDocOnlyCount()
    {
        return array(
            array(
                "TST_ONLYCOUNT_0",
                array(
                    "only" => false
                ) ,
                array(
                    "noViewControl"
                ) ,
                array() ,
                3 + 4
            ) ,
            array(
                "TST_ONLYCOUNT_0",
                array(
                    "only" => true
                ) ,
                array(
                    "noViewControl"
                ) ,
                array() ,
                3
            ) ,
            array(
                "TST_ONLYCOUNT_0",
                array(
                    "only" => false
                ) ,
                array(
                    "noViewControl"
                ) ,
                array(
                    "title <> 'Just to add some SQL conditions in the query...'",
                    "title <> '... blah blah blah'"
                ) ,
                3 + 4
            ) ,
            array(
                "TST_ONLYCOUNT_0",
                array(
                    "only" => true
                ) ,
                array(
                    "noViewControl"
                ) ,
                array(
                    "title <> 'Just to add some SQL conditions in the query...'",
                    "title <> '... blah blah blah'"
                ) ,
                3
            ) ,
            array(
                "TST_ONLYCOUNT_0",
                array(
                    "only" => false
                ) ,
                array(
                    "noViewControl"
                ) ,
                array(
                    "a_title <> 'Just to add some SQL conditions in the query...'",
                    "a_title <> '... blah blah blah'"
                ) ,
                3 + 4
            ) ,
            array(
                "TST_ONLYCOUNT_0",
                array(
                    "only" => true
                ) ,
                array(
                    "noViewControl"
                ) ,
                array(
                    "a_title <> 'Just to add some SQL conditions in the query...'",
                    "a_title <> '... blah blah blah'"
                ) ,
                3
            )
        );
    }
    /**
     * Test setOrder by label on enum attributes
     *
     * @dataProvider dataSearchDocSetOrder
     */
    function testSearchDocSetOrder($fam, $orderby, $orderbyLabel, $expectedCount, $expectedTitles = array())
    {
        $search = new \SearchDoc(self::$dbaccess, $fam);
        $search->setObjectReturn(true);
        $search->setOrder($orderby, $orderbyLabel);
        $search->search();
        
        $count = $search->count();
        $this->assertTrue($count == $expectedCount, sprintf("search with setOrder(%s, %s) returned '%s' elements while expecting '%s'.", var_export($orderby, true) , var_export($orderbyLabel, true), $count, $expectedCount));
        
        $titles = array();
        while ($doc = $search->nextDoc()) {
            $titles[] = $doc->title;
        }
        
        $s1 = join(', ', $titles);
        $s2 = join(', ', $expectedTitles);
        $this->assertTrue($s1 == $s2, sprintf("Expected titles not found: titles = [%s] / expected titles = [%s] / sql = [%s]", $s1, $s2, $search->getOriginalQuery()));
    }
    function dataSearchDocSetOrder()
    {
        return array(
            array(
                'TST_ORDERBY_LABEL',
                'a_enum',
                'a_enum',
                3,
                array(
                    'AAA',
                    'BBB',
                    'CCC'
                )
            ) ,
            array(
                'TST_ORDERBY_LABEL',
                'a_enum asc',
                'a_enum',
                3,
                array(
                    'AAA',
                    'BBB',
                    'CCC'
                )
            ) ,
            array(
                'TST_ORDERBY_LABEL',
                '-a_enum',
                'a_enum',
                3,
                array(
                    'CCC',
                    'BBB',
                    'AAA'
                )
            ) ,
            array(
                'TST_ORDERBY_LABEL',
                'a_enum desc',
                'a_enum',
                3,
                array(
                    'CCC',
                    'BBB',
                    'AAA'
                )
            ) ,
            array(
                'TST_ORDERBY_LABEL',
                'a_docid_0',
                'a_docid_0',
                3,
                array(
                    'CCC',
                    'BBB',
                    'AAA'
                )
            ) ,
            array(
                'TST_ORDERBY_LABEL',
                '-a_docid_0',
                'a_docid_0',
                3,
                array(
                    'AAA',
                    'BBB',
                    'CCC'
                )
            ) ,
            array(
                'TST_ORDERBY_LABEL',
                'a_docid_1',
                'a_docid_1',
                3,
                array(
                    'AAA',
                    'BBB',
                    'CCC'
                )
            ) ,
            array(
                'TST_ORDERBY_LABEL',
                '-a_docid_1',
                'a_docid_1',
                3,
                array(
                    'CCC',
                    'BBB',
                    'AAA'
                )
            ) ,
            array(
                'TST_ORDERBY_LABEL',
                'a_docid_2',
                'a_docid_2',
                3,
                array(
                    'AAA',
                    'BBB',
                    'CCC'
                )
            ) ,
            array(
                'TST_ORDERBY_LABEL',
                '-a_docid_2',
                'a_docid_2',
                3,
                array(
                    'CCC',
                    'BBB',
                    'AAA'
                )
            )
        );
    }
    /**
     * Test setOrder by label on enum attributes
     *
     * @dataProvider dataSearchDocSetOrderWithCollection
     */
    function testSearchDocSetOrderWithCollection($collectionId, $orderby, $orderbyLabel, $expectedCount, $expectedTitles = array())
    {
        $search = new \SearchDoc(self::$dbaccess);
        $search->useCollection($collectionId);
        $search->setObjectReturn(true);
        $search->setOrder($orderby, $orderbyLabel);
        $search->search();

        $count = $search->count();
        $this->assertTrue($count == $expectedCount, sprintf("search with setOrder(%s, %s) returned '%s' elements while expecting '%s'.", var_export($orderby, true) , var_export($orderbyLabel, true), $count, $expectedCount));

        $titles = array();
        while ($doc = $search->nextDoc()) {
            $titles[] = $doc->title;
        }

        $s1 = join(', ', $titles);
        $s2 = join(', ', $expectedTitles);
        $this->assertTrue($s1 == $s2, sprintf("Expected titles not found: titles = [%s] / expected titles = [%s] / sql = [%s]", $s1, $s2, $search->getOriginalQuery()));
    }
    function dataSearchDocSetOrderWithCollection()
    {
        return array(
            array(
                'TST_ORDERBY_LABEL_COLLECTION_1',
                'a_enum',
                'a_enum',
                3,
                array(
                    'AAA',
                    'BBB',
                    'CCC'
                )
            ) ,
            array(
                'TST_ORDERBY_LABEL_COLLECTION_1',
                'a_enum asc',
                'a_enum',
                3,
                array(
                    'AAA',
                    'BBB',
                    'CCC'
                )
            ) ,
            array(
                'TST_ORDERBY_LABEL_COLLECTION_1',
                '-a_enum',
                'a_enum',
                3,
                array(
                    'CCC',
                    'BBB',
                    'AAA'
                )
            ) ,
            array(
                'TST_ORDERBY_LABEL_COLLECTION_1',
                'a_enum desc',
                'a_enum',
                3,
                array(
                    'CCC',
                    'BBB',
                    'AAA'
                )
            ) ,
            array(
                'TST_ORDERBY_LABEL_COLLECTION_1',
                'a_docid_0',
                'a_docid_0',
                3,
                array(
                    'CCC',
                    'BBB',
                    'AAA'
                )
            ) ,
            array(
                'TST_ORDERBY_LABEL_COLLECTION_1',
                '-a_docid_0',
                'a_docid_0',
                3,
                array(
                    'AAA',
                    'BBB',
                    'CCC'
                )
            ) ,
            array(
                'TST_ORDERBY_LABEL_COLLECTION_1',
                'a_docid_1',
                'a_docid_1',
                3,
                array(
                    'AAA',
                    'BBB',
                    'CCC'
                )
            ) ,
            array(
                'TST_ORDERBY_LABEL_COLLECTION_1',
                '-a_docid_1',
                'a_docid_1',
                3,
                array(
                    'CCC',
                    'BBB',
                    'AAA'
                )
            ) ,
            array(
                'TST_ORDERBY_LABEL_COLLECTION_1',
                'a_docid_2',
                'a_docid_2',
                3,
                array(
                    'AAA',
                    'BBB',
                    'CCC'
                )
            ) ,
            array(
                'TST_ORDERBY_LABEL_COLLECTION_1',
                '-a_docid_2',
                'a_docid_2',
                3,
                array(
                    'CCC',
                    'BBB',
                    'AAA'
                )
            )
        );
    }
    private function getFilterResult(\DocumentList $dl)
    {
        $names = array();
        /**
         * @var \Doc $doc
         */
        foreach ($dl as $doc) {
            $names[] = $doc->name;
        }
        return implode(",", $names);
    }
    
    public function dataSpellGeneralFilter()
    {
        return array(
            array(
                "téléfone",
                array(
                    "TST_FULL2",
                    "TST_FULL1"
                )
            ) ,
            
            array(
                "téléfone maizon",
                array(
                    "TST_FULL2"
                )
            ) ,
            array(
                "téléfone méson",
                array(
                    "TST_FULL2"
                )
            ) ,
            array(
                '"fixe" mésons',
                array(
                    "TST_FULL2"
                )
            )
        );
    }
    public function dataGeneralFilter()
    {
        return array(
            array(
                "",
                array(
                    "TST_FULL3",
                    "TST_FULL4",
                    "TST_FULL5",
                    "TST_FULL6",
                    "TST_FULL7",
                    "TST_FULL2",
                    "TST_FULL9",
                    "TST_FULL1",
                    "TST_FULL8"
                )
            ) ,
            array(
                "téléphone",
                array(
                    "TST_FULL2",
                    "TST_FULL1"
                )
            ) ,
            
            array(
                "'téléphone",
                array(
                    "TST_FULL2",
                    "TST_FULL1"
                )
            ) ,
            array(
                "'téléphone'",
                array(
                    "TST_FULL2",
                    "TST_FULL1"
                )
            ) ,
            array(
                "'-;=téléphone_%'",
                array(
                    "TST_FULL2",
                    "TST_FULL1"
                )
            ) ,
            
            array(
                "téléphones portables",
                array(
                    "TST_FULL1"
                )
            ) ,
            array(
                "téléphones |&portables",
                array(
                    "TST_FULL1"
                )
            ) ,
            array(
                "téléphones AND portables",
                array(
                    "TST_FULL1"
                )
            ) ,
            array(
                "téléphones AND 'portables'",
                array(
                    "TST_FULL1"
                )
            ) ,
            array(
                "'téléphones AND 'portables",
                array(
                    "TST_FULL1"
                )
            ) ,
            array(
                "téléphones fixes",
                array(
                    "TST_FULL2"
                )
            ) ,
            array(
                "(téléphones) (fixes)",
                array(
                    "TST_FULL2"
                )
            ) ,
            array(
                "cheval",
                array(
                    "TST_FULL3",
                    "TST_FULL6"
                )
            ) ,
            array(
                "rouge",
                array(
                    "TST_FULL3",
                    "TST_FULL4",
                    "TST_FULL5",
                    "TST_FULL6",
                    "TST_FULL7",
                    "TST_FULL8"
                )
            ) ,
            array(
                "portable OR fixe",
                array(
                    "TST_FULL2",
                    "TST_FULL1"
                )
            ) ,
            array(
                "portàble OR fixe OR cheval",
                array(
                    "TST_FULL3",
                    "TST_FULL6",
                    "TST_FULL2",
                    "TST_FULL1"
                )
            ) ,
            array(
                "téléphone OR (jument    AND rouge)",
                array(
                    "TST_FULL6",
                    "TST_FULL2",
                    "TST_FULL1"
                )
            ) ,
            array(
                "téléphone OR (rouge jument)",
                array(
                    "TST_FULL6",
                    "TST_FULL2",
                    "TST_FULL1"
                )
            ) ,
            array(
                '"rouge"',
                array(
                    "TST_FULL4",
                    "TST_FULL5",
                    "TST_FULL6",
                    "TST_FULL7",
                    "TST_FULL8"
                )
            ) ,
            array(
                '"rouges" OR "cheval"',
                array(
                    "TST_FULL3",
                    "TST_FULL6"
                )
            ) ,
            array(
                '"rouges" OR "cheval" OR animaux',
                array(
                    "TST_FULL3",
                    "TST_FULL4",
                    "TST_FULL5",
                    "TST_FULL6"
                )
            ) ,
            array(
                '("rouges" OR "cheval") AND animaux',
                array(
                    "TST_FULL3",
                    "TST_FULL6"
                )
            ) ,
            array(
                '~télé',
                array(
                    "TST_FULL2",
                    "TST_FULL9",
                    "TST_FULL1"
                )
            ) ,
            array(
                '~télé fixes',
                array(
                    "TST_FULL2"
                )
            ) ,
            array(
                '"fixe maison"',
                array(
                    "TST_FULL2"
                )
            ) ,
            array(
                '"maison fixe"',
                array()
            ) ,
            array(
                '"fixe" maisons',
                array(
                    "TST_FULL2"
                )
            ) ,
            array(
                'maisons "fixe"',
                array(
                    "TST_FULL2"
                )
            ) ,
            array(
                '~mais "fixe"',
                array(
                    "TST_FULL2"
                )
            )
        );
    }
    public function dataGeneralSortFilter()
    {
        return array(
            array(
                "rouge",
                "",
                array(
                    "TST_FULL8",
                    "TST_FULL5",
                    "TST_FULL7",
                    "TST_FULL6",
                    "TST_FULL4",
                    "TST_FULL3"
                )
            ) ,
            array(
                "animal cheval",
                "",
                array(
                    "TST_FULL3",
                    "TST_FULL6"
                )
            ) ,
            array(
                "rouge",
                "cheval",
                array(
                    "TST_FULL3",
                    "TST_FULL6"
                )
            ) ,
            array(
                "rouge",
                "cheval OR canin",
                array(
                    "TST_FULL4",
                    "TST_FULL3",
                    "TST_FULL6"
                )
            ) ,
            array(
                '"rouge" OR "cheval"',
                "",
                array(
                    "TST_FULL8",
                    "TST_FULL5",
                    "TST_FULL7",
                    "TST_FULL6"
                )
            ) ,
            array(
                '"rouge" OR chevaux',
                "",
                array(
                    "TST_FULL8",
                    "TST_FULL3",
                    "TST_FULL5"
                )
            ) ,
            array(
                '"rouge" OR chevaux OR ~télé',
                "",
                array(
                    "TST_FULL8",
                    "TST_FULL3",
                    "TST_FULL5"
                )
            )
        );
    }
}
?>