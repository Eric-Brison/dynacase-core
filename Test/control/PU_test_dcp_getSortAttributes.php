<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package DCPTEST
*/

namespace PU;

require_once 'PU_testcase_dcp_commonfamily.php';

class TestGetSortAttributes extends TestCaseDcpCommonFamily
{
    protected static function getCommonImportFile()
    {
        return array(
            "PU_data_dcp_getSortAttributes.ods"
        );
    }
    /**
     * @dataProvider data_getSortAttributes
     * @param $data
     */
    public function test_getSortAttributes($data)
    {
        if (!is_numeric($data['famid'])) {
            $data['famid'] = getIdFromName(self::$dbaccess, $data['famid']);
        }
        
        $famdoc = new \DocFam(self::$dbaccess, $data['famid']);
        $attributes = $famdoc->getSortAttributes();
        $count = count($attributes);
        $this->assertTrue(($count == $data['expected']['count']) , sprintf("Got %d attributes while expecting %s: %s", $count, $data['expected']['count'], print_r(array_map(function ($e)
        {
            return $e->id;
        }
        , $attributes) , true)));
        
        foreach ($data['expected']['check'] as $check) {
            $found = false;
            foreach ($attributes as $attr) {
                if ($attr->id == $check['id']) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue($found, sprintf("Expected attribute '%s' not found.", $check['id']));
            $this->assertTrue(($attr->getOption('sortable') == $check['sortable']) , sprintf("Attribute '%s' has sortable '%s' while expecting '%s'.", $attr->id, $attr->getOption('sortable') , $check['sortable']));
        }
    }
    /**
     * @return array
     */
    public function data_getSortAttributes()
    {
        return array(
            array(
                array(
                    'famid' => 'TST_GETSORTATTRIBUTES',
                    'expected' => array(
                        'count' => 2,
                        'check' => array(
                            array(
                                'id' => 's_text',
                                'sortable' => 'asc'
                            ) ,
                            array(
                                'id' => 's_date',
                                'sortable' => 'desc'
                            )
                        )
                    )
                )
            )
        );
    }
}
