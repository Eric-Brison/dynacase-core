<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package Dcp\Pu
*/

namespace Dcp\Pu;

require_once 'PU_testcase_dcp_document.php';

class TestSimpleQuery extends TestCaseDcpDocument
{
    
    protected function createDataSearch()
    {
        
        $basics = array(
            "Poire",
            "Pomme",
            "Cerise",
            "Pomme-Banane",
            "Banane"
        );
        foreach ($basics as $socTitle) {
            $d1 = createDoc(self::$dbaccess, "BASE", false);
            $d1->setTitle($socTitle);
            $err = $d1->add();
            if ($err != "") return false;
        }
        return true;
    }
    /**
     * test basic search criteria
     * @param string $query filter
     * @param array $arg filter arg
     * @param integer $expectedCount expected results count
     * @return void
     * @dataProvider dataSimpleQuery
     */
    public function testExecuteSimpleQuery($query, array $arg, $expectedCount)
    {
        $result = array();
        $sql = vsprintf($query, $arg);
        $err = simpleQuery(self::$dbaccess, $sql, $result);
        
        $this->assertEmpty($err, sprintf("Simple query error %s", $sql));
        
        $this->assertEquals($expectedCount, count($result) , sprintf("Count must be %d (found %d) error %s", $expectedCount, count($result) , $sql));
    }
    /**
     * test basic search criteria
     * @param string $query filter
     * @param array $arg filter arg
     * @param array $expectedErrors expected errors
     * @return void
     * @dataProvider dataErrorSimpleQuery
     */
    public function testErrorSimpleQuery($query, array $arg, array $expectedErrors)
    {
        $result = array();
        $sql = vsprintf($query, $arg);
        
        try {
            $err = simpleQuery(self::$dbaccess, $sql, $result);
        }
        catch(\Exception $e) {
            $err = $e->getMessage();
        }
        $this->assertNotEmpty($err, sprintf("No error found in simple query %s", $sql));
        foreach ($expectedErrors as $errors) {
            $this->assertContains($errors, $err, sprintf("Not correct error for %s", $sql));
        }
    }
    /**
     * test basic search criteria
     * @param string $query filter
     * @param array $arg filter arg
     * @param array $expectedErrors expected errors
     * @return void
     * @dataProvider dataErrorSimpleQuery
     */
    public function testTolerantErrorSimpleQuery($query, array $arg, array $expectedErrors)
    {
        
        $result = array();
        $sql = vsprintf($query, $arg);
        $err = simpleQuery(self::$dbaccess, $sql, $result, false, false, $useStrict = false);
        
        $this->assertNotEmpty($err, sprintf("No error found in simple query %s", $sql));
        foreach ($expectedErrors as $errors) {
            $this->assertContains($errors, $err, sprintf("Not correct error for %s", $sql));
        }
    }
    /**
     * @return array
     */
    public function dataSimpleQuery()
    {
        
        return array(
            array(
                "q" => "select * from users where id=%d",
                "args" => array(
                    self::getAction()->user->id
                ) ,
                "count" => 1
            )
        );
    }
    /**
     * @return array
     */
    public function dataErrorSimpleQuery()
    {
        
        return array(
            array(
                "q" => "select * from users where id=%s",
                "args" => array(
                    "zut"
                ) ,
                "errors" => array(
                    'DB0100',
                    'zut'
                )
            )
        );
    }
}
