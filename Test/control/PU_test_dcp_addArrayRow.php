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
class TestAddArrayRow extends TestCaseDcpCommonFamily
{
    /**
     * import TST_UPDTATTR
     * @static
     * @return array|string
     */
    protected static function getCommonImportFile()
    {
        return array(
            "PU_data_dcp_addArrayRow_family.csv"
        );
    }
    /**
     * @dataProvider dataAddArrayRow
     */
    public function testExecuteAddArrayRow($data)
    {
        $doc = createDoc(self::$dbaccess, $data['fam'], false);
        $this->assertTrue(is_object($doc) , sprintf("Could not create new document from family '%s'.", $data['fam']));
        
        $err = $doc->add();
        $this->assertEmpty($err, sprintf("Error adding new document in database: %s", $err));
        
        $err = $doc->setLogicalName($data['name']);
        $this->assertEmpty($err, sprintf("Error setting logical identificator '%s' on new document: %s", $data['name'], $err));
        
        foreach ($data['rows'] as & $row) {
            $err = $doc->addArrayRow($data['array_attr_name'], $row['data'], $row['index']);
            $this->assertEmpty($err, sprintf("Error adding row {%s} to '%s': %s", join(', ', $row['data']) , $data['name'], $err));
        }
        unset($row);
        
        $err = $doc->modify();
        $this->assertEmpty($err, sprintf("modify() on '%s' returned with error: %s", $data['name'], $err));
        
        self::resetDocumentCache();
        
        $doc = new_Doc(self::$dbaccess, $data['name'], true);
        $this->assertTrue(is_object($doc) , sprintf("Error retrieving document '%s': %s", $data['name'], $err));
        
        foreach ($data['expected_tvalues'] as $colName => & $colData) {
            $tvalue = $doc->getTValue($colName);
            $this->assertTrue(is_array($tvalue) , sprintf("getTValue(%s) on document '%s' did not returned an array.", $colName, $data['name']));
            
            $tvalueCount = count($tvalue);
            $expectedCount = count($colData);
            $this->assertTrue(($tvalueCount == $expectedCount) , sprintf("Column size mismatch on column '%s' from document '%s' (actual size is '%s', while expecting '%s').", $colName, $data['name'], $tvalueCount, $expectedCount));
            
            foreach ($colData as $i => $expectedCellContent) {
                $tvalueCellContent = $tvalue[$i];
                $this->assertTrue(($tvalueCellContent == $expectedCellContent) , sprintf("Cell content '%s' did not matched expected content '%s' (document '%s' / column '%s' / line '%s' / column cells {%s})", $tvalueCellContent, $expectedCellContent, $data['name'], $colName, $i, join(', ', $tvalue)));
            }
        }
        unset($colData);
    }
    
    public function dataAddArrayRow()
    {
        return array(
            array(
                array(
                    'fam' => 'TST_ADDARRAYROW',
                    'name' => 'TST_ADDARRAYROW_DOC_01',
                    'array_attr_name' => 'ARR',
                    'rows' => array(
                        array(
                            'index' => - 1,
                            'data' => array(
                                'COL_1' => '1_1',
                                'COL_2' => '1_2',
                                'COL_3' => '1_3',
                                'COL_4' => '1_4'
                            )
                        ) ,
                        array(
                            'index' => - 1,
                            'data' => array(
                                'cOl_1' => '2_1'
                            )
                        ) ,
                        array(
                            'index' => - 1,
                            'data' => array()
                        ) ,
                        array(
                            'index' => - 1,
                            'data' => array(
                                'col_2' => '4_2',
                                'col_3' => '4_3',
                                'col_4' => '4_4'
                            )
                        )
                    ) ,
                    'expected_tvalues' => array(
                        'col_1' => array(
                            '1_1',
                            '2_1',
                            '',
                            ''
                        ) ,
                        'col_2' => array(
                            '1_2',
                            '',
                            '',
                            '4_2'
                        ) ,
                        'col_3' => array(
                            '1_3',
                            '',
                            '',
                            '4_3'
                        ) ,
                        'col_4' => array(
                            '1_4',
                            '',
                            '',
                            '4_4'
                        )
                    )
                )
            ) ,
            array(
                array(
                    'fam' => 'TST_ADDARRAYROW',
                    'name' => 'TST_ADDARRAYROW_DOC_02',
                    'array_attr_name' => 'ARR',
                    'rows' => array(
                        array(
                            'index' => 0,
                            'data' => array(
                                'col_2' => '4_2',
                                'col_3' => '4_3',
                                'col_4' => '4_4'
                            )
                        ) ,
                        array(
                            'index' => 0,
                            'data' => array()
                        ) ,
                        array(
                            'index' => 0,
                            'data' => array(
                                'cOl_1' => '2_1'
                            )
                        ) ,
                        array(
                            'index' => 0,
                            'data' => array(
                                'COL_1' => '1_1',
                                'COL_2' => '1_2',
                                'COL_3' => '1_3',
                                'COL_4' => '1_4'
                            )
                        )
                    ) ,
                    'expected_tvalues' => array(
                        'col_1' => array(
                            '1_1',
                            '2_1',
                            '',
                            ''
                        ) ,
                        'col_2' => array(
                            '1_2',
                            '',
                            '',
                            '4_2'
                        ) ,
                        'col_3' => array(
                            '1_3',
                            '',
                            '',
                            '4_3'
                        ) ,
                        'col_4' => array(
                            '1_4',
                            '',
                            '',
                            '4_4'
                        )
                    )
                )
            ) ,
            array(
                array(
                    'fam' => 'TST_ADDARRAYROW',
                    'name' => 'TST_ADDARRAYROW_DOC_03',
                    'array_attr_name' => 'ARR',
                    'rows' => array(
                        array(
                            'index' => - 1,
                            'data' => array(
                                'col_2' => '2_2',
                                'col_3' => '2_3',
                                'col_4' => '2_4'
                            )
                        ) ,
                        array(
                            'index' => 1,
                            'data' => array()
                        ) ,
                        array(
                            'index' => 2,
                            'data' => array(
                                'cOl_1' => '4_1'
                            )
                        ) ,
                        array(
                            'index' => 0,
                            'data' => array(
                                'COL_1' => '1_1',
                                'COL_2' => '1_2',
                                'COL_3' => '1_3',
                                'COL_4' => '1_4'
                            )
                        )
                    ) ,
                    'expected_tvalues' => array(
                        'col_1' => array(
                            '1_1',
                            '',
                            '',
                            '4_1'
                        ) ,
                        'col_2' => array(
                            '1_2',
                            '2_2',
                            '',
                            ''
                        ) ,
                        'col_3' => array(
                            '1_3',
                            '2_3',
                            '',
                            ''
                        ) ,
                        'col_4' => array(
                            '1_4',
                            '2_4',
                            '',
                            ''
                        )
                    )
                )
            )
        );
    }
}
?>