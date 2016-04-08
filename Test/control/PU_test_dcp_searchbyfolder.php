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
/**
 * test some SearchDoc option like generalFilter
 */
class TestSearchByFolder extends TestCaseDcpCommonFamily
{
    /**
     * import TST_FULLSERACHFAM1 family and some documents
     * @static
     * @return string
     */
    protected static function getCommonImportFile()
    {
        return "PU_data_dcp_foldersearchfamily.ods";
    }
    /**
     * test "usefor" system search
     * @param string $dirId The identifer of the collection to use
     * @param array $existsNameList List of documents name that must be returned by the search
     * @param array $notExistsNameList List of documents name that must NOT be returned by the search
     * @return void
     * @dataProvider dataRecurisveSearch
     */
    public function testRecurisveSearch($dirId, $famId, $sublevel, $expectedName)
    {
        $dir = new_Doc(self::$dbaccess, $dirId);
        $this->assertTrue($dir->isAlive() , sprintf("Could not get search with id '%s'.", $dirId));
        
        $search = new \SearchDoc(self::$dbaccess, $famId);
        $search->setObjectReturn();
        $search->useCollection($dirId);
        $search->setRecursiveSearch(true, $sublevel);
        $search->search();
        $res = array();
        while ($doc = $search->getNextDoc()) {
            $res[] = $doc->name;
        }
        
        $this->assertEquals(count($expectedName) , $search->count() , sprintf("returns %s\n expected %s", print_r($res, true) , print_r($expectedName, true)));
        
        foreach ($expectedName as $name) {
            $this->assertTrue(in_array($name, $res) , sprintf("%s not found, returns %s\n expected %s", $name, print_r($res, true) , print_r($expectedName, true)));
        }
    }
    /**
     * test "usefor" system search
     * @param string $dirId The identifer of the collection to use
     * @param array $existsNameList List of documents name that must be returned by the search
     * @param array $notExistsNameList List of documents name that must NOT be returned by the search
     * @return void
     * @dataProvider dataRecurisveSearch
     */
    public function testCountRecurisveSearch($dirId, $famId, $sublevel, $expectedName)
    {
        $dir = new_Doc(self::$dbaccess, $dirId);
        $this->assertTrue($dir->isAlive() , sprintf("Could not get search with id '%s'.", $dirId));
        
        $search = new \SearchDoc(self::$dbaccess, $famId);
        $search->setObjectReturn();
        $search->useCollection($dirId);
        $search->setRecursiveSearch(true, $sublevel);
        $c = $search->onlyCount();
        
        $this->assertEquals(count($expectedName) , $c, sprintf("not expected cound"));
    }
    public function dataRecurisveSearch()
    {
        return array(
            array(
                "dirid" => "TST_SDIR1",
                "famid" => "",
                "level" => 10,
                "names" => array(
                    "TST_ABYFLD1",
                    "TST_ABYFLD2",
                    "TST_ABYFLD3",
                    "TST_ABYFLD4",
                    "TST_ABYFLD5",
                    "TST_BBYFLD1",
                    "TST_BBYFLD2",
                    "TST_BBYFLD3",
                    "TST_BBYFLD4",
                    "TST_BBYFLD5",
                    "TST_SDIR2",
                    "TST_SDIR3",
                    "TST_SDIR4",
                    "TST_SDIR5",
                )
            ) ,
            
            array(
                "dirid" => "TST_SDIR1",
                "famid" => "",
                "level" => 0,
                "names" => array(
                    "TST_ABYFLD1",
                    "TST_BBYFLD1",
                    "TST_SDIR2",
                    "TST_SDIR3",
                )
            ) ,
            array(
                "dirid" => "TST_SDIR1",
                "famid" => "",
                "level" => 1,
                "names" => array(
                    "TST_ABYFLD1",
                    "TST_BBYFLD1",
                    "TST_ABYFLD2",
                    "TST_BBYFLD2",
                    "TST_ABYFLD3",
                    "TST_BBYFLD3",
                    "TST_SDIR2",
                    "TST_SDIR3",
                    "TST_SDIR4",
                ) ,
                array(
                    "dirid" => "TST_SDIR1",
                    "famid" => "",
                    "level" => 2,
                    "names" => array(
                        "TST_ABYFLD1",
                        "TST_BBYFLD1",
                        "TST_ABYFLD2",
                        "TST_BBYFLD2",
                        "TST_ABYFLD3",
                        "TST_BBYFLD3",
                        "TST_ABYFLD4",
                        "TST_BBYFLD4",
                        "TST_SDIR2",
                        "TST_SDIR3",
                        "TST_SDIR4",
                        "TST_SDIR5",
                    )
                ) ,
                array(
                    "dirid" => "TST_SDIR1",
                    "famid" => "",
                    "level" => 3,
                    "names" => array(
                        "TST_ABYFLD1",
                        "TST_BBYFLD1",
                        "TST_ABYFLD2",
                        "TST_BBYFLD2",
                        "TST_ABYFLD3",
                        "TST_BBYFLD3",
                        "TST_ABYFLD4",
                        "TST_BBYFLD4",
                        "TST_ABYFLD5",
                        "TST_BBYFLD5",
                        "TST_SDIR2",
                        "TST_SDIR3",
                        "TST_SDIR4",
                        "TST_SDIR5",
                    )
                )
            ) ,
            array(
                "dirid" => "TST_SDIR1",
                "famid" => "DIR",
                "level" => 10,
                "names" => array(
                    "TST_SDIR2",
                    "TST_SDIR3",
                    "TST_SDIR4",
                    "TST_SDIR5"
                )
            ) ,
            
            array(
                "dirid" => "TST_SDIR1",
                "famid" => "DIR",
                "level" => 0,
                "names" => array(
                    "TST_SDIR2",
                    "TST_SDIR3"
                )
            ) ,
            array(
                "dirid" => "TST_SDIR1",
                "famid" => "TST_BYFOLDER_A",
                "level" => 1,
                "names" => array(
                    "TST_ABYFLD1",
                    "TST_ABYFLD2",
                    "TST_ABYFLD3"
                ) ,
                array(
                    "dirid" => "TST_SDIR1",
                    "famid" => "TST_BYFOLDER_A",
                    "level" => 2,
                    "names" => array(
                        "TST_BBYFLD1",
                        "TST_BBYFLD2",
                        "TST_BBYFLD3",
                        "TST_BBYFLD4"
                    )
                ) ,
                array(
                    "dirid" => "TST_SDIR2",
                    "famid" => "TST_BYFOLDER_A",
                    "level" => 3,
                    "names" => array(
                        "TST_ABYFLD2",
                        "TST_ABYFLD3",
                        "TST_ABYFLD4",
                        "TST_ABYFLD5",
                    )
                )
            )
        );
    }
}
?>