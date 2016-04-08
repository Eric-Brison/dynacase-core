<?php
/*
 * @author Anakeen
 * @package Dcp\Pu
*/

namespace Dcp\Pu;

require_once 'PU_testcase_dcp_commonfamily.php';

class TestAffect extends TestCaseDcpCommonFamily
{
    
    public static function getCommonImportFile()
    {
        return array(
            'PU_data_dcp_TestAffect.ods'
        );
    }
    
    protected $famName = "TST_AFFECT1";
    /**
     * test basic affect hook
     * @param string $family family name or id
     * @return void
     * @dataProvider dataAffect
     */
    public function testAffect($family, $count, $one, $two)
    {
        $s = new \SearchDoc(self::$dbaccess, $family);
        $s->setObjectReturn(true);
        $s->setOrder("initid");
        $s->search();
        
        $this->assertEquals($s->count(), $count);
        $dl=$s->getDocumentList();

        $k=0;
        /**
         * @var TestAffect1 $doc
         */
        foreach ($dl as $doc) {
            $this->assertEquals($one[$k],$doc->getOne(), sprintf("index %d => %s", $k, $doc->getOne()));
            $this->assertEquals($two[$k],$doc->getTwo(), sprintf("index %d => %s", $k, $doc->getTwo()));
            $k++;
        }
    }
    
    public function dataAffect()
    {
        return array(
            array($this->famName,6,
                array(1,2,3,1,2,3),
                array(0,0,0,2,4,6)
                )
        );
    }
}
