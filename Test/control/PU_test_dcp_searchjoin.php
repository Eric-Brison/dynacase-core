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

class TestSearchJoin extends TestCaseDcpCommonFamily
{
    /**
     * import TST_SEARCHJOIN1 and TST_SEARCHJOIN2 family
     * @static
     * @return string
     */
    protected static function getCommonImportFile()
    {
        return "PU_data_dcp_search_join.ods";
    }
    /**
     * Test join search
     * @param string $join join query
     * @param string $filter filters query
     * @param string $filterVar Variable for filter
     * @dataProvider dataSearchJoin
     */
    public function testSearchJoinExecute($join, $filter, $filterVar)
    {
        include_once ("FDL/Class.SearchDoc.php");
        $s = new \SearchDoc(self::$dbaccess, "TST_SEARCHJOIN2");
        $s->join($join);
        $s->addFilter($filter, $filterVar);
        $s->search();
        $array = $s->getSearchInfo();
        $err = $array["error"];
        $this->assertEmpty($err, "An error was found when trying a join search:" . $err);
    }
    
    public function dataSearchJoin()
    {
        return array(
            array(
                "tst_title = tst_searchjoin1(tst_title)",
                "tst_searchjoin1.tst_attr1 = '%s'",
                "Youpi"
            ) ,
            array(
                "tst_join::int = tst_searchjoin1(initid)",
                "lower(tst_searchjoin1.tst_title) = '%s'",
                "youpi"
            ) ,
            array(
                "tst_join::int = tst_searchjoin1(initid)",
                "tst_searchjoin1.tst_title = '%s'",
                "youpi"
            ) ,
        );
    }
}
