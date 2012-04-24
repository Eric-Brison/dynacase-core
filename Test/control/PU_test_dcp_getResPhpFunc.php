<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package DCP
*/

namespace PU;

require_once 'PU_testcase_dcp.php';
include_once 'WHAT/Lib.Http.php';
include_once 'FDL/enum_choice.php';

class TestGetResPhpFunc extends TestCaseDcp
{
    static public $externalsList = array();
    
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        
        self::addExternals("PU_data_dcp_getResPhpFunc.php");
        
        self::connectUser();
        self::beginTransaction();
        self::importDocument("PU_data_dcp_getResPhpFunc_family.ods");
    }
    
    public static function tearDownAfterClass()
    {
        self::rollbackTransaction();
        
        self::rollbackExternals();
        
        parent::tearDownAfterClass();
    }
    
    public function setUp()
    {
        return;
    }
    
    public function tearDown()
    {
        return;
    }
    
    public static function addExternals($file)
    {
        $source = '..' . DIRECTORY_SEPARATOR . 'DCPTEST' . DIRECTORY_SEPARATOR . $file;
        $destination = 'EXTERNALS' . DIRECTORY_SEPARATOR . basename($file);
        /* Check that the destination does not already exists */
        if (file_exists($destination)) {
            throw new \Exception(sprintf("External destination file '%s' already exists.", $destination));
        }
        /* Create the symlink */
        $ret = symlink($source, $destination);
        if ($ret === false) {
            self::rollbackExternals();
            throw new \Exception(sprintf("Could not symlink '%s' into '%s'.", $source, $destination));
        }
        
        self::$externalsList[] = $destination;
    }
    
    public static function rollbackExternals()
    {
        foreach (self::$externalsList as $file) {
            if (is_link($file)) {
                unlink($file);
            }
        }
    }
    /**
     * @dataProvider dataGetResPhpFunc
     */
    public function testExecuteGetResPhpFunc($data)
    {
        global $ZONE_ARGS;
        $ZONE_ARGS = array();
        
        $doc = false;
        if (isset($data['document'])) {
            $doc = new_Doc(self::$dbaccess, $data['document']);
            $this->assertTrue(is_object($doc) , sprintf("Error retrieving document '%s'.", $data['document']));
        } else {
            $doc = createDoc(self::$dbaccess, $data['fam']);
            $this->assertTrue(is_object($doc) , sprintf("Could not create new document from family '%s'.", $data['fam']));
            $err = $doc->add();
            $this->assertEmpty($err, sprintf("Could not add new document to database: %s", $err));
        }
        
        $oattr = $doc->getAttribute($data['attr']);
        $this->assertTrue(is_object($oattr) , sprintf("Could not get attribute '%s' on document '%s' (id=%s).", $data['attr'], $doc->name, $doc->id));
        
        if (isset($data['http:vars'])) {
            foreach ($data['http:vars'] as $name => $value) {
                SetHttpVar($name, $value);
            }
        }
        
        $rargids = array();
        $tselect = array();
        $tval = array();
        $res = getResPhpFunc($doc, $oattr, $rargids, $tselect, $tval);
        $this->assertTrue(is_array($res) , sprintf("getResPhpFunc did not returned an array."));
        
        if (isset($data['expected:results'])) {
            $err = $this->checkResult($data['expected:results'], $res);
            $this->assertEmpty($err, sprintf("Expected results not met: %s", $err));
        }
        
        if (isset($data['expected:rargids'])) {
            $err = $this->checkRargids($data['expected:rargids'], $rargids);
            $this->assertEmpty($err, sprintf("Expected rargids not met: %s", $err));
        }
    }
    /**
     * @dataProvider dataBadGetResPhpFunc
     */
    public function testGetResPhpFuncError($family, $attrid, array $inputs, array $expectedErrors)
    {
        global $ZONE_ARGS;
        $ZONE_ARGS = array();
        
        $doc = createDoc(self::$dbaccess, $family);
        $this->assertTrue(is_object($doc) , sprintf("Could not create new document from family '%s'.", $family));
        $err = $doc->add();
        $this->assertEmpty($err, sprintf("Could not add new document to database: %s", $err));
        
        $oattr = $doc->getAttribute($attrid);
        $this->assertTrue(is_object($oattr) , sprintf("Could not get attribute '%s' on document '%s' (id=%s).", $attrid, $doc->name, $doc->id));
        
        foreach ($inputs as $name => $value) {
            SetHttpVar($name, $value);
        }
        
        $rargids = array();
        $tselect = array();
        $tval = array();
        $res = getResPhpFunc($doc, $oattr, $rargids, $tselect, $tval);
        $this->assertTrue(is_string($res) , sprintf("getResPhpFunc mist return errors."));
        //error_log($res);
        foreach ($expectedErrors as $error) {
            $this->assertContains($error, $res, "getResPhpFunc not the expected error");
        }
    }
    private function checkResult(&$expectedResult, &$actualResult)
    {
        $expectedCount = count($expectedResult);
        $actualCount = count($actualResult);
        if ($actualCount != $expectedCount) {
            return sprintf("Result count mismatch: found '%s' while expecting '%s'", $actualCount, $expectedCount);
        }
        
        for ($line = 0; $line < $expectedCount; $line++) {
            $expectedLine = $expectedResult[$line];
            $actualLine = $actualResult[$line];
            $expectedLineCount = count($expectedLine);
            $actualLineCount = count($actualLine);
            if ($actualLineCount != $expectedLineCount) {
                return sprintf("Line #%s count mismatch: found '%s' while expecting '%s'", $line, $actualLineCount, $expectedLineCount);
            }
            
            for ($col = 0; $col < $expectedLineCount; $col++) {
                $actualCell = $actualLine[$col];
                $expectedCell = $expectedLine[$col];
                if ($actualCell != $expectedCell) {
                    return sprintf("Data mismatch at Line #%s, Column #%s: found '%s' while expecting '%s'", $line, $col, $actualCell, $expectedCell);
                }
            }
        }
        return '';
    }
    
    private function checkRargids(&$expectedRargsid, &$actualRargids)
    {
        $expectedCount = count($expectedRargsid);
        $actualCount = count($actualRargids);
        if ($actualCount != $expectedCount) {
            return sprintf("Rargids count mismatch: found '%s' while expecting '%s'", $actualCount, $expectedCount);
        }
        
        for ($i = 0; $i < $expectedCount; $i++) {
            $expected = $expectedRargsid[$i];
            $actual = $actualRargids[$i];
            if ($actual != $expected) {
                return sprintf("Data mismatch at index #%s: found '%s' while expecting '%s'", $i, $actual, $expected);
            }
        }
        return '';
    }
    
    public function dataGetResPhpFunc()
    {
        return array(
            array(
                array(
                    'fam' => 'TST_GETRESPHPFUNC',
                    'attr' => 'S_GRAVITE',
                    'expected:results' => array(
                        array(
                            'mineure',
                            'Mi'
                        ) ,
                        array(
                            'majeure',
                            'Ma'
                        ) ,
                        array(
                            'bloquante',
                            'Bl'
                        )
                    ) ,
                    'expected:rargids' => array(
                        'S_GRAVITE'
                    )
                )
            ) ,
            array(
                array(
                    'fam' => 'TST_GETRESPHPFUNC',
                    'attr' => 'S_TITLE_1',
                    'http:vars' => array(
                        '_s_title_1' => 'Relation'
                    ) ,
                    'expected:results' => array(
                        array(
                            'Test Relation 2',
                            'Test Relation 2'
                        ) ,
                        array(
                            'Test Relation 3',
                            'Test Relation 3'
                        )
                    ) ,
                    'expected:rargids' => array(
                        'S_TITLE_1'
                    )
                )
            ) ,
            array(
                array(
                    'fam' => 'TST_GETRESPHPFUNC',
                    'attr' => 'S_TITLE_2',
                    'http:vars' => array(
                        '_s_title_2' => 'Relation'
                    ) ,
                    'expected:results' => array(
                        array(
                            'Test Relation 2',
                            'Test Relation 2'
                        ) ,
                        array(
                            'Test Relation 3',
                            'Test Relation 3'
                        )
                    ) ,
                    'expected:rargids' => array(
                        'S_TITLE_2'
                    )
                )
            ) ,
            /*
            array(
                array(
                    'fam' => 'TST_GETRESPHPFUNC',
                    'attr' => 'S_SPACE_1',
                    'http:vars' => array(
                        '_arg_1' => 'Arg 1',
                        '_arg_2' => 'Arg 2',
                        '_arg_3' => 'Arg 3'
                    ),
                    'expected:results' => array(
                        array(
                            'Arg 1, Arg 2, Arg 3',
                            'Arg 1, Arg 2, Arg 3'
                        )
                    ),
                    'expected:rargids' => array(
                        'RET_1',
                        'RET_2',
                        'RET_3'
                    )
                )
            ) ,
            array(
                array(
                    'fam' => 'TST_GETRESPHPFUNC',
                    'attr' => 'S_QUOTE_1',
                    'expected:results' => array(
                        array(
                            'Arg 1, Arg 2"Deux, Arg 3'.chr(0x27).'Trois',
                            'Arg 1, Arg 2"Deux, Arg 3'.chr(0x27).'Trois'
                        )
                    ),
                    'expected:rargids' => array(
                        'RET'
                    )
                )
            )
            */
        );
    }
    
    public function dataBadGetResPhpFunc()
    {
        return array(
            array(
                "TST_GETRESPHPFUNC",
                'S_LATIN1',
                array(
                    "_s_latin1" => "été"
                ) ,
                array(
                    'INH0002',
                    "s_latin1"
                )
            ) ,
            array(
                "TST_GETRESPHPFUNC",
                'S_WRONGARRAY',
                array(
                    "_s_wrongarray" => "test"
                ) ,
                array(
                    'INH0001',
                    "s_wrongarray"
                )
            )
        );
    }
}
?>