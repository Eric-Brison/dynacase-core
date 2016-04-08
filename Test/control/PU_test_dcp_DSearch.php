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

class TestDSearch extends TestCaseDcpCommonFamily
{
    static function getCommonImportFile()
    {
        return array(
            'PU_data_dcp_dsearch.ods'
        );
    }
    /**
     * @dataProvider dataDSearchConstraints
     */
    public function testDSearchConstraints($data)
    {
        $this->requiresCoreParamEquals('CORE_LANG', 'fr_FR');
        
        $doc = new_Doc(self::$dbaccess, $data['doc']);
        $this->assertTrue(($doc !== null && $doc->isAlive()) , sprintf("Could not find document with id '%s'.", $data['doc']));
        $info = array();
        $err = $doc->store($info);
        $this->assertNotEmpty($err, "Store of document with id '%s' did not returned an expected error message.", $data['doc']);
        foreach ($data['expect'] as $re) {
            $match = preg_match($re, $err);
            $this->assertTrue(($match !== false && $match > 0) , sprintf("Error message '%s' did not matched expected '%s'.", $err, $re));
        }
    }
    
    public function dataDSearchConstraints()
    {
        return array(
            array(
                array(
                    "doc" => "DSEARCH_01",
                    "expect" => array(
                        "/.* 's_text' .*: expression rationnelle invalide/u"
                    )
                )
            ) ,
            array(
                array(
                    "doc" => "DSEARCH_02",
                    "expect" => array(
                        "/.* 's_date' .*: horodatage malformé/u"
                    )
                )
            ) ,
            array(
                array(
                    "doc" => "DSEARCH_03",
                    "expect" => array(
                        "/'s_timestamp' .*: horodatage malformé/u"
                    )
                )
            ) ,
            array(
                array(
                    "doc" => "DSEARCH_04",
                    "expect" => array(
                        "/parenthèses non équilibrées/u"
                    )
                )
            )
        );
    }
}
