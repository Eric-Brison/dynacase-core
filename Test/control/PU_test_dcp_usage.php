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

class TestUsage extends TestCaseDcp
{
    /**
     *
     * @dataProvider dataTextUsage
     *
     */
    public function testTextApiUsage($text)
    {
        $u = new \ApiUsage();
        $u->setText($text);
        
        $this->assertContains($text, $u->getUsage());
    }
    /**
     *
     * @dataProvider dataNeedUsage
     *
     */
    public function testNeededApiUsage($argNeeded, $def)
    {
        $usage = '';
        try {
            $u = new \ApiUsage();
            $u->addNeeded($argNeeded, $def);
            $u->verify();
        }
        catch(\Exception $e) {
            $usage = $e->getMessage();
        }
        $this->assertContains($argNeeded, $usage);
        $this->assertContains($def, $usage);
    }
    /**
     *
     * @dataProvider dataNeedUsage
     *
     */
    public function testNeededApiUsageForceException($argNeeded, $def)
    {
        $usage = '';
                $error = '';
        try {
            $u = new \ApiUsage();
            $u->addNeeded($argNeeded, $def);
            $u->verify(true);
        }
        catch(\ApiUsageException $e) {
            $error = $e->getMessage();
                        $usage = $e->getUsage();
        }
        $this->assertContains($argNeeded, $error);
        $this->assertNotContains($def, $error);
                $this->assertContains($argNeeded, $usage);
                $this->assertContains($def, $usage);
    }
    
    public function dataTextUsage()
    {
        return array(
            array(
                "hello world"
            )
        );
    }
    
    public function dataNeedUsage()
    {
        return array(
            array(
                "needAbsolut",
                "necessary"
            )
        );
    }
}
?>