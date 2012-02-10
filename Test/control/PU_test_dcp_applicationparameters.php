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

require_once 'PU_testcase_dcp.php';

class TestApplicationParameters extends TestCaseDcp
{
    /**
     * @dataProvider dataParameters
     */
    public function testGetParam($parameters, array $expectedValues)
    {
        $appTest = new \Application(self::$dbaccess);
        $appTest->set("DCPTEST", $parent = null);
        $this->assertTrue($appTest->isAffected() , sprintf("DCPTEST app not found"));
        $appTest->param->DelStatic($appTest->id);
        $appTest->InitAllParam($parameters, $update = true);
        
        foreach ($expectedValues as $k => $v) {
            $this->assertEquals($v, $appTest->GetParam($k) , sprintf("wrong value for %s", $k));
        }
    }
    /**
     * @dataProvider dataGlobParameters
     */
    public function testGlobParam(array $parameters, array $expectedGlob, array $expectedNoGlob)
    {
        $appTest = new \Application(self::$dbaccess);
        $appTest->set("DCPTEST", $parent = null);
        $this->assertTrue($appTest->isAffected() , sprintf("DCPTEST app not found"));
        $appTest->InitAllParam($parameters, $update = true);
        $this->globParamTest($appTest, $expectedGlob, $expectedNoGlob);
    }
    /**
     * @dataProvider dataGlobMigrParameters
     */
    public function testGlobMigrParam(array $parameters1, array $parameters2, array $expectedGlob, array $expectedNoGlob)
    {
        $appTest = new \Application(self::$dbaccess);
        $appTest->set("DCPTEST", $parent = null);
        $this->assertTrue($appTest->isAffected() , sprintf("DCPTEST app not found"));
        $appTest->InitAllParam($parameters1, $update = true);
        $appTest->InitAllParam($parameters2, $update = true);
        $this->globParamTest($appTest, $expectedGlob, $expectedNoGlob);
    }
    /**
     * @dataProvider dataGlobParameters
     */
    protected function globParamTest(\Application & $appTest, array $expectedGlob, array $expectedNoGlob)
    {
        foreach ($expectedGlob as $globId) {
            $p = new \Param(self::$dbaccess, array(
                $globId,
                PARAM_GLB,
                $appTest->id
            ));
            $this->assertTrue($p->isAffected() , sprintf("not paramvalue glob %s", $globId));
            $pdef = new \ParamDef(self::$dbaccess, $globId);
            $this->assertTrue($pdef->isAffected() , sprintf("not paramdef %s", $globId));
            $this->assertEquals('Y', $pdef->isglob, sprintf("not a paramdef glob %s", $globId));
        }
        
        foreach ($expectedNoGlob as $globId) {
            $p = new \Param(self::$dbaccess, array(
                $globId,
                PARAM_APP,
                $appTest->id
            ));
            $this->assertTrue($p->isAffected() , sprintf("not paramvalue app %s", $globId));
            $pdef = new \ParamDef(self::$dbaccess, $globId);
            $this->assertTrue($pdef->isAffected() , sprintf("not paramdef %s", $globId));
            $this->assertEquals('N', $pdef->isglob, sprintf("not a paramdef app %s", $globId));
        }
    }
    public function dataParameters()
    {
        return array(
            array(
                array(
                    "VERSION" => "0.3.2-2",
                    "TST_NAMEP1" => array(
                        "val" => "Zoo Land",
                        "descr" => N_("Name of the zoo") ,
                        "global" => "N",
                        "user" => "N"
                    ) ,
                    "TST_GLOB1" => array(
                        "val" => "Glob one",
                        "descr" => N_("Name of the glob") ,
                        "global" => "N",
                        "user" => "N"
                    )
                ) ,
                array(
                    "TST_NAMEP1" => "Zoo Land",
                    "VERSION" => "0.3.2-2",
                    "TST_GLOB1" => "Glob one"
                )
            ) ,
            array(
                array(
                    "VERSION" => "0.3.2-2",
                    "TST_NAMEP1" => array(
                        "val" => "Zoo Land",
                        "descr" => N_("Name of the zoo") ,
                        "global" => "Y",
                        "user" => "N"
                    ) ,
                    "TST_GLOB2" => array(
                        "val" => "Glob two",
                        "descr" => N_("Name of the glob") ,
                        "global" => "N",
                        "user" => "Y"
                    )
                ) ,
                array(
                    "TST_NAMEP1" => "Zoo Land",
                    "VERSION" => "0.3.2-2",
                    "TST_GLOB2" => "Glob two"
                )
            )
        );
    }
    
    public function dataGlobParameters()
    {
        return array(
            array(
                array(
                    "VERSION" => "0.3.2-2",
                    "TST_NAMEP1" => array(
                        "val" => "Zoo Land",
                        "descr" => N_("Name of the zoo") ,
                        "global" => "N",
                        "user" => "N"
                    ) ,
                    "TST_GLOB1" => array(
                        "val" => "Glob one",
                        "descr" => N_("Name of the glob") ,
                        "global" => "Y",
                        "user" => "N"
                    )
                ) ,
                "glob" => array(
                    "TST_GLOB1"
                ) ,
                "noglob" => array(
                    "TST_NAMEP1",
                    "VERSION"
                )
            )
        );
    }
    
    public function dataGlobMigrParameters()
    {
        return array(
            array(
                "conf1" => array(
                    "VERSION" => "0.3.2-2",
                    "TST_NAMEP1" => array(
                        "val" => "Zoo Land",
                        "descr" => N_("Name of the zoo") ,
                        "global" => "N",
                        "user" => "N"
                    ) ,
                    "TST_GLOB1" => array(
                        "val" => "Glob one",
                        "descr" => N_("Name of the glob") ,
                        "global" => "Y",
                        "user" => "N"
                    )
                ) ,
                "conf2" => array(
                    "TST_NAMEP1" => array(
                        "val" => "Zoo Land",
                        "descr" => N_("Name of the zoo") ,
                        "global" => "Y",
                        "user" => "N"
                    ) ,
                    "TST_GLOB1" => array(
                        "val" => "Glob one",
                        "descr" => N_("Name of the glob") ,
                        "global" => "N",
                        "user" => "N"
                    )
                ) ,
                "glob" => array(
                    
                    "TST_NAMEP1"
                ) ,
                "noglob" => array(
                    "TST_GLOB1",
                    "VERSION"
                )
            )
        );
    }
}
?>