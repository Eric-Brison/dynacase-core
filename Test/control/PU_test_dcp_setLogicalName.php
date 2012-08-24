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

class TestSetLogicalName extends TestCaseDcpCommonFamily
{
    /**
     * import TST_FAMSETVALUE family
     * @static
     * @return string
     */
    protected static function getCommonImportFile()
    {
        return "";
    }
    /**
     * @dataProvider dataSetLogicalName
     * @param string $oldname
     * @param string $newname
     */
    public function testExecuteSetLogicalName($oldname, $newname)
    {
        $doc = createDoc(self::$dbaccess, "BASE");
        $err = $doc->Add();
        $this->assertEmpty($err, sprintf("Error when creating document %s", $err));
        $this->assertTrue($doc->isAlive() , sprintf("document %s not alive", $doc->id));
        
        $err = $doc->setLogicalName($oldname);
        $this->assertEmpty($err, sprintf("Error when setting logical name %s for document %s : %s", $oldname, $doc->id, $err));
        clearCacheDoc();
        
        $new_doc = new_Doc(self::$dbaccess, $oldname);
        $this->assertTrue($new_doc->isAlive() , sprintf("document %s not alive", $oldname));
        
        $err = $doc->setLogicalName($newname, true);
        $this->assertEmpty($err, sprintf("Error when setting logical name %s for document %s : %s", $newname, $oldname, $err));
        clearCacheDoc();
        
        $new_doc = new_Doc(self::$dbaccess, $newname);
        $this->assertTrue($new_doc->isAlive() , sprintf("document %s not alive", $newname));
        $this->assertEquals($doc->id, $new_doc->id, sprintf("New logical name is not set to document"));
    }
    
    public function dataSetLogicalName()
    {
        return array(
            array(
                'TST_TEST_ONE',
                'TST_NEW_TEST_ONE'
            )
        );
    }
}
