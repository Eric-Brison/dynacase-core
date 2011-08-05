<?php

/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 */



require_once './PU_Framework_TestDocument.php';

class TestDocument extends PHPUnit_Framework_TestDocument
{
    /**
     * @dataProvider provider
     */
    public function testAlive($a)    {
        $d=new_doc($this->dbaccess,$a);
        $this->assertTrue($d->isAlive(),sprintf("document %d not alive",$a));
        return $d;
    }

    /**
     * @dataProvider provider
     * @---depends testAlive
     */
    public function testLock($a)    {

        $d=new_doc($this->dbaccess,$a,true);
        if ($d->isAlive()) {
            if ($d->canLock()) {
                $d->lock();
                $slock=$this->_DBGetValue(sprintf("select locked from docread where id=%d",$d->id));
                $slock=intval($slock);
                $this->assertEquals($d->userid,$slock,sprintf("document %d not locked",$a));
            }
            else {
                $this->markTestIncomplete(sprintf(_( 'Document %d is locked.'),$a  ) );
            }
        }else {
            $this->markTestIncomplete(sprintf(_( 'Document %d not alive.'),$a  ) );
        }
    }

    /**
     * @dataProvider provider
     * @---depends testAlive
     */
    public function testunLock($a)    {

        $d=new_doc($this->dbaccess,$a,true);
        if ($d->isAlive()) {
            if ($d->canUnLock()) {
                $d->unlock();
                $slock=$this->_DBGetValue(sprintf("select locked from docread where id=%d",$d->id));
                $slock=intval($slock);
                $this->assertEquals(0,$slock,sprintf("document %d still locked",$a));
            } else {
                $this->markTestIncomplete(sprintf(_( 'Document %d is locked.'),$a  ) );
            }
        }else {
            $this->markTestIncomplete(sprintf(_( 'Document %d not alive.'),$a  ) );
        }
    }

    /**
     * @dataProvider provider
     * @---depends testAlive
     */
    public function testautoLock($a)    {

        $d=new_doc($this->dbaccess,$a,true);
        if ($d->isAlive()) {
            if ($d->canLock()) {
                $d->lock(true);

                $slock=$this->_DBGetValue(sprintf("select locked from docread where id=%d",$d->id));
                $slock=intval($slock);
                if ($d->userid==1) $this->markTestIncomplete(sprintf(_( 'Admin cannot auto lock.') ) );
                else $this->assertEquals(-($d->userid),($slock),sprintf("document %d not locked",$a));
            } else {
                $this->markTestIncomplete(sprintf(_( 'Document %d is locked.'),$a  ) );
            }
        }else {
            $this->markTestIncomplete(sprintf(_( 'Document %d not alive.'),$a  ) );
        }
    }
    /**
     * @dataProvider folderProvider
     * @---depends testAlive
     */
    public function testSetbadesc($a)    {
        $d=new_doc($this->dbaccess,$a,true);
        if ($d->isAlive()) {
            $val="testing ".time();
            $err=$d->setValue("ba_desc",$val);
            $this->assertEquals("",$err,sprintf("cannot object update",$a));
            $this->assertEquals($val,$d->getValue("ba_desc"),sprintf("document not updated",$a));
            $err=$d->modify();
            $this->assertEquals("",$err,sprintf("cannot database update",$a));

            $sval=$this->_DBGetValue(sprintf("select ba_desc from doc2 where id=%d",$d->id));
            $this->assertEquals($val,$sval,sprintf("document %d not locked",$a));
        }else {
            $this->markTestIncomplete(sprintf(_( 'Document %d not alive.'),$a  ) );
        }
    }

    /**
     * @dataProvider searchProvider
     * @---depends testAlive
     */
    public function testnotSetbadesc($a)    {
        $d=new_doc($this->dbaccess,$a,true);
        if ($d->isAlive()) {
            $val="testing ".time();
            $err=$d->setValue("ba_desc",$val);
            $this->assertFalse($err=="",sprintf(_("cannot object update %s"),$a));
        }else {
            $this->markTestIncomplete(sprintf(_( 'Document %d not alive.'),$a  ) );
        }
    }

    /**
     * insert $b in $a
     * @dataProvider twoFolderProvider
     * @---depends testAlive
     */
    public function testAddFile($a,$b)    {
        $da=new_doc($this->dbaccess,$a,true);
        $db=new_doc($this->dbaccess,$b,true);
        if ($da->isAlive() && $db->isAlive()) {

            $err=$da->addFile($db->initid);
            if ($err=="") {
                $this->assertEquals("",$err,sprintf(_("error ::addFile %s %s"),$a,$err));
                $sval=$this->_DBGetValue(sprintf("select childid from fld where dirid=%d and childid=%d",$da->initid,$db->initid));

                $this->assertEquals($db->initid,$sval,sprintf("not inserted %s",$a));
            } else {
                $this->markTestIncomplete(sprintf(_( 'Cannot insert : %s.'),$err ) );
            }
        } else {
            $this->markTestIncomplete(sprintf(_( 'Document %d not alive.'),$a  ) );
        }
    }


    /**
     * move $c from  $a to $b
     * @dataProvider threeFolderProvider
     * @---depends testAlive
     */
    public function testMoveDocument($a,$b,$c)    {
        $da=new_doc($this->dbaccess,$a,true);
        $db=new_doc($this->dbaccess,$b,true);
        $dc=new_doc($this->dbaccess,$c,true);
        if ($da->isAlive() && $db->isAlive()&& $dc->isAlive()) {
            $sval=$this->_DBGetValue(sprintf("select childid from fld where dirid=%d and childid=%d",$da->initid,$dc->initid));
            //$err=simpleQuery($this->dbaccess,sprintf("select childid from fld where dirid=%d and childid=%d",$da->initid,$dc->initid),$sval,true,true);
            //$this->assertEquals("",$err,sprintf("database select error",$a));
            if ($dc->initid != $sval) {
                $this->markTestSkipped(sprintf(_("not present %s in %s"),$c,$a));
            }
            $this->assertEquals($dc->initid,$sval,sprintf("not present %s in %s",$c,$a));
            $err=$da->moveDocument($dc->initid,$db->initid);
            if ($err=="") {
                $this->assertEquals("",$err,sprintf(_("error ::moveDocument %s %s"),$a,$err));
                $sval=$this->_DBGetValue(sprintf("select childid from fld where dirid=%d and childid=%d",$da->initid,$dc->initid));
                $this->assertFalse($sval,sprintf("not unlinked %s",$a));
                $sval=$this->_DBGetValue(sprintf("select childid from fld where dirid=%d and childid=%d",$db->initid,$dc->initid));
                $this->assertEquals($dc->initid,$sval,sprintf("not inserted %s",$a));
                $sval=$this->_DBGetValue(sprintf("select prelid from docread where initid=%d and locked != -1",$dc->initid));
                $this->assertEquals($db->initid,$sval,sprintf("primary relation not updated %s",$c));
            } else {
                $this->markTestIncomplete(sprintf(_( 'Cannot move : %s.'),$err ) );
            }
        } else {
            $this->markTestIncomplete(sprintf(_( 'One of these documents %s not alive.'),$a.",".$b.','.$c  ) );
        }
    }



    public function provider()  {
        return array(array(9),array(10),array(11),array(12));
    }
    public function folderProvider()  {
        return array(array(9),array(10));
    }
    public function searchProvider()  {
        return array(array(11),array(12),array(13));
    }


    public function twoFolderProvider()  {
        return array(array(9,10),array(10,11));
    }
    public function threeFolderProvider()  {
        return array(array(9,10,11),array(9,10,12),array(9,10,13));
    }
}
?>