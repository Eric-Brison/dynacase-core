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

class TestDocument extends TestCaseDcpCommonFamily
{
    /**
     * import some documents
     * @static
     * @return string
     */
    protected static function getCommonImportFile()
    {
        return array(
            "PU_data_dcp_somebasicdoc.ods",
            "PU_data_dcp_fileattr.ods"
        );
    }
    /**
     * @dataProvider provider
     * @param string $a
     * @return \Doc
     */
    public function testAlive($a)
    {
        $d = new_doc(self::$dbaccess, $a);
        $this->assertTrue($d->isAlive() , sprintf("document %s not alive", $a));
        return $d;
    }
    /**
     * @dataProvider logicalName
     * @depends testAlive
     * @param string $file
     * @param array $ln
     */
    public function testLogicalName($file, array $ln)
    {
        $this->importDocument($file);
        
        foreach ($ln as $n) {
            $this->testAlive($n);
        }
    }
    /**
     * @dataProvider provider
     * @depends testAlive
     * @param string $a
     */
    public function testLock($a)
    {
        
        $d = new_doc(self::$dbaccess, $a, true);
        if ($d->isAlive()) {
            if ($d->canLock()) {
                $d->lock();
                $slock = $this->_DBGetValue(sprintf("select locked from docread where id=%d", $d->id));
                $slock = intval($slock);
                $this->assertEquals($d->userid, $slock, sprintf("document %d not locked", $a));
            } else {
                $this->markTestIncomplete(sprintf(_('Document %d is locked.') , $a));
            }
        } else {
            $this->markTestIncomplete(sprintf(_('Document %d not alive.') , $a));
        }
    }
    /**
     * @dataProvider provider
     * @depends testAlive
     * @param string $a
     */
    public function testunLock($a)
    {
        $d = new_doc(self::$dbaccess, $a, true);
        if ($d->isAlive()) {
            if ($d->canUnLock()) {
                $d->unlock();
                $slock = $this->_DBGetValue(sprintf("select locked from docread where id=%d", $d->id));
                $slock = intval($slock);
                $this->assertEquals(0, $slock, sprintf("document %d still locked", $a));
            } else {
                $this->markTestIncomplete(sprintf(_('Document %d is locked.') , $a));
            }
        } else {
            $this->markTestIncomplete(sprintf(_('Document %d not alive.') , $a));
        }
    }
    /**
     * @dataProvider provider
     * @depends testAlive
     * @param string $a
     */
    public function testautoLock($a)
    {
        
        $d = new_doc(self::$dbaccess, $a, true);
        if ($d->isAlive()) {
            if ($d->canLock()) {
                $d->lock(true);
                
                $slock = $this->_DBGetValue(sprintf("select locked from docread where id=%d", $d->id));
                $slock = intval($slock);
                if ($d->userid == 1) {
                    //$this->markTestIncomplete(sprintf(_('Admin cannot auto lock.')));
                    
                } else $this->assertEquals(-($d->userid) , ($slock) , sprintf("document %d not locked", $a));
            } else {
                $this->markTestIncomplete(sprintf(_('Document %d is locked.') , $a));
            }
        } else {
            $this->markTestIncomplete(sprintf(_('Document %d not alive.') , $a));
        }
    }
    /**
     * @dataProvider dataDelete
     * @param string $name
     * @param string $familyName
     */
    public function testDelete($name, $familyName)
    {
        if ($name) {
            $d = new_doc(self::$dbaccess, $name, true);
            if ($d->isAlive()) {
                $this->markTestIncomplete(sprintf(_('Document %s exists.') , $name));
            }
        }
        $nd = createDoc(self::$dbaccess, $familyName, false);
        if (!$nd) $this->assertFalse($nd, sprintf("cannot create document BASE"));
        $err = $nd->add();
        $this->assertEmpty($err, sprintf("error when create document BASE : %s", $err));
        $this->assertTrue(($nd->id > 0) , sprintf("no id when create document BASE"));
        if ($name) {
            $err = $nd->setLogicalIdentificator($name);
            $this->assertEmpty($err, sprintf("cannot set name %s : %s", $name, $err));
        }
        $err = $nd->delete();
        $this->assertEmpty($err, sprintf("error when delete document BASE : %s", $err));
        $slock = $this->_DBGetValue(sprintf("select locked from docread where id=%d", $nd->id));
        $this->assertEquals(-1, $slock, sprintf("document %s not locked fix", $name));
        $sdoctype = $this->_DBGetValue(sprintf("select doctype from docread where id=%d", $nd->id));
        $this->assertEquals('Z', $sdoctype, sprintf("document %s not deleted fix", $name));
        
        $err = $nd->revive();
        $this->assertEmpty($err, sprintf("error when revive document BASE : %s", $err));
        $slock = $this->_DBGetValue(sprintf("select locked from docread where id=%d", $nd->id));
        $this->assertGreaterThan(-1, $slock, sprintf("document %s locked fix", $name));
        $sdoctype = $this->_DBGetValue(sprintf("select doctype from docread where id=%d", $nd->id));
        $this->assertNotEquals('Z', $sdoctype, sprintf("document %s not revived fix", $name));
    }
    /**
     * @dataProvider dataDelete
     * @param string $name
     * @param string $familyName
     */
    public function testReallyDelete($name, $familyName)
    {
        if ($name) {
            $d = new_doc(self::$dbaccess, $name, true);
            if ($d->isAlive()) {
                $this->markTestIncomplete(sprintf(_('Document %s exists.') , $name));
            }
        }
        $nd = createDoc(self::$dbaccess, $familyName, false);
        if (!$nd) $this->assertFalse($nd, sprintf("cannot create document BASE"));
        $err = $nd->add();
        $this->assertEmpty($err, sprintf("error when create document BASE : %s", $err));
        
        $this->assertTrue(($nd->id > 0) , sprintf("no id when create document BASE"));
        if ($name) {
            $err = $nd->setLogicalIdentificator($name);
            $this->assertEmpty($err, sprintf("cannot set name %s : %s", $name, $err));
        }
        $err = $nd->delete(true);
        $this->assertEmpty($err, sprintf("error when delete document BASE : %s", $err));
        
        $sid = $this->_DBGetValue(sprintf("select id from docread where id=%d", $nd->id));
        $this->assertFalse($sid, sprintf("document %s not really deleted (docread)", $nd->id));
        if ($name) {
            $sid = $this->_DBGetValue(sprintf("select id from docname where name='%s'", $name));
            $this->assertFalse($sid, sprintf("document %s not really deleted (docname)", $name));
        }
        $sid = $this->_DBGetValue(sprintf("select id from docfrom where id='%s'", $nd->id));
        $this->assertFalse($sid, sprintf("document %s not really deleted (docfrom)", $nd->id));
        
        $err = $nd->revive();
        $this->assertNotEmpty($err, sprintf("error when revive document BASE : %s", $err));
    }
    /**
     * @dataProvider dataStoreFile
     * @param string $docId
     * @param string $attrName
     * @param string $filePathName
     * @param string $fileName
     * @param int $index
     */
    public function testStoreFile($docId, $attrName, $filePathName, $fileName, $index = - 1)
    {
        $doc = new_doc(self::$dbaccess, $docId);
        $this->assertTrue($doc->isAlive() , sprintf("could not get document with id '%s'", $docId));
        
        $err = $doc->setFile($attrName, $filePathName, $fileName, $index);
        $this->assertEmpty($err, sprintf("storeFile(%s, %s, %s, %s) returned with error: %s", $attrName, $filePathName, $fileName, $index, $err));
        
        $value = $doc->getTValue($attrName, '', $index);
        $this->assertNotEmpty($value, sprintf("value of '%s' at index %s should not be empty", $attrName, $index));
    }
    /**
     * @dataProvider dataSaveFile
     * @param string $docId
     * @param string $attrName
     * @param string $filePathName
     * @param string $fileName
     * @param int $index
     */
    public function testSaveFile($docId, $attrName, $filePathName, $fileName = '', $index = - 1)
    {
        /**
         * @var \stream $fd
         */
        $fd = @fopen($filePathName, 'r');
        $this->assertFalse(($fd === false) , sprintf("error openging file '%s': %s", $filePathName, isset($php_errormsg) ? $php_errormsg : ''));
        
        $doc = new_doc(self::$dbaccess, $docId);
        $this->assertTrue($doc->isAlive() , sprintf("could not get document with id '%s'", $docId));
        
        $err = $doc->saveFile($attrName, $fd, $fileName, $index);
        $this->assertEmpty($err, sprintf("saveFile(%s, %s, %s, %s) returned with error: %s", $attrName, $filePathName, $fileName, $index, $err));
        
        $value = $doc->getTValue($attrName, '', $index);
        $this->assertNotEmpty($value, sprintf("value of '%s' at index %s should not be empty", $attrName, $index));
    }
    /**
     * @dataProvider dataSetFile
     * @param string $docId
     * @param string $attrName
     * @param string $filePathName
     * @param string $fileName
     * @param int $index
     */
    public function testSetFile($docId, $attrName, $filePathName, $fileName, $index = - 1)
    {
        $doc = new_doc(self::$dbaccess, $docId);
        $this->assertTrue($doc->isAlive() , sprintf("could not get document with id '%s'", $docId));
        
        $err = $doc->setFile($attrName, $filePathName, $fileName, $index);
        $this->assertEmpty($err, sprintf("setFile(%s, %s, %s, %s) returned with error: %s", $attrName, $filePathName, $fileName, $index, $err));
        
        $value = $doc->getTValue($attrName, '', $index);
        $this->assertNotEmpty($value, sprintf("value of '%s' at index %s should not be empty", $attrName, $index));
    }
    /**
     * @dataProvider dataVaultRegisterFile
     * @param string $docId
     * @param string $filename
     * @param string $ftitle
     * @param string $expectedFileName
     * @param string $expectSuccess
     */
    public function testVaultRegisterFile($docId, $filename, $ftitle, $expectedFileName, $expectSuccess)
    {
        $doc = new_doc(self::$dbaccess, $docId);
        $this->assertTrue($doc->isAlive() , sprintf("could not get document with id '%s'", $docId));
        
        $exception = '';
        $vid = '';
        $info = null;
        try {
            $vid = $doc->vaultRegisterFile($filename, $ftitle, $info);
        }
        catch(\Exception $e) {
            $exception = $e->getMessage();
        }
        if ($expectSuccess) {
            $this->assertEmpty($exception, sprintf("vaultRegisterFile thrown exception for file '%s': %s", $filename, $exception));
            $ret = preg_match('/^.*|\d+|.*$/', $vid);
            $this->assertTrue((($ret !== false) && ($ret > 0)) , sprintf("vaultRegisterFile returned a malformed VID '%s' for file '%s'", $vid, $filename));
            $this->assertTrue(($expectedFileName == $info->name) , sprintf("Stored file name '%s' does not match expected file name '%s'.", $info->name, $expectedFileName));
        } else {
            $this->assertNotEmpty($exception, sprintf("vaultRegisterFile did not thrown expected exception for file '%s'", $filename));
        }
    }
    public function provider()
    {
        return array(
            array(
                9
            ) ,
            array(
                11
            ) ,
            array(
                12
            ) ,
            array(
                'TST_FOLDER1',
                'TST_BASE1'
            )
        );
    }
    public function dataDelete()
    {
        return array(
            array(
                'TST_DELETE',
                "BASE"
            ) ,
            array(
                'TST_DELETE',
                "DIR"
            ) ,
            array(
                '',
                "DIR"
            )
        );
    }
    public function logicalName()
    {
        return array(
            array(
                "PU_data_dcp_logicalname.xml",
                array(
                    'TST_ONE',
                    'TST_TWO'
                )
            )
        );
    }
    public function dataStoreFile()
    {
        return array(
            array(
                "TST_STOREFILE_1",
                "tst_single_file",
                "DCPTEST/Layout/tst_file.odt",
                "tst_file_single_file.odt",
                "-1"
            ) ,
            array(
                "TST_STOREFILE_1",
                "tst_array_file",
                "DCPTEST/Layout/tst_file.odt",
                "tst_file_array_file.odt",
                "3"
            ) ,
            array(
                "TST_STOREFILE_1",
                "tst_multiple_file",
                "DCPTEST/Layout/tst_file.odt",
                "tst_file_multiple_file.odt",
                "3"
            )
        );
    }
    public function dataSaveFile()
    {
        return array(
            array(
                "TST_SAVEFILE_1",
                "tst_single_file",
                "DCPTEST/Layout/tst_file.odt",
                "tst_file_single_file.odt",
                "-1"
            ) ,
            array(
                "TST_SAVEFILE_1",
                "tst_array_file",
                "DCPTEST/Layout/tst_file.odt",
                "tst_file_array_file.odt",
                "3"
            ) ,
            array(
                "TST_SAVEFILE_1",
                "tst_multiple_file",
                "DCPTEST/Layout/tst_file.odt",
                "tst_file_multiple_file.odt",
                "3"
            )
        );
    }
    public function dataSetFile()
    {
        return array(
            array(
                "TST_SETFILE_1",
                "tst_single_file",
                "DCPTEST/Layout/tst_file.odt",
                "tst_file_single_file.odt",
                "-1"
            ) ,
            array(
                "TST_SETFILE_1",
                "tst_array_file",
                "DCPTEST/Layout/tst_file.odt",
                "tst_file_array_file.odt",
                "3"
            ) ,
            array(
                "TST_STOREFILE_1",
                "tst_multiple_file",
                "DCPTEST/Layout/tst_file.odt",
                "tst_file_multiple_file.odt",
                "3"
            )
        );
    }
    public function dataVaultRegisterFile()
    {
        return array(
            array(
                "TST_VAULTREGISTERFILE_1",
                "DCPTEST/Layout/tst_file.odt",
                "",
                "tst_file.odt",
                true
            ) ,
            array(
                "TST_VAULTREGISTERFILE_1",
                "DCPTEST/Layout/tst_file.odt",
                "a new name.odt",
                "a new name.odt",
                true
            ) ,
            array(
                "TST_VAULTREGISTERFILE_1",
                "DCPTEST/Layout/this_file_does_not_exists.odt",
                "error",
                "error",
                false
            )
        );
    }
}
?>