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

require_once 'PU_testcase_dcp_commonfamily.php';

class TestDocControlSubstitute extends TestCaseDcpCommonFamily
{
    protected static $outputDir;
    /**
     * import TST_FAMSETVALUE family
     * @static
     * @return string
     */
    protected static function getCommonImportFile()
    {
        return array(
            "PU_data_dcp_substitute_family.ods"
        );
    }
    
    private function getSearchName(\SearchDoc & $s)
    {
        $names = array();
        while ($doc = $s->nextDoc()) {
            $names[] = $doc->name;
        }
        return $names;
    }
    /**
     * @dataProvider dataControlIncumbent
     */
    public function testControlIncumbent($login, array $incumbents, array $expectedDocNames)
    {
        $nu = $this->sudo($login);
        
        foreach ($incumbents as $aIncumbent) {
            $u = new \Account();
            $u->setLoginName($aIncumbent);
            if (!$u->isAffected()) $this->markTestIncomplete("cannot find $aIncumbent account");
            $err = $u->setSubstitute($nu->id);
            $this->assertEmpty($err, "substitute error: $err");
        }
        $s = new \SearchDoc(self::$dbaccess, 'TST_SUBSTITUTE1');
        $s->setObjectReturn();
        $s->search();
        $err = $s->getError();
        $this->assertEmpty($err, "substitute search error: $err");
        
        $names = $this->getSearchName($s);
        
        $this->assertEquals(count($expectedDocNames) , $s->count() , sprintf("not expected items:\n\tfound  : %s \n\texpect : %s", implode(',', $names) , implode(',', $expectedDocNames)));
        
        $this->assertEquals(count($expectedDocNames) , count(array_intersect($names, $expectedDocNames)) , sprintf("not expected items.\n\t found  : %s\n\t expect : %s", implode(',', $names) , implode(',', $expectedDocNames)));
        $this->exitSudo();
    }
    /**
     * @dataProvider dataControlSubstitute
     */
    public function testControlSubstitute(array $substituts, array $expectedDocNamesByLogin)
    {
        
        foreach ($substituts as $incumbent => $aSubstitute) {
            $u = new \Account();
            $u->setLoginName($incumbent);
            if (!$u->isAffected()) $this->markTestIncomplete("cannot find $incumbent account");
            $err = $u->setSubstitute($aSubstitute);
            $this->assertEmpty($err, "substitute error : $err");
        }
        foreach ($expectedDocNamesByLogin as $login => $expectNames) {
            $this->sudo($login);
            $s = new \SearchDoc(self::$dbaccess, 'TST_SUBSTITUTE1');
            $s->setObjectReturn();
            $s->search();
            $err = $s->getError();
            $this->assertEmpty($err, "substitute search error");
            
            $names = $this->getSearchName($s);
            
            $this->assertEquals(count($expectNames) , $s->count() , sprintf("not expected items:\n\tfound  : %s \n\texpect : %s", implode(',', $names) , implode(',', $expectNames)));
            
            $this->assertEquals(count($expectNames) , count(array_intersect($names, $expectNames)) , sprintf("not expected items.\n\t found  : %s\n\t expect : %s", implode(',', $names) , implode(',', $expectNames)));
            $this->exitSudo();
        }
    }
    /**
     * @dataProvider dataControlReSubstitute
     */
    public function testControlReSubstitute(array $previousSubstitues, array $substituts, array $expectedDocNamesByLogin)
    {
        
        foreach ($previousSubstitues as $incumbent => $aSubstitute) {
            $u = new \Account();
            $u->setLoginName($incumbent);
            if (!$u->isAffected()) $this->markTestIncomplete("cannot find $incumbent account");
            $err = $u->setSubstitute($aSubstitute);
            $this->assertEmpty($err, "substitute error : $err");
        }
        $this->testControlSubstitute($substituts, $expectedDocNamesByLogin);
    }
    /**
     * @dataProvider dataControlStrict
     */
    public function testControlStrict($login, array $incumbents, array $expectedDocNames)
    {
        
        $nu = $this->sudo($login);
        foreach ($incumbents as $aIncumbent) {
            $u = new \Account();
            $u->setLoginName($aIncumbent);
            if (!$u->isAffected()) $this->markTestIncomplete("cannot find $aIncumbent account");
            $err = $u->setSubstitute($nu->id);
            $this->assertEmpty($err, "substitute error : $err");
        }
        clearCacheDoc();
        foreach ($expectedDocNames as $docName => $expectControl) {
            $d = new_doc(self::$dbaccess, $docName);
            $this->assertEquals($expectControl["normal"], $d->control('view', false) == "", sprintf("not correct normal control view for %s", $docName));
            
            $this->assertEquals($expectControl["strict"], $d->control('view', true) == "", sprintf("not correct strict control view for %s", $docName));
        }
        $this->exitSudo();
    }
    
    public function dataControlReSubstitute()
    {
        return array(
            array(
                
                "previousSubstitutes" => array(
                    "ured" => "ublue",
                    "ublue" => "ured"
                ) ,
                "substitutes" => array(
                    "ured" => "uyellow",
                    "ublue" => "uyellow"
                ) ,
                "expect" => array(
                    "ured" => array(
                        'TST_D1',
                        'TST_D2',
                        'TST_D6'
                    ) ,
                    "ublue" => array(
                        'TST_D2',
                        'TST_D3',
                        'TST_D4',
                        'TST_D7'
                    ) ,
                    "uyellow" => array(
                        'TST_D1',
                        'TST_D2',
                        'TST_D3',
                        'TST_D4',
                        'TST_D5',
                        'TST_D6',
                        'TST_D7',
                        'TST_D8'
                    )
                )
            )
        );
    }
    public function dataControlSubstitute()
    {
        return array(
            array(
                "substitutes" => array(
                    "ured" => "uyellow",
                    "ublue" => "uyellow"
                ) ,
                "expect" => array(
                    "ured" => array(
                        'TST_D1',
                        'TST_D2',
                        'TST_D6'
                    ) ,
                    "ublue" => array(
                        'TST_D2',
                        'TST_D3',
                        'TST_D4',
                        'TST_D7'
                    ) ,
                    "uyellow" => array(
                        'TST_D1',
                        'TST_D2',
                        'TST_D3',
                        'TST_D4',
                        'TST_D5',
                        'TST_D6',
                        'TST_D7',
                        'TST_D8'
                    )
                )
            ) ,
            array(
                "substitutes" => array(
                    "ured" => "ublue",
                    "ublue" => "uyellow"
                ) ,
                "expect" => array(
                    "ured" => array(
                        'TST_D1',
                        'TST_D2',
                        'TST_D6'
                    ) ,
                    "ublue" => array(
                        'TST_D1',
                        'TST_D2',
                        'TST_D6',
                        'TST_D3',
                        'TST_D4',
                        'TST_D7'
                    ) ,
                    "uyellow" => array(
                        'TST_D2',
                        'TST_D3',
                        'TST_D4',
                        'TST_D5',
                        'TST_D7',
                        'TST_D8'
                    )
                )
            ) ,
            array(
                "substitutes" => array(
                    "ured" => "ublue",
                    "uyellow" => "ured"
                ) ,
                "expect" => array(
                    "ured" => array(
                        'TST_D1',
                        'TST_D2',
                        'TST_D5',
                        'TST_D8',
                        'TST_D6'
                    ) ,
                    "ublue" => array(
                        'TST_D2',
                        'TST_D3',
                        'TST_D4',
                        'TST_D7',
                        'TST_D1',
                        'TST_D6'
                    ) ,
                    "uyellow" => array(
                        'TST_D5',
                        'TST_D8'
                    )
                )
            )
        );
    }
    
    public function dataControlIncumbent()
    {
        return array(
            array(
                "login" => "ured",
                "incumbent" => array() ,
                "expect" => array(
                    'TST_D1',
                    'TST_D2',
                    'TST_D6'
                )
            ) ,
            array(
                "login" => "ublue",
                "incumbent" => array() ,
                "expect" => array(
                    'TST_D2',
                    'TST_D3',
                    'TST_D4',
                    'TST_D7'
                )
            ) ,
            array(
                "login" => "uyellow",
                "incumbent" => array() ,
                "expect" => array(
                    'TST_D5',
                    'TST_D8'
                )
            ) ,
            array(
                "login" => "uyellow",
                "incumbent" => array(
                    "ured"
                ) ,
                "expect" => array(
                    'TST_D1',
                    'TST_D2',
                    'TST_D6',
                    'TST_D8',
                    'TST_D5'
                )
            ) ,
            array(
                "login" => "uyellow",
                "incumbent" => array(
                    "ublue"
                ) ,
                "expect" => array(
                    'TST_D2',
                    'TST_D3',
                    'TST_D4',
                    'TST_D7',
                    'TST_D8',
                    'TST_D5'
                )
            ) ,
            array(
                "login" => "uyellow",
                "incumbent" => array(
                    "ublue",
                    "ured"
                ) ,
                "expect" => array(
                    'TST_D1',
                    'TST_D2',
                    'TST_D3',
                    'TST_D4',
                    'TST_D6',
                    'TST_D7',
                    'TST_D8',
                    'TST_D5'
                )
            )
        );
    }
    
    public function dataControlStrict()
    {
        return array(
            array(
                "login" => "ured",
                "incumbent" => array() ,
                "expect" => array(
                    'TST_D1' => array(
                        "strict" => true,
                        "normal" => true
                    ) ,
                    'TST_D2' => array(
                        "strict" => true,
                        "normal" => true
                    ) ,
                    'TST_D3' => array(
                        "strict" => false,
                        "normal" => false
                    ) ,
                    'TST_D4' => array(
                        "strict" => false,
                        "normal" => false
                    ) ,
                    'TST_D5' => array(
                        "strict" => false,
                        "normal" => false
                    ) ,
                    'TST_D6' => array(
                        "strict" => true,
                        "normal" => true
                    ) ,
                    'TST_D7' => array(
                        "strict" => false,
                        "normal" => false
                    ) ,
                    'TST_D8' => array(
                        "strict" => false,
                        "normal" => false
                    ) ,
                )
            ) ,
            array(
                "login" => "uyellow",
                "incumbent" => array(
                    "ured"
                ) ,
                "expect" => array(
                    'TST_D1' => array(
                        "strict" => false,
                        "normal" => true
                    ) ,
                    'TST_D2' => array(
                        "strict" => false,
                        "normal" => true
                    ) ,
                    'TST_D3' => array(
                        "strict" => false,
                        "normal" => false
                    ) ,
                    'TST_D4' => array(
                        "strict" => false,
                        "normal" => false
                    ) ,
                    'TST_D5' => array(
                        "strict" => true,
                        "normal" => true
                    ) ,
                    'TST_D6' => array(
                        "strict" => false,
                        "normal" => true
                    ) ,
                    'TST_D7' => array(
                        "strict" => false,
                        "normal" => false
                    ) ,
                    'TST_D8' => array(
                        "strict" => true,
                        "normal" => true
                    ) ,
                )
            )
        );
    }
}
