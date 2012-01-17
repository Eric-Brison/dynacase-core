<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package DCP
 */

namespace PU;

require_once 'PU_testcase_dcp_commonfamily.php';

class TestFolder extends TestCaseDcpCommonFamily
{
    /**
     * import some documents
     * @static
     * @return string
     */
    protected static function getCommonImportFile()
    {
        return "PU_data_dcp_somebasicdoc.ods";
    }
    /**
     * @dataProvider provider
     */
    public function testAlive($a)
    {
        $d = new_doc(self::$dbaccess, $a);
        $this->assertTrue($d->isAlive() , sprintf("document %d not alive", $a));
        return $d;
    }
    /**
     * test ba_desc attribute set
     * @dataProvider folderProvider
     * @---depends testAlive
     */
    public function testSetbadesc($a)
    {
        $d = new_doc(self::$dbaccess, $a, true);
        if ($d->isAlive()) {
            $val = "testing " . time();
            $err = $d->setValue("ba_desc", $val);
            $this->assertEquals("", $err, sprintf("cannot object update", $a));
            $this->assertEquals($val, $d->getValue("ba_desc") , sprintf("document not updated", $a));
            $err = $d->modify();
            $this->assertEquals("", $err, sprintf("cannot database update", $a));
            
            $sval = $this->_DBGetValue(sprintf("select ba_desc from doc2 where id=%d", $d->id));
            $this->assertEquals($val, $sval, sprintf("document %d not locked", $a));
        } else {
            $this->markTestIncomplete(sprintf(_('Document %d not alive.') , $a));
        }
    }
    /**
     * @dataProvider searchProvider
     * @---depends testAlive
     */
    public function testnotSetbadesc($a)
    {
        $d = new_doc(self::$dbaccess, $a, true);
        if ($d->isAlive()) {
            $val = "testing " . time();
            $err = $d->setValue("ba_desc", $val);
            $this->assertFalse($err == "", sprintf(_("cannot object update %s") , $a));
        } else {
            $this->markTestIncomplete(sprintf(_('Document %d not alive.') , $a));
        }
    }
    /**
     * insert $b in $a
     * @dataProvider twoFolderProvider
     * @---depends testAlive
     */
    public function testAddFile($a, $b)
    {
        /**
         * @var \Dir $da
         */
        $da = new_doc(self::$dbaccess, $a, true);
        $db = new_doc(self::$dbaccess, $b, true);
        if ($da->isAlive() && $db->isAlive()) {
            
            $err = $da->addFile($db->initid);
            if ($err == "") {
                $this->assertEquals("", $err, sprintf(_("error ::addFile %s %s") , $a, $err));
                $sval = $this->_DBGetValue(sprintf("select childid from fld where dirid=%d and childid=%d", $da->initid, $db->initid));
                
                $this->assertEquals($db->initid, $sval, sprintf("not inserted %s", $a));
            } else {
                $this->markTestIncomplete(sprintf(_('Cannot insert : %s.') , $err));
            }
        } else {
            $this->markTestIncomplete(sprintf(_('Document %d not alive.') , $a));
        }
    }
    /**
     * move $c from  $a to $b
     * @dataProvider threeFolderProvider
     * @---depends testAlive
     */
    public function testMoveDocument($a, $b, $c)
    {
        /**
         * @var $da \Dir
         */
        $da = new_doc(self::$dbaccess, $a, true);
        $db = new_doc(self::$dbaccess, $b, true);
        $dc = new_doc(self::$dbaccess, $c, true);
        
        if ($da->isAlive() && $db->isAlive() && $dc->isAlive()) {
            $sval = $this->_DBGetValue(sprintf("select childid from fld where dirid=%d and childid=%d", $da->initid, $dc->initid));
            //$err=simpleQuery(self::$dbaccess,sprintf("select childid from fld where dirid=%d and childid=%d",$da->initid,$dc->initid),$sval,true,true);
            //$this->assertEquals("",$err,sprintf("database select error",$a));
            if ($dc->initid != $sval) {
                $this->markTestSkipped(sprintf(_("not present %s in %s") , $c, $a));
            }
            $this->assertEquals($dc->initid, $sval, sprintf("not present %s in %s", $c, $a));
            $err = $da->moveDocument($dc->initid, $db->initid);
            if ($err == "") {
                $this->assertEquals("", $err, sprintf(_("error ::moveDocument %s %s") , $a, $err));
                $sval = $this->_DBGetValue(sprintf("select childid from fld where dirid=%d and childid=%d", $da->initid, $dc->initid));
                $this->assertFalse($sval, sprintf("not unlinked %s", $a));
                $sval = $this->_DBGetValue(sprintf("select childid from fld where dirid=%d and childid=%d", $db->initid, $dc->initid));
                $this->assertEquals($dc->initid, $sval, sprintf("not inserted %s", $a));
                $sval = $this->_DBGetValue(sprintf("select prelid from docread where initid=%d and locked != -1", $dc->initid));
                $this->assertEquals($db->initid, $sval, sprintf("primary relation not updated %s", $c));
            } else {
                $this->markTestIncomplete(sprintf(_('Cannot move : %s.') , $err));
            }
        } else {
            $this->markTestIncomplete(sprintf(_('One of these documents %s not alive.') , $a . "," . $b . ',' . $c));
        }
    }
    
    public function provider()
    {
        return array(
            array(
                'TST_FOLDER2'
            ) ,
            array(
                'TST_FOLDER1'
            ) ,
            array(
                11
            ) ,
            array(
                12
            )
        );
    }
    public function folderProvider()
    {
        return array(
            array(
                'TST_FOLDER2'
            ) ,
            array(
                'TST_FOLDER1'
            )
        );
    }
    public function searchProvider()
    {
        return array(
            array(
                11
            ) ,
            array(
                12
            ) ,
            array(
                13
            )
        );
    }
    
    public function twoFolderProvider()
    {
        return array(
            array(
                'TST_FOLDER2',
                'TST_FOLDER1'
            ) ,
            array(
                'TST_FOLDER1',
                'TST_FOLDER2'
            )
        );
    }
    public function threeFolderProvider()
    {
        return array(
            array(
                'TST_FOLDER1',
                'TST_FOLDER2',
                'TST_FOLDER3'
            ) ,
            array(
                'TST_FOLDER2',
                'TST_FOLDER6',
                'TST_FOLDER4'
            ) ,
            array(
                'TST_FOLDER4',
                'TST_FOLDER5',
                'TST_FOLDER5'
            )
        );
    }
}
?>