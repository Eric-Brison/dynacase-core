<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package Dcp\PuTEST
*/

namespace Dcp\Pu;

require_once 'PU_testcase_dcp_commonfamily.php';

class TestGetSortProperties extends TestCaseDcpCommonFamily
{
    protected static function getCommonImportFile()
    {
        return array(
            "PU_data_dcp_getSortProperties.ods"
        );
    }
    /**
     * @dataProvider data_getSortProperties
     * @param $data
     */
    public function test_getSortProperties($data)
    {
        if (!is_numeric($data['famid'])) {
            $data['famid'] = getIdFromName(self::$dbaccess, $data['famid']);
        }
        
        $famdoc = new \DocFam(self::$dbaccess, $data['famid']);
        $properties = $famdoc->getSortProperties();
        $count = count($properties);
        $this->assertTrue(($count == $data['expected']['count']) , sprintf("Got '%d' properties while expecting '%d': %s", $count, $data['expected']['count'], print_r($properties, true)));
        
        foreach ($data['expected']['check'] as $check) {
            $found = false;
            foreach ($properties as $propName => $parameters) {
                if ($propName == $check['id']) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue($found, sprintf("Expected property '%s' not found.", $check['id']));
            $this->assertTrue(($parameters['sort'] == $check['sort']) , sprintf("Property '%s' has sort '%s' while expecting '%s'.", $propName, $parameters['sort'], $check['sort']));
        }
    }
    /**
     * @return array
     */
    public function data_getSortProperties()
    {
        return array(
            array(
                array(
                    'famid' => 'TST_GETSORTPROPERTIES_1',
                    'expected' => array(
                        'count' => 4,
                        'check' => array(
                            array(
                                'id' => 'title',
                                'sort' => 'desc'
                            ) ,
                            array(
                                'id' => 'initid',
                                'sort' => 'desc'
                            ) ,
                            array(
                                'id' => 'revdate',
                                'sort' => 'desc'
                            ) ,
                            array(
                                'id' => 'state',
                                'sort' => 'asc'
                            )
                        )
                    )
                )
            ) ,
            array(
                array(
                    'famid' => 'TST_GETSORTPROPERTIES_2',
                    'expected' => array(
                        'count' => 5,
                        'check' => array(
                            array(
                                'id' => 'title',
                                'sort' => 'asc'
                            ) ,
                            array(
                                'id' => 'initid',
                                'sort' => 'desc'
                            ) ,
                            array(
                                'id' => 'revdate',
                                'sort' => 'desc'
                            ) ,
                            array(
                                'id' => 'state',
                                'sort' => 'asc'
                            ) ,
                            array(
                                'id' => 'owner',
                                'sort' => 'asc'
                            )
                        )
                    )
                )
            )
        );
    }
}
