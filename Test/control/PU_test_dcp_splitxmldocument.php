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

require_once 'PU_testcase_dcp.php';

include_once 'FREEDOM/freedom_import_xml.php';
/**
 * Test class for splitXmlDocument() function.
 */

class TestSplitXmlDocument extends TestCaseDcp
{
    static private $runid = 0;
    static private $workDir = false;
    public $errmsg = '';
    
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::createWorkDir();
    }
    
    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        $stat = stat(self::$workDir);
        if ($stat['nlink'] <= 2) {
            rmdir(self::$workDir);
        }
    }
    /**
     * @dataProvider dataSplitXmlDocument
     */
    public function testExecuteSplitXmlDocument($data)
    {
        self::$runid++;
        
        $testDir = self::$workDir . DIRECTORY_SEPARATOR . self::$runid;
        mkdir($testDir);
        
        $xml = $data['xml'];
        $produces = $data['produces'];
        $src = 'DCPTEST' . DIRECTORY_SEPARATOR . 'Layout' . DIRECTORY_SEPARATOR . $data['xml'];
        $workingXML = $testDir . DIRECTORY_SEPARATOR . $data['xml'];
        $ret = copy($src, $workingXML);
        if ($ret === false) {
            throw new \Exception(sprintf("Could not copy '%s' to '%s'.", $src, $workingXML));
        }
        if (isset($data['xml_alter'])) {
            $args = array();
            if (isset($data['xml_alter_args'])) {
                $args = $data['xml_alter_args'];
            }
            $ret = call_user_func(array(
                $this,
                $data['xml_alter']
            ) , $workingXML, $args);
            if ($ret === false) {
                throw new \Exception($this->errmsg);
            }
        }
        /* check splitXmlDocument() */
        $err = splitXmlDocument($workingXML, $testDir);
        if (isset($data['expect_error']) && $data['expect_error'] === true) {
            $this->assertNotEmpty($err, sprintf("splitXmlDocument did not returned with an expected error"));
            return;
        } else {
            $this->assertEmpty($err, sprintf("splitXmlDocument returned with '%s'", $err));
        }
        
        if (!isset($data['produces'])) {
            return;
        }
        /* check that the expected files are present */
        foreach ($data['produces'] as $file) {
            $file = $testDir . DIRECTORY_SEPARATOR . $file;
            $this->assertTrue(is_file($file) , sprintf("Required file '%s' has not been produced by splitXmlDocument.", $file));
        }
        /* check they are valid XML files */
        foreach ($data['produces'] as $file) {
            $file = $testDir . DIRECTORY_SEPARATOR . $file;
            $this->assertTrue($this->isValidXML($file) , sprintf("Output file '%s' does not seems to be a valid XML file according to xmllint.", $file));
        }
        
        $this->rm_Rf($testDir);
    }
    private function createWorkDir()
    {
        $tmpdir = getTmpDir();
        if (!is_dir($tmpdir)) {
            throw new \Exception(sprintf("Invalid directory '%s'.", $tmpdir));
        }
        $tmpname = tempnam($tmpdir, 'PU_TEST_DCP_SPLITXMLDOCUMENT');
        if ($tmpname === false) {
            throw new \Exception(sprintf("Could not create temporary file in '%s'.", $tmpdir));
        }
        unlink($tmpname);
        if (mkdir($tmpname, 0700) === false) {
            throw new \Exception(sprintf("Could not create directory '%s'.", $tmpname));
        }
        self::$workDir = $tmpname;
    }
    
    private function isValidXML($file)
    {
        $cmd = sprintf("xmllint --sax %s | grep -c '^SAX\\.error' > /dev/null 2>&1", escapeshellarg($file));
        $ret = 0;
        $out = system($cmd, $ret);
        if ($ret == 0) {
            /* If grep exit code is 0, it means it found a "SAX.error" line,
             * which means there are errors in the XML file
            */
            return false;
        }
        /* grep found no "SAX.error" lines */
        return true;
    }
    
    private function addBigNode($xml, $args = array())
    {
        $addNodeData = file_get_contents('DCPTEST' . DIRECTORY_SEPARATOR . 'Layout' . DIRECTORY_SEPARATOR . 'PU_data_dcp_splitxmldocument_bignode_template.xml');
        if ($addNodeData === false) {
            $this->errmsg = sprintf("Could not get content from XML file '%s'.", "PU_data_dcp_splitxmldocument_bignode_template.xml");
            return false;
        }
        $xmlData = file_get_contents($xml);
        if ($xmlData === false) {
            $this->errmsg = sprintf("Could not get content from XML file '%s'.", $xml);
            return false;
        }
        /*
         * expand @VARIABLES@ (1st pass)
        */
        $addNodeElmts = preg_split('/(@[a-zA-Z_][a-zA-Z0-9_]+@)/', $addNodeData, -1, PREG_SPLIT_DELIM_CAPTURE);
        foreach ($addNodeElmts as & $el) {
            $m = array();
            if (!preg_match('/^@(?<var>[a-zA-Z_][a-zA-Z0-9_]+)@$/', $el, $m)) {
                continue;
            }
            if ($m['var'] == 'DATA') {
                /* @DATA@ will be expanded in 2nd and final pass */
                continue;
            }
            if (!isset($args[$m['var']])) {
                continue;
            }
            $el = $args[$m['var']];
        }
        unset($el);
        /*
         * parse main XML document
        */
        $xmlElmts = array();
        if (!preg_match(':^(?<top>.*)(?<bottom></documents>\s*)$:ms', $xmlData, $xmlElmts)) {
            $this->errmsg = sprintf("Could not match XML document in XML file '%s'.", $xml);
            return false;
        }
        $fh = fopen($xml, 'w');
        if ($fh === false) {
            $this->errmsg = sprintf("Could not open XML file '%s' for writing.", $xml);
            return false;
        }
        /* write back the top part */
        $ret = fwrite($fh, $xmlElmts['top']);
        /*
         * expand @DATA@ (2nd pass)
        */
        foreach ($addNodeElmts as & $el) {
            $m = array();
            if (!preg_match('/^@DATA@$/', $el, $m)) {
                fwrite($fh, $el);
                continue;
            }
            /* Generate BASE64 data */
            $size = isset($args['size_in_MB']) ? $args['size_in_MB'] : 1;
            $size = $size * 1024 * 1024;
            $blockCount = floor($size / (3 * 1024));
            $oneBlock = str_repeat("QUFB", 1024);
            for ($i = 1; $i <= $blockCount; $i++) {
                fwrite($fh, $oneBlock);
            }
            $remBytes = $size - $blockCount * 3 * 1024;
            if ($remBytes == 1024) {
                fwrite($fh, str_repeat("QUFB", 341) . "QQ==");
            } elseif ($remBytes == 2048) {
                fwrite($fh, str_repeat("QUFB", 682) . "QUE=");
            }
        }
        unset($el);
        /* write back the bottom part */
        $ret = fwrite($fh, $xmlElmts['bottom']);
        fclose($fh);
        return $xml;
    }
    
    private function rm_Rf($dir)
    {
        $type = filetype($dir);
        if ($type != 'dir') {
            unlink($dir);
            return true;
        }
        $fh = opendir($dir);
        if ($fh === false) {
            return false;
        }
        while (($file = readdir($fh)) !== false) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            $file = $dir . DIRECTORY_SEPARATOR . $file;
            $this->rm_Rf($file);
        }
        rmdir($dir);
        return true;
    }
    
    public function dataSplitXmlDocument()
    {
        return array(
            array(
                array(
                    'description' => 'Small XML file',
                    'xml' => 'PU_data_dcp_splitxmldocument.xml',
                    'produces' => array(
                        'PU_DATA_DCP_SPLITXMLDOCUMENT_1.xml',
                        'PU_DATA_DCP_SPLITXMLDOCUMENT_2.xml',
                        'PU_DATA_DCP_SPLITXMLDOCUMENT_3.xml'
                    )
                )
            ) ,
            array(
                array(
                    'description' => 'Big XML file',
                    'xml' => 'PU_data_dcp_splitxmldocument.xml',
                    'xml_alter' => 'addBigNode',
                    'xml_alter_args' => array(
                        'NAME' => 'PU_DATA_DCP_SPLITXMLDOCUMENT_BIGNODE',
                        'TITLE' => 'big.bin',
                        'size_in_MB' => '100'
                    ) ,
                    'produces' => array(
                        'PU_DATA_DCP_SPLITXMLDOCUMENT_1.xml',
                        'PU_DATA_DCP_SPLITXMLDOCUMENT_2.xml',
                        'PU_DATA_DCP_SPLITXMLDOCUMENT_3.xml',
                        'PU_DATA_DCP_SPLITXMLDOCUMENT_BIGNODE.xml'
                    )
                )
            ) ,
            array(
                array(
                    'description' => 'Invalid root node',
                    'xml' => 'PU_data_dcp_splitxmldocument_invalid_root_node.xml',
                    'expect_error' => true
                )
            )
        );
    }
}
?>