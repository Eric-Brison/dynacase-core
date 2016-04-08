<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp\Pu;
/**
 * @author Anakeen
 * @package Dcp\Pu
 */

require_once 'PU_testcase_dcp.php';

class TestMultipleAlive extends TestCaseDcp
{
    /**
     * @param string $a
     * @return \Doc
     */
    public function testFixed()
    {
        $d = createDoc(self::$dbaccess, "BASE");
        $d->setValue("ba_title", "Initial");
        $this->assertTrue(is_object($d) , sprintf("cannot create BASE document"));
        $err = $d->add();
        $this->assertEmpty($err, sprintf("add error : $err"));
        $id0 = $d->id;
        
        $err = $d->revise();
        $id1 = $d->id;
        $this->assertEmpty($err, sprintf("revision rev.1 error : $err"));
        
        clearCacheDoc();
        
        $nd0 = new_doc(self::$dbaccess, $id0);
        $nd0->setValue("ba_title", "a");
        $err = $nd0->store(); // cannot modify fixed document
        $this->assertContains("DOC0118", $err, sprintf("modify rev.1 error : $err"));
        
        $nd1 = new_doc(self::$dbaccess, $id1);
        $nd1->setValue("ba_title", "a");
        $err = $nd1->store(); // can modify alive document
        $this->assertEmpty($err, sprintf("modify rev.1 error : $err"));
        
        $err = $d->revise(); // $nd1 is fixed now
        $this->assertEmpty($err, sprintf("revision rev.2 error : $err"));
        $id2 = $d->id;
        
        $nd1->setValue("ba_title", "b");
        $err = $nd1->store(); // cannot modify fixed document
        $this->assertContains("DOC0118", $err, sprintf("modify rev.1 error : $err"));
        
        error_log($err);
        // corrupt integraty => $nd1 becomes alives
        $nd1->locked = 0;
        $err = $nd1->modify(true, array(
            "locked"
        ) , true);
        $this->assertEmpty($err, sprintf("modify lock rev.1 error : $err"));
        // two documents are alive now $nd1 /$nd2
        clearCacheDoc();
        simpleQuery(self::$dbaccess, sprintf("select id, title, revision, locked from only doc%d where initid=%d  order by id", $nd1->fromid, $nd1->initid) , $r);
        
        $nd1 = new_doc(self::$dbaccess, $id1);
        $this->assertTrue($nd1->isAlive() , "nd1 is not alive");
        $nd1->setValue("ba_title", "c");
        $err = $nd1->store(); // cannot modify fixed document
        error_log($err);
        $this->assertContains("DOC0119", $err, sprintf("modify rev.1 error : $err"));
        
        $nd2 = new_doc(self::$dbaccess, $id2);
        $nd2->setValue("ba_title", "d");
        $err = $nd2->store(); // can modify clean document
        $this->assertEmpty($err, sprintf("modify rev.2 error : $err"));
        // corrupt integraty => $nd0 becomes alives
        $nd0->locked = 0;
        $err = $nd0->modify(true, array(
            "locked"
        ) , true);
        $this->assertEmpty($err, sprintf("modify lock rev.0 error : $err"));
        // corrupt again integraty => $nd1 becomes alives
        $nd1->locked = 0;
        $err = $nd1->modify(true, array(
            "locked"
        ) , true);
        $this->assertEmpty($err, sprintf("modify lock rev.1 error : $err"));
        
        clearCacheDoc();
        
        $nd2 = new_doc(self::$dbaccess, $id2);
        $this->assertTrue($nd1->isAlive() , "nd2 is not alive");
        $nd2->setValue("ba_title", "e");
        $err = $nd2->store(); // clean and modify can modify clean document
        $this->assertEmpty($err, sprintf("modify rev.1 error : $err"));
        
        return $d;
    }
}
?>