<?php

namespace PU;
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package DCP
 */

require_once 'PU_testcase_dcp_document.php';

class TestDocument extends TestCaseDcpDocument
{
    /**
     * @dataProvider provider
     */
    public function testAlive($a)
    {
        $d = new_doc(self::$dbaccess, $a);
        $this->assertTrue($d->isAlive(), sprintf("document %s not alive", $a));
        return $d;
    }

    /**
     * @dataProvider logicalName
     * @---depends testAlive
     */
    public function testLogicalName($file, $ln)
    {
        $this->importDocument($file);

        foreach ($ln as $n) {
            $this->testAlive($n);
        }
    }
    /**
     * @dataProvider provider
     * @---depends testAlive
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
                $this->markTestIncomplete(sprintf(_('Document %d is locked.'), $a));
            }
        } else {
            $this->markTestIncomplete(sprintf(_('Document %d not alive.'), $a));
        }
    }

    /**
     * @dataProvider provider
     * @---depends testAlive
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
                $this->markTestIncomplete(sprintf(_('Document %d is locked.'), $a));
            }
        } else {
            $this->markTestIncomplete(sprintf(_('Document %d not alive.'), $a));
        }
    }

    /**
     * @dataProvider provider
     * @---depends testAlive
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
                } else
                    $this->assertEquals(-($d->userid), ($slock), sprintf("document %d not locked", $a));
            } else {
                $this->markTestIncomplete(sprintf(_('Document %d is locked.'), $a));
            }
        } else {
            $this->markTestIncomplete(sprintf(_('Document %d not alive.'), $a));
        }
    }

    /**
     * @dataProvider dataDelete
     */
    public function testDelete($name, $familyName)
    {
        if ($name) {
            $d = new_doc(self::$dbaccess, $name, true);
            if ($d->isAlive()) {
                $this->markTestIncomplete(sprintf(_('Document %s exists.'), $name));
            }
        }
        $nd = createDoc(self::$dbaccess, $familyName, false);
        if (!$nd)
            $this->assertFalse($nd, sprintf("cannot create document BASE"));
        $err = $nd->add();
        $this->assertEmpty($err, sprintf("error when create document BASE : %s", $err));
        $this->assertTrue(($nd->id > 0), sprintf("no id when create document BASE"));
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
     */
    public function testReallyDelete($name, $familyName)
    {
        if ($name) {
            $d = new_doc(self::$dbaccess, $name, true);
            if ($d->isAlive()) {
                $this->markTestIncomplete(sprintf(_('Document %s exists.'), $name));
            }
        }
        $nd = createDoc(self::$dbaccess, $familyName, false);
        if (!$nd)
            $this->assertFalse($nd, sprintf("cannot create document BASE"));
        $err = $nd->add();
        $this->assertEmpty($err, sprintf("error when create document BASE : %s", $err));

        $this->assertTrue(($nd->id > 0), sprintf("no id when create document BASE"));
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
    public function provider()
    {
        return array(
            array(
                9
            ), array(
                10
            ), array(
                11
            ), array(
                12
            )
        );
    }
    public function dataDelete()
    {
        return array(
            array(
                'TST_DELETE', "BASE"
            ), array(
                'TST_DELETE', "DIR"
            ), array(
                '', "DIR"
            )
        );
    }

    public function logicalName()
    {
        return array(
            array(
                "PU_data_dcp_logicalname.xml", array(
                    'TST_ONE', 'TST_TWO'
                )
            )
        );
    }

    public function folderProvider()
    {
        return array(
            array(
                9
            ), array(
                10
            )
        );
    }
    public function searchProvider()
    {
        return array(
            array(
                11
            ), array(
                12
            ), array(
                13
            )
        );
    }
    public function twoFolderProvider()
    {
        return array(
            array(
                9, 10
            ), array(
                10, 11
            )
        );
    }
    public function threeFolderProvider()
    {
        return array(
            array(
                9, 10, 11
            ), array(
                9, 10, 12
            ), array(
                9, 10, 13
            )
        );
    }
}
?>