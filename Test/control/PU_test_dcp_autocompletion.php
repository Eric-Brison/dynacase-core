<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package DCP
*/

namespace PU;

require_once 'PU_testcase_dcp_action.php';
include_once 'WHAT/Lib.Http.php';
include_once 'FDL/autocompletion.php';

class TestAutocompletion extends TestCaseDcpAction
{
    static public $externalsList = array();
    
    public $errmsg = '';
    
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        
        self::addExternals("PU_data_dcp_autocompletion.php");
        
        self::connectUser();
        self::beginTransaction();
        self::importDocument("PU_data_dcp_autocompletion_family.ods");
    }
    
    public static function tearDownAfterClass()
    {
        self::rollbackTransaction();
        
        self::rollbackExternals();
        
        parent::tearDownAfterClass();
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
    
    public function setUp()
    {
        $this->setUpTestAction("FDL", "AUTOCOMPLETION");
    }
    
    public function tearDown()
    {
        $this->resetCurrentParameters();
    }
    /**
     * @dataProvider dataAutocompletion
     */
    public function testAutocompletion($data)
    {
        global $ZONE_ARGS;
        $ZONE_ARGS = array();
        
        $doc = false;
        if (isset($data['document'])) {
            $doc = new_Doc(self::$dbaccess, $data['document'], true);
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
        
        $this->setCurrentParameters('docid', $doc->id);
        $this->setCurrentParameters('attrid', $data['attr']);
        if (isset($data['action:args'])) {
            foreach ($data['action:args'] as $name => $value) {
                $this->setCurrentParameters($name, $value);
            }
        }
        
        $out = $this->testAction->execute();
        
        $dom = new \DOMDocument();
        $ret = $dom->loadXML($out);
        $this->assertTrue($ret, sprintf("response does not seems to be valid XML: [%s…]", substr($out, 0, 30)));
        
        $xp = new \DOMXpath($dom);
        $res = $xp->query('/status');
        $this->assertTrue((is_object($res) && ($res->length > 0)) , sprintf("response does not contains a status node."));
        
        $statusNode = $res->item(0);
        
        if (isset($data['expected:warning'])) {
            $warning = $statusNode->getAttribute('warning');
            $this->assertTrue(($warning == $data['expected:warning']) , sprintf("response contains non expected warning: found '%s' while expecting '%s'", $warning, $data['expected:warning']));
        }
        
        $cibles = $this->getAutocompletionCibles($xp, $statusNode);
        $this->assertTrue(is_array($cibles) , sprintf("Error getting <cible> nodes: %s", $this->errmsg));
        
        $options = $this->getAutocompletionOptions($xp, $statusNode);
        $this->assertTrue(is_array($options) , sprintf("Error getting <option> nodes: %s", $this->errmsg));
        
        if (isset($data['expected:results'])) {
            $results = $this->getArrayFromOptions($options);
            $err = $this->checkResult($data['expected:results'], $results);
            $this->assertEmpty($err, sprintf("Expected results not met: %s", $err));
        }
        
        if (isset($data['expected:cibles'])) {
            $err = $this->checkCibles($data['expected:cibles'], $cibles);
            $this->assertEmpty($err, sprintf("Expected cibles not met: %s", $err));
        }
    }
    
    public function getAutocompletionCibles(&$xp, &$statusNode)
    {
        $cibles = array();
        $cibleNodeList = $xp->query('cibles/cible', $statusNode);
        if ($cibleNodeList === false) {
            $this->errmsg = sprintf("XPath query error: 'cibles/cible'");
            return false;
        }
        foreach ($cibleNodeList as $cibleNode) {
            $cibles[] = $cibleNode->textContent;
        }
        return $cibles;
    }
    
    public function getAutocompletionOptions(&$xp, &$statusNode)
    {
        $options = array();
        $optionNodeList = $xp->query('option', $statusNode);
        if ($optionNodeList === false) {
            $this->errmsg = sprintf("XPath query error: 'option'");
            return false;
        }
        foreach ($optionNodeList as $i => $optionNode) {
            $options[$i] = array();
            $titleNodeList = $xp->query('title', $optionNode);
            $options[$i]['title'] = $titleNodeList->item(0)->textContent;
            $options[$i]['value'] = $optionNode->getAttribute('value');
            $options[$i]['values'] = array();
            
            $valNodeList = $xp->query('values/val', $optionNode);
            foreach ($valNodeList as $valNode) {
                if ($valNode->nodeName != 'val') {
                    continue;
                }
                $options[$i]['values'][] = $valNode->textContent;
            }
        }
        return $options;
    }
    
    public function getArrayFromOptions(&$options)
    {
        $res = array();
        foreach ($options as $i => & $option) {
            $line = array(
                $option['title']
            );
            foreach ($option['values'] as & $value) {
                $line[] = $value;
            }
            unset($value);
            $res[$i] = $line;
        }
        return $res;
    }
    
    public function checkResult(&$expectedResult, &$actualResult)
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
    
    public function checkCibles(&$expectedCibles, &$actualCibles)
    {
        $expectedCount = count($expectedCibles);
        $actualCount = count($actualCibles);
        if ($actualCount != $expectedCount) {
            return sprintf("Cible count mismatch: found '%s' while expecting '%s'", $actualCount, $expectedCount);
        }
        
        for ($i = 0; $i < $expectedCount; $i++) {
            $expected = $expectedCibles[$i];
            $actual = $actualCibles[$i];
            if ($actual != $expected) {
                return sprintf("Data mismatch at index #%s: found '%s' while expecting '%s'", $i, $actual, $expected);
            }
        }
        return '';
    }
    public function dataAutocompletion()
    {
        return array(
            array(
                array(
                    'fam' => 'TST_AUTOCOMPLETION',
                    'attr' => 'S_GRAVITE',
                    'expected:warning' => '',
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
                    'expected:cibles' => array(
                        's_gravite'
                    )
                )
            ) ,
            array(
                array(
                    'fam' => 'TST_AUTOCOMPLETION',
                    'attr' => 'S_TITLE_1',
                    'http:vars' => array(
                        '_s_title_1' => 'Relation'
                    ) ,
                    'expected:warning' => '',
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
                    'expected:cibles' => array(
                        's_title_1'
                    )
                )
            ) ,
            array(
                array(
                    'fam' => 'TST_AUTOCOMPLETION',
                    'attr' => 'S_TITLE_2',
                    'http:vars' => array(
                        '_s_title_2' => 'Relation'
                    ) ,
                    'expected:warning' => '',
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
                    'expected:cibles' => array(
                        's_title_2'
                    )
                )
            ) ,
            array(
                array(
                    'fam' => 'TST_AUTOCOMPLETION',
                    'attr' => 'BUG_2108',
                    'http:vars' => array(
                        '_s_text' => 'Bug 2108',
                        '_ilink_bug_2108' => 'Current Title'
                    ) ,
                    'expected:warning' => '',
                    'expected:results' => array(
                        array(
                            'Current Title, Bug 2108',
                            'Current Title, Bug 2108'
                        )
                    ) ,
                    'expected:cibles' => array(
                        'ilink_bug_2108',
                        'bug_2108'
                    )
                )
            ) ,
            array(
                array(
                    'fam' => 'TST_AUTOCOMPLETION',
                    'attr' => 'BUG_2108_OK',
                    'http:vars' => array(
                        '_s_text' => 'Test Bug 2108 OK',
                        '_ilink_bug_2108_ok' => 'Current Title'
                    ) ,
                    'expected:warning' => '',
                    'expected:results' => array(
                        array(
                            'Test Bug 2108 OK, Current Title',
                            'Test Bug 2108 OK, Current Title'
                        )
                    ) ,
                    'expected_cibles' => array(
                        'bug_2108_ok',
                        'ilink_bug_2108_ok'
                    )
                )
            ) ,
            array(
                array(
                    'fam' => 'TST_AUTOCOMPLETION',
                    'document' => 'TST_AUTOCOMPLETION_2',
                    'attr' => 'RELATION_2',
                    'http:vars' => array(
                        '_s_text' => 'Test Relation 2',
                        '_ilink_relation_1' => 'Document test',
                        '_ilink_relation_2' => 'Current Title'
                    ) ,
                    'expected:warning' => '',
                    'expected:results' => array(
                        array(
                            'Current Title, Document test, Test Relation 2',
                            'Current Title, Document test, Test Relation 2'
                        )
                    ) ,
                    'expected:cibles' => array(
                        'ilink_relation_1',
                        'relation_2',
                        'ilink_relation_2'
                    )
                )
            ) ,
            array(
                array(
                    'fam' => 'TST_AUTOCOMPLETION',
                    'document' => 'TST_AUTOCOMPLETION_3',
                    'attr' => 'RELATION_3',
                    'http:vars' => array(
                        '_s_text' => 'Test Relation 3',
                        '_ilink_relation_1' => 'Document test',
                        '_ilink_relation_3' => 'Current Title'
                    ) ,
                    'expected:warning' => '',
                    'expected:results' => array(
                        array(
                            'Test Relation 3, Current Title, Document test',
                            'Test Relation 3, Current Title, Document test'
                        )
                    ) ,
                    'expected:cibles' => array(
                        'ilink_relation_3',
                        'relation_3',
                        'ilink_relation_1'
                    )
                )
            )
        );
    }
}
?>