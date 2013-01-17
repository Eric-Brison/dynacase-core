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

require_once 'PU_testcase_dcp.php';
require_once 'PU_testcase_dcp_application.php';

class TestApplicationParameterManeger extends TestCaseDcpApplication
{

    const appName = "DCPTEST2";

    /**
     * Add conf
     *
     * @return array
     */
    public static function appConfig()
    {
        return array(
            "appRoot" => join(DIRECTORY_SEPARATOR, array(
                DEFAULT_PUBDIR,
                "DCPTEST",
                "app"
            )),
            "appName" => "TST_PARAMETER_MANAGER"
        );
    }

    /**
     * @param $parameters
     * @return \Application
     */
    private function initTestApplication($parameters)
    {
        $appTest = new \Application(self::$dbaccess);
        $appTest->name = self::appName;
        $appTest->childof = 'TST_PARAMETER_MANAGER';
        $err = $appTest->Add();

        $this->assertEmpty($err, "Cannot create application : $err");
        $parent = null;
        $appTest->set(self::appName, $parent);

        $this->assertTrue($appTest->isAffected(), sprintf("DCPTEST2 app not found"));

        $appTest->InitAllParam($parameters, $update = false);
        $a = $this->getAction();
        // add new parameters in current action
        $a->parent->param->SetKey($appTest->id, $a->user->id);

        \ApplicationParameterManager::resetCache();
        return $appTest;
    }

    /**
     * @dataProvider dataGetParam
     */
    public function testGetParam($parameters, array $expectedValues)
    {

        $this->initTestApplication($parameters);

        foreach ($expectedValues as $k => $v) {
            $this->assertEquals($v, \ApplicationParameterManager::getParameterValue(self::appName, $k), sprintf("wrong value for %s", $k));
        }
    }

    /**
     * @dataProvider dataGetParam
     */
    public function testGetUnknownParam($parameters)
    {

        $this->initTestApplication($parameters);

        $this->assertEquals(null, \ApplicationParameterManager::getParameterValue(self::appName, "UNKNOWN_OPTION_VALUE"));
    }

    /**
     * @dataProvider dataGetParam
     * @expectedException Dcp\ApplicationParameterManager\Exception
     */
    public function testSetUnknownParam($parameters)
    {

        $this->initTestApplication($parameters);

        \ApplicationParameterManager::setParameterValue(self::appName, "UNKNOWN_OPTION_VALUE", "25");
    }

    /**
     * @dataProvider dataGetParam
     */
    public function testGetScopedParam($parameters, array $expectedValues)
    {

        $this->initTestApplication($parameters);

        foreach ($expectedValues as $k => $v) {
            $this->assertEquals($v, \ApplicationParameterManager::getScopedParameterValue($k), sprintf("wrong value for %s", $k));
        }
    }

    /**
     * @dataProvider dataGetGlobalParam
     */
    public function testGetGlobalParam($parameters, array $expectedValues)
    {

        $this->initTestApplication($parameters);

        foreach ($expectedValues as $k => $v) {
            $this->assertEquals($v, \ApplicationParameterManager::getCommonParameterValue(\ApplicationParameterManager::GLOBAL_PARAMETER, $k), sprintf("wrong value for %s", $k));
        }
    }

    /**
     * @dataProvider dataSetApplicationParameter
     */
    public function testSetApplicationParameter($parameters, array $newValues, array $expectedValues)
    {

        $this->initTestApplication($parameters);

        foreach ($newValues as $k => $v) {
            \ApplicationParameterManager::setParameterValue(self::appName, $k, $v);
        }
        foreach ($expectedValues as $k => $v) {
            $this->assertEquals($v, \ApplicationParameterManager::getParameterValue(self::appName, $k), sprintf("wrong value for %s", $k));
        }
    }

    /**
     * @dataProvider dataSetGlobalParameter
     */
    public function testSetGlobalParameter($parameters, array $newValues, array $expectedValues)
    {

        $this->initTestApplication($parameters);

        foreach ($newValues as $k => $v) {

            \ApplicationParameterManager::setParameterValue(\ApplicationParameterManager::GLOBAL_PARAMETER, $k, $v);
        }
        foreach ($expectedValues as $k => $v) {
            $this->assertEquals($v, \ApplicationParameterManager::getParameterValue(\ApplicationParameterManager::GLOBAL_PARAMETER, $k), sprintf("wrong value for %s", $k));
        }
    }

    /**
     * @dataProvider dataSetUserApplicationParameter
     */
    public function testSetUserApplicationParameter($parameters, array $newValues, array $expectedValues)
    {

        $this->initTestApplication($parameters);

        foreach ($newValues as $k => $v) {
            \ApplicationParameterManager::setParameterValue(self::appName, $k, $v);
        }
        foreach ($expectedValues as $k => $v) {
            $this->assertEquals($v, \ApplicationParameterManager::getParameterValue(self::appName, $k), sprintf("wrong value for %s", $k));
        }
    }

    /**
     * @dataProvider dataGetParameters
     */
    public function testGetParameters($parameters)
    {

        $definedParametersKey = array_keys($parameters);

        $this->initTestApplication($parameters);
        $applicationParameters = \ApplicationParameterManager::getParameters(self::appName);
        $filterFunction = function ($value) use ($applicationParameters) {
            foreach ($applicationParameters as $currentApplicationParameters) {
                if ($currentApplicationParameters["name"] == $value) {
                    return false;
                }
            }
            return true;
        };
        $definedParametersKey = array_filter($definedParametersKey, $filterFunction);
        $this->assertEquals(count($definedParametersKey), 0);
    }

    /**
     * @dataProvider dataGetParameters
     */
    public function testGetParameter($parameters)
    {
        $this->initTestApplication($parameters);
        $applicationParameter = \ApplicationParameterManager::getParameter(self::appName, "TST_GLOB1");

        $this->assertEquals("TST_GLOB1", $applicationParameter["name"]);
        $this->assertEquals("Name of the glob", $applicationParameter["descr"]);
        $this->assertEquals("Y", $applicationParameter["isglob"]);
        $this->assertEquals("N", $applicationParameter["isuser"]);
    }


    public function dataUserSetGlobalParameter()
    {
        return array(
            array(
                "init" => array(
                    "VERSION" => "4.0.8",
                    "PARENT_USER_GLOBAL_PARAMETER_VALUE" => "128",
                    "TST_NAMEP1" => array(
                        "val" => "Test 2",
                        "descr" => "Name of test one",
                        "global" => "Y",
                        "user" => "Y"
                    ),
                    "TST_GLOB2" => array(
                        "val" => "Test global 2",
                        "descr" => "Name of the glob",
                        "global" => "Y",
                        "user" => "Y"
                    )
                ),
                "set" => array(
                    "TST_NAMEP1" => "Test 3",
                    "TST_GLOB2" => "Test G3",
                ),
                "expect" => array(
                    "TST_NAMEP1" => "Test 3",
                    "VERSION" => "4.0.8",
                    "TST_GLOB2" => "Test G3"
                )
            )
        );
    }

    public function dataSetUserApplicationParameter()
    {
        return array(
            array(
                "init" => array(
                    "VERSION" => "0.3.2-2",
                    "PARENT_USER_GLOBAL_PARAMETER_VALUE" => "128",
                    "TST_NAMEP1" => array(
                        "val" => "Test 2",
                        "descr" => "Name of test one",
                        "global" => "N",
                        "user" => "Y"
                    ),
                    "TST_GLOB2" => array(
                        "val" => "Test global 2",
                        "descr" => "Name of the glob",
                        "global" => "Y",
                        "user" => "Y"
                    )
                ),
                "set" => array(
                    "PARENT_USER_PARAMETER" => "128,127",
                    "TST_NAMEP1" => "Test 3",
                ),
                "expect" => array(
                    "TST_NAMEP1" => "Test 3",
                    "PARENT_USER_PARAMETER" => "128,127",
                    "TST_GLOB2" => "Test global 2"
                )
            )
        );
    }

    public function dataSetGlobalParameter()
    {
        return array(
            array(
                "init" => array(
                    "VERSION" => "4.0.8",
                    "PARENT_GLOBAL_PARAMETER" => "128",
                    "TST_NAMEP1" => array(
                        "val" => "Test 2",
                        "descr" => "Name of test one",
                        "global" => "Y",
                        "user" => "N"
                    ),
                    "TST_GLOB2" => array(
                        "val" => "Test global 2",
                        "descr" => "Name of the glob",
                        "global" => "Y",
                        "user" => "N"
                    )
                ),
                "set" => array(
                    "TST_NAMEP1" => "Test 3",
                    "TST_GLOB2" => "Test G3",
                ),
                "expect" => array(
                    "TST_NAMEP1" => "Test 3",
                    "TST_GLOB2" => "Test G3"
                )
            )
        );
    }

    public function dataSetApplicationParameter()
    {
        return array(
            array(
                "init" => array(
                    "VERSION" => "0.3.2-2",
                    "PARENT_PARAMETER" => "128",
                    "TST_NAMEP1" => array(
                        "val" => "Test 2",
                        "descr" => "Name of test one",
                        "global" => "N",
                        "user" => "N"
                    ),
                    "TST_GLOB2" => array(
                        "val" => "Test global 2",
                        "descr" => "Name of the glob",
                        "global" => "Y",
                        "user" => "N"
                    )
                ),
                "set" => array(
                    "VERSION" => "4.0.7",
                    "PARENT_PARAMETER" => "128,127",
                    "TST_NAMEP1" => "Test 3",
                ),
                "expect" => array(
                    "TST_NAMEP1" => "Test 3",
                    "VERSION" => "4.0.7",
                    "PARENT_PARAMETER" => "128,127",
                    "TST_GLOB2" => "Test global 2"
                )
            )
        );
    }

    public function dataGetParam()
    {
        return array(
            array(
                array(
                    "VERSION" => "0.3.2-2",
                    "PARENT_PARAMETER" => "128",
                    "TST_NAMEP1" => array(
                        "val" => "Test 1",
                        "descr" => "Name of test one",
                        "global" => "N",
                        "user" => "N"
                    ),
                    "TST_GLOB1" => array(
                        "val" => "Test global 1",
                        "descr" => "Name of the glob",
                        "global" => "Y",
                        "user" => "N"
                    )
                ),
                array(
                    "TST_NAMEP1" => "Test 1",
                    "VERSION" => "0.3.2-2",
                    "PARENT_PARAMETER" => "128",
                    "TST_GLOB1" => "Test global 1"
                )
            )
        );
    }

    public function dataGetGlobalParam()
    {
        return array(
            array(
                array(
                    "VERSION" => "0.3.2-2",
                    "PARENT_PARAMETER" => "128",
                    "TST_NAMEP1" => array(
                        "val" => "Test 1",
                        "descr" => "Name of test one",
                        "global" => "N",
                        "user" => "N"
                    ),
                    "TST_GLOB1" => array(
                        "val" => "Test global 2",
                        "descr" => "Name of the glob",
                        "global" => "Y",
                        "user" => "N"
                    )
                ),
                array(
                    "TST_GLOB1" => "Test global 2"
                )
            )
        );
    }

    public function dataGetParameters()
    {
        return array(
            array(
                array(
                    "VERSION" => "0.3.2-2",
                    "PARENT_PARAMETER" => "128",
                    "TST_NAMEP1" => array(
                        "val" => "Test 1",
                        "descr" => "Name of test one",
                        "global" => "N",
                        "user" => "N"
                    ),
                    "TST_GLOB1" => array(
                        "val" => "Test global 2",
                        "descr" => "Name of the glob",
                        "global" => "Y",
                        "user" => "N"
                    )
                )
            )
        );
    }
}

?>