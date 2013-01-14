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

class TestDocRel extends TestCaseDcpCommonFamily
{
    /**
     * import TST_FAMSETVALUE family
     * @static
     * @return string
     */
    protected static function getCommonImportFile()
    {
        return array(
            "PU_data_dcp_docrel_family.ods"
        );
    }
    private $base = array();
    /**
     * init 3 docs with 3 revisions
     */
    private function initBaseDocuments()
    {
        
        $n = array(
            "A",
            "B",
            "C"
        );
        
        foreach ($n as $L) {
            $d = createDoc("", "TST_DOCREL");
            for ($i = 0; $i < 3; $i++) {
                $bid = sprintf("%s%d", $L, $i);
                $d->setValue("tst_title", $bid);
                $d->store();
                $this->base[$bid] = $d->id;
                if ($i < 2) $d->revise();
            }
        }
    }
    /**
     * to search real ids
     * @param $val
     * @return array
     */
    private function translateName($val)
    {
        if (is_array($val)) {
            $tb = array();
            foreach ($val as $ka => $aVal) {
                $tbs = explode("<BR>", $aVal);
                $tids = array();
                foreach ($tbs as $single) {
                    $tids[] = $this->base[$single];
                }
                
                $tb[] = implode('<BR>', $tids);
            }
            return $tb;
        } else return $this->base[$val];
    }
    /**
     * @dataProvider dataDocRelUpdate
     */
    public function testDocRelUpdate($initValues, $updateValues, $expectValues)
    {
        $this->initBaseDocuments();
        $d = createDoc("", "TST_DOCREL");
        $err = '';
        foreach ($initValues as $aid => $val) {
            $err.= $d->setValue($aid, $this->translateName($val));
        }
        $err.= $d->store();
        $this->assertEmpty($err, "add error $err");
        foreach ($updateValues as $aid => $val) {
            $err.= $d->setValue($aid, $this->translateName($val));
        }
        $err.= $d->store();
        $this->assertEmpty($err, "update error $err");
        
        $rel = new \DocRel();
        $rel->sinitid = $d->id;
        $rels = $rel->getRelations();
        
        $this->assertEquals(count($expectValues) , count($rels) , sprintf("Not correct relations %s", print_r($rels, true)));
        foreach ($expectValues as $expectProps) {
            $aid = $expectProps["aid"];
            $this->assertEquals($expectProps["title"], $this->getRelTitle($rels, $aid, $expectProps["title"]) , sprintf("not correct title $aid expect %s : %s", $expectProps["title"], print_r($rels, true)));
            $this->assertEquals($this->base[$expectProps["id"]], $this->getRelId($rels, $aid, $this->base[$expectProps["id"]]) , "not correct id $aid");
        }
    }
    
    private function getRelTitle(array $rels, $key, $expect)
    {
        foreach ($rels as $rel) {
            if ($rel["type"] == $key && $rel["ctitle"] == $expect) return $rel["ctitle"];
        }
        return '';
    }
    
    private function getRelId(array $rels, $key, $expect)
    {
        foreach ($rels as $rel) {
            if ($rel["type"] == $key && $rel["cinitid"] == $expect) return $rel["cinitid"];
        }
        return '';
    }
    public function dataDocRelUpdate()
    {
        return array(
            array(
                "init" => array(
                    "tst_rel1" => "B2"
                ) ,
                "update" => array(
                    "tst_rel1" => "A2",
                    "tst_rel2" => "C2"
                ) ,
                "expect" => array(
                    array(
                        "aid" => "tst_rel1",
                        "id" => "A0",
                        "title" => "A2"
                    ) ,
                    array(
                        "aid" => "tst_rel2",
                        "id" => "C0",
                        "title" => "C2"
                    )
                )
            ) ,
            
            array(
                "init" => array(
                    "tst_rel1" => "B2"
                ) ,
                "update" => array() ,
                "expect" => array(
                    array(
                        "aid" => "tst_rel1",
                        "id" => "B0",
                        "title" => "B2"
                    )
                )
            ) ,
            array(
                "init" => array(
                    "tst_rel1" => "B2",
                    "tst_rel2" => "B0",
                    "tst_rels3" => "B1",
                ) ,
                "update" => array() ,
                "expect" => array(
                    array(
                        "aid" => "tst_rel1",
                        "id" => "B0",
                        "title" => "B2"
                    ) ,
                    array(
                        "aid" => "tst_rel2",
                        "id" => "B0",
                        "title" => "B2"
                    ) ,
                    array(
                        "aid" => "tst_rels3",
                        "id" => "B0",
                        "title" => "B2"
                    )
                )
            ) ,
            array(
                "init" => array(
                    "tst_rel1" => "B2",
                    "tst_rel2" => "C0",
                    "tst_rels3" => array(
                        "B1",
                        "B0",
                        "B2"
                    ) ,
                ) ,
                "update" => array() ,
                "expect" => array(
                    array(
                        "aid" => "tst_rel1",
                        "id" => "B0",
                        "title" => "B2"
                    ) ,
                    array(
                        "aid" => "tst_rel2",
                        "id" => "C0",
                        "title" => "C2"
                    ) ,
                    array(
                        "aid" => "tst_rels3",
                        "id" => "B0",
                        "title" => "B2"
                    )
                )
            ) ,
            array(
                "init" => array(
                    "tst_rels3" => array(
                        "B1",
                        "C0",
                        "B2"
                    ) ,
                ) ,
                "update" => array() ,
                "expect" => array(
                    
                    array(
                        "aid" => "tst_rels3",
                        "id" => "B0",
                        "title" => "B2"
                    ) ,
                    array(
                        "aid" => "tst_rels3",
                        "id" => "B0",
                        "title" => "B2"
                    )
                )
            ) ,
            
            array(
                "init" => array(
                    "tst_rels2" => array(
                        "B1<BR>A0",
                        "C0",
                        "B2<BR>A2"
                    ) ,
                ) ,
                "update" => array() ,
                "expect" => array(
                    
                    array(
                        "aid" => "tst_rels2",
                        "id" => "B0",
                        "title" => "B2"
                    ) ,
                    array(
                        "aid" => "tst_rels2",
                        "id" => "A0",
                        "title" => "A2"
                    ) ,
                    array(
                        "aid" => "tst_rels2",
                        "id" => "B0",
                        "title" => "B2"
                    )
                )
            )
        );
    }
}
