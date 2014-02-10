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

class TestUpdateAttribute extends TestCaseDcpCommonFamily
{
    /**
     * import TST_UPDTATTR
     * @static
     * @return array|string
     */
    protected static function getCommonImportFile()
    {
        return array(
            "PU_data_dcp_updateattribute.ods"
        );
    }
    
    protected $famName = 'TST_UPDTATTR';
    /**
     * @dataProvider dataSetValue
     */
    public function testSetValue($attrid, $newValue, $expectedValue)
    {
        $s = new \SearchDoc(self::$dbaccess, $this->famName);
        $s->setObjectReturn();
        $s->addFilter("name ~ '^TST_DUPTATTR'");
        $dl = $s->search()->getDocumentList();
        $ua = new \UpdateAttribute();
        $ua->useCollection($dl);
        $ua->setValue($attrid, $newValue);
        
        $s->reset();
        $dl = $s->search()->getDocumentList();
        /**
         * @var \Doc $doc
         */
        foreach ($dl as $doc) {
            $this->assertEquals($expectedValue, $doc->getRawValue($attrid) , sprintf("doc %s [#%d] not correct value for %s attribute", $doc->title, $doc->id, $attrid));
        }
    }
    /**
     * @dataProvider dataSetValueWithRevision
     */
    public function testSetValueWithRevision($attrid, $newValue, $expectedValue, $unchangedCount, $changedCount)
    {
        $s = new \SearchDoc(self::$dbaccess, $this->famName);
        $s->setObjectReturn();
        $s->setSlice(20);
        $s->addFilter("name ~ '^TST_DUPTATTR'");
        $s->setOrder('initid');
        $dl = $s->search()->getDocumentList();
        $ua = new \UpdateAttribute();
        $ua->useCollection($dl);
        $ua->addRevision(true);
        $ua->setValue($attrid, $newValue);
        
        $s->reset();
        $dl = $s->search()->getDocumentList();
        
        $results = $ua->getResults();
        /**
         * @var \Doc $doc
         */
        foreach ($dl as $doc) {
            $this->assertEquals($expectedValue, $doc->getRawValue($attrid) , sprintf("doc %s [#%d] not correct value for %s attribute", $doc->title, $doc->id, $attrid));
            if ($results[$doc->initid]->changed) {
                $this->assertGreaterThan($doc->initid, $doc->id, "revision not done for %s (#%d)", $doc->title, $doc->id);
                $this->assertTrue($results[$doc->initid]->revised);
            }
        }
        $changed = $unchanged = 0;
        foreach ($results as $r) {
            if ($r->changed) {
                $changed++;
            } else $unchanged++;
        }
        $this->assertEquals($unchangedCount, $changed, sprintf("revised unchanged count mistmatch %s", print_r($results, true)));
        $this->assertEquals($changedCount, $unchanged, sprintf("revised changed count mistmatch %s", print_r($results, true)));
    }
    /**
     * @dataProvider dataSetValue
     */
    public function testSetValueWithHistory($attrid, $newValue, $expectedValue)
    {
        $s = new \SearchDoc(self::$dbaccess, $this->famName);
        $s->setObjectReturn();
        $s->addFilter("name ~ '^TST_DUPTATTR'");
        $s->setOrder('initid');
        $s->setSlice(20);
        //        $dl = $s->search();
        $dl = new \DocumentList($s);
        
        $ua = new \UpdateAttribute();
        $ua->useCollection($dl);
        $comment = "coucou";
        $ua->addHistoryComment($comment);
        $ua->setValue($attrid, $newValue);
        $ua->useTransaction();
        $s->reset();
        $dl = $s->search()->getDocumentList();
        /**
         * @var \Doc $doc
         */
        foreach ($dl as $doc) {
            $this->assertEquals($expectedValue, $doc->getRawValue($attrid) , sprintf("doc %s [#%d] not correct value for %s attribute", $doc->title, $doc->initid, $attrid));
            $histo = $doc->getHisto(false, "UPDATE");
            
            $this->assertEquals($comment, $histo[0]["comment"], sprintf("not good history #%d:%s", $doc->initid, print_r($histo, true)));
        }
    }
    /**
     * @dataProvider dataSetValueWithProfiling
     */
    public function testSetValueWithProfiling($attrid, $newValue, $login, $expectedCountNames)
    {
        $s = new \SearchDoc(self::$dbaccess, $this->famName);
        $s->setObjectReturn();
        $s->addFilter("name ~ '^TST_DUPTATTR'");
        $s->setOrder('initid');
        $dl = new \DocumentList($s);
        
        $ua = new \UpdateAttribute();
        $ua->useCollection($dl);
        $ua->useProfileUpdating(true);
        if ($newValue) $ua->setValue($attrid, $newValue);
        
        $this->sudo($login);
        $ns = new \SearchDoc(self::$dbaccess, $this->famName);
        $ns->setObjectReturn();
        $ns->addFilter("name ~ '^TST_DUPTATTR'");
        $ns->search();
        $c = $ns->count();
        
        $this->assertEquals($expectedCountNames, $c, "not the good count for view docs" . print_r($ns->getSearchInfo() , true));
        $this->exitSudo();
    }
    /**
     * @dataProvider dataReplaceValue
     */
    public function testReplaceValue($attrid, $oldvalue, $newValue, $expectedChangedCount)
    {
        $fam = new_doc(self::$dbaccess, $this->famName);
        $oattr = $fam->getAttribute($attrid);
        $ws = new \SearchDoc(self::$dbaccess, $this->famName);
        $ws->setObjectReturn();
        $ws->addFilter("name ~ '^TST_DUPTATTR'");
        if ($oattr->isMultiple()) {
            
            $ws->addFilter("'%s' = any (%s)", $oldvalue, $attrid);
        } else {
            $ws->addFilter("'%s' = %s", $oldvalue, $attrid);
        }
        $wdl = $ws->search()->getDocumentList();
        $needChanged = array();
        foreach ($wdl as $id => $doc) {
            $needChanged[] = $doc->initid;
        }
        $ws = new \SearchDoc(self::$dbaccess, $this->famName);
        $ws->setObjectReturn();
        $ws->addFilter("name ~ '^TST_DUPTATTR'");
        
        if ($oattr->isMultiple()) {
            $ws->addFilter("'%s' = any (%s)", $newValue, $attrid);
        } else {
            $ws->addFilter("'%s' = %s", $newValue, $attrid);
        }
        $wdl = $ws->search()->getDocumentList();
        $noNeedChanged = array();
        foreach ($wdl as $id => $doc) {
            $noNeedChanged[] = $doc->initid;
        }
        
        $s = new \SearchDoc(self::$dbaccess, $this->famName);
        $s->setObjectReturn();
        $s->addFilter("name ~ '^TST_DUPTATTR'");
        $dl = $s->search()->getDocumentList();
        $ua = new \UpdateAttribute();
        $ua->useCollection($dl);
        $ua->replaceValue($attrid, $oldvalue, $newValue);
        
        $s->reset();
        $dl = $s->search()->getDocumentList();
        /**
         * @var \Doc $doc
         */
        foreach ($dl as $doc) {
            $isIn = (preg_match("/\b$newValue\b/", $doc->getRawValue($attrid)) > 0);
            if (in_array($doc->initid, $needChanged)) {
                $this->assertTrue($isIn, sprintf("doc %s [#%d] not updated : value is \"%s\"  for %s attribute", $doc->title, $doc->initid, $doc->getRawValue($attrid) , $attrid));
            } else {
                if (!in_array($doc->initid, $noNeedChanged)) {
                    $this->assertFalse($isIn, sprintf("doc %s [#%d] must not be updated : value is \"%s\"  for %s attribute", $doc->title, $doc->initid, $doc->getRawValue($attrid) , $attrid));
                }
            }
        }
        $results = $ua->getResults();
        $changed = $unchanged = 0;
        foreach ($results as $r) {
            if ($r->changed) {
                $changed++;
            } else $unchanged++;
        }
        $this->assertEquals($expectedChangedCount, $changed, "not correct changed count");
    }
    /**
     * @dataProvider dataAddValue
     */
    public function testAddValue($attrid, $valueToAdd, $isUnique, $expectedChangedCount)
    {
        $fam = new_doc(self::$dbaccess, $this->famName);
        $oa = $fam->getAttribute($attrid);
        $expectedAdd = $valueToAdd;
        if (($oa->type == "docid") || ($oa->type == "account")) {
            if (is_array($valueToAdd)) {
                foreach ($valueToAdd as $aValue) {
                    $aValue = \UpdateAttribute::getInitIdFromName($aValue);
                    $expectedAdd[] = $aValue;
                }
            } else {
                if (!is_numeric($valueToAdd)) $expectedAdd = \UpdateAttribute::getInitIdFromName($valueToAdd);
            }
        }
        $noNeedChanged = array();
        if ($isUnique) {
            $ws = new \SearchDoc(self::$dbaccess, $this->famName);
            $ws->setObjectReturn();
            $ws->addFilter("name ~ '^TST_DUPTATTR'");
            if (!is_array($expectedAdd)) $ws->addFilter("'%s' = any (%s)", $expectedAdd, $attrid);
            $wdl = $ws->search()->getDocumentList();
            foreach ($wdl as $id => $doc) {
                $noNeedChanged[] = $doc->initid;
            }
        }
        $s = new \SearchDoc(self::$dbaccess, $this->famName);
        $s->setObjectReturn();
        $s->addFilter("name ~ '^TST_DUPTATTR'");
        $dl = $s->search()->getDocumentList();
        $ua = new \UpdateAttribute();
        $ua->useCollection($dl);
        $ua->addValue($attrid, $valueToAdd);
        
        $s->reset();
        $dl = $s->search()->getDocumentList();
        
        $singleMultiple = false;
        $doubleMultiple = false;
        
        if (!$oa) $this->markTestIncomplete("cannot find $attrid attribute");
        if ($oa->isMultipleInArray()) {
            $doubleMultiple = true;
        } elseif ($oa->isMultiple()) {
            $singleMultiple = true;
        }
        /**
         * @var \Doc $doc
         */
        foreach ($dl as $doc) {
            $tv = $doc->getMultipleRawValues($attrid);
            if ($singleMultiple) {
                if (!in_array($doc->initid, $noNeedChanged)) {
                    if (is_array($expectedAdd)) {
                        $this->assertEquals(end($expectedAdd) , end($tv) , sprintf("value \"%s\" not added : %s", print_r($valueToAdd, true) , print_r($tv, true)));
                    } else {
                        $this->assertEquals($expectedAdd, end($tv) , sprintf("value \"%s\" not added : %s", $valueToAdd, print_r($tv, true)));
                    }
                }
            }
        }
        $results = $ua->getResults();
        $changed = $unchanged = 0;
        foreach ($results as $r) {
            if ($r->changed) $changed++;
            else $unchanged++;
        }
        $this->assertEquals($expectedChangedCount, $changed, "not correct changed count");
    }
    /**
     * @dataProvider dataAddErrorValue
     */
    public function testAddErrorValue($attrid, $valueToAdd, $expectedError)
    {
        
        $s = new \SearchDoc(self::$dbaccess, $this->famName);
        $s->setObjectReturn();
        $s->addFilter("name ~ '^TST_DUPTATTR'");
        $dl = $s->search()->getDocumentList();
        $ua = new \UpdateAttribute();
        $ua->useCollection($dl);
        $statusFile = $ua->bgAddValue($attrid, $valueToAdd);
        $sua = new \UpdateAttributeStatus($statusFile);
        
        while (!$sua->isFinished()) {
            sleep(1);
        }
        $err = $sua->getError();
        $this->assertNotEmpty($err, "An error must be returned");
        $this->assertContains($expectedError, $err, "not expected error message");
    }
    /**
     * @dataProvider dataRemoveValue
     */
    public function testRemoveValue($attrid, $valueToRemove, $expectedChangedCount)
    {
        $fam = new_doc(self::$dbaccess, $this->famName);
        $oa = $fam->getAttribute($attrid);
        $expectedRemove = $valueToRemove;
        if (($oa->type == "docid") || ($oa->type == "account")) {
            if (is_array($valueToRemove)) {
                foreach ($valueToRemove as $aValue) {
                    $aValue = \UpdateAttribute::getInitIdFromName($aValue);
                    $expectedRemove[] = $aValue;
                }
            } else {
                if (!is_numeric($valueToRemove)) $expectedRemove = \UpdateAttribute::getInitIdFromName($valueToRemove);
            }
        }
        $noNeedChanged = array();
        $ws = new \SearchDoc(self::$dbaccess, $this->famName);
        $ws->setObjectReturn();
        $ws->addFilter("name ~ '^TST_DUPTATTR'");
        if (!is_array($expectedRemove)) {
            if ($oa->isMultiple()) {
                $ws->addFilter("'%s' = any(%s)", pg_escape_string($expectedRemove) , $attrid);
            } else {
                $ws->addFilter("'%s' = %s", pg_escape_string($expectedRemove) , $attrid);
            }
        }
        $wdl = $ws->search()->getDocumentList();
        foreach ($wdl as $id => $doc) {
            $noNeedChanged[] = $doc->initid;
        }
        
        $s = new \SearchDoc(self::$dbaccess, $this->famName);
        $s->setObjectReturn();
        $s->addFilter("name ~ '^TST_DUPTATTR'");
        $dl = $s->search()->getDocumentList();
        $ua = new \UpdateAttribute();
        $ua->useCollection($dl);
        $ua->removeValue($attrid, $valueToRemove);
        
        $s->reset();
        $dl = $s->search()->getDocumentList();
        
        $singleMultiple = false;
        $doubleMultiple = false;
        
        if (!$oa) $this->markTestIncomplete("cannot find $attrid attribute");
        if ($oa->isMultipleInArray()) {
            $doubleMultiple = true;
        } elseif ($oa->isMultiple()) {
            $singleMultiple = true;
        }
        /**
         * @var \Doc $doc
         */
        foreach ($dl as $doc) {
            $tv = $doc->getMultipleRawValues($attrid);
            if ($singleMultiple) {
                if (!in_array($doc->initid, $noNeedChanged)) {
                    if (is_array($expectedRemove)) {
                        foreach ($expectedRemove as $aExpect) {
                            $this->assertFalse(in_array($aExpect, $tv) , sprintf("value \"%s\" is not removed : %s", $aExpect, implode(',', $tv)));
                        }
                    } else {
                        
                        $this->assertFalse(in_array($expectedRemove, $tv) , sprintf("value \"%s\" is not removed : %s", $valueToRemove, implode(',', $tv)));
                    }
                }
            }
        }
        $results = $ua->getResults();
        $changed = $unchanged = 0;
        foreach ($results as $r) {
            if ($r->changed) {
                $changed++;
            } else $unchanged++;
        }
        $this->assertEquals($expectedChangedCount, $changed, "not correct changed count");
    }
    /**
     * @dataProvider dataErrorSetValue
     */
    public function testErrorSetValue($attrid, $newValue, $expectErrorCode)
    {
        $s = new \SearchDoc(self::$dbaccess, $this->famName);
        $s->setObjectReturn();
        $s->addFilter("name ~ '^TST_DUPTATTR'");
        $dl = $s->search()->getDocumentList();
        $ua = new \UpdateAttribute();
        try {
            $ua->useCollection($dl);
            $ua->setValue($attrid, $newValue);
        }
        catch(\Dcp\Upat\Exception $e) {
            $this->assertEquals($expectErrorCode, $e->getDcpCode() , "erroneous exception code");
            return;
        }
        $this->assertTrue(true, false, "an error must be catched");
    }
    /**
     * @dataProvider dataSetValue
     * cannot use it in bg mode cause postgresql transaction locking
     */
    private function _testBgSetValue($attrid, $newValue)
    {
        $s = new \SearchDoc(self::$dbaccess, $this->famName);
        $s->setObjectReturn();
        $s->setSlice(20);
        $s->setStart(5);
        //$dl = $s->search()->getDocumentList();
        $dl = new \DocumentList($s);
        $ua = new \UpdateAttribute();
        $ua->useCollection($dl);
        $statusFile = $ua->bgSetValue($attrid, $newValue);
        $sua = new \UpdateAttributeStatus($statusFile);
        error_log($statusFile);
        
        while (!$sua->isFinished()) {
            error_log($sua->getStatus());
            error_log($sua->getLastMessage());
            usleep(800000);
        }
        
        $s->reset();
        $dl = $s->search()->getDocumentList();
        /**
         * @var \Doc $doc
         */
        foreach ($dl as $doc) {
            $this->assertEquals($newValue, $doc->getRawValue($attrid) , sprintf("doc %s [#%d] not correct value for %s attribute", $doc->title, $doc->id, $attrid));
        }
    }
    
    public function dataSetValueWithProfiling()
    {
        return array(
            
            array(
                "TST_REDACTOR",
                "TST_UUPDTATTR2",
                "admin",
                28
            ) ,
            array(
                "TST_REDACTOR",
                "",
                "tstLoginUpdtU2",
                18
            ) ,
            array(
                "TST_REDACTOR",
                "TST_UUPDTATTR2",
                "tstLoginUpdtU2",
                28
            ) ,
            array(
                "TST_REDACTOR",
                "TST_UUPDTATTR1",
                "tstLoginUpdtU2",
                0
            )
        );
    }
    public function dataAddErrorValue()
    {
        
        return array(
            array(
                "TST_NOTHING",
                "3",
                "not found"
            )
        );
    }
    
    public function dataErrorSetValue()
    {
        
        return array(
            array(
                "TST_NOTHING",
                "3",
                "UPAT0004"
            )
        );
    }
    
    public function dataSetValue()
    {
        
        return array(
            array(
                "TST_ENUM",
                "3",
                "3"
            ) ,
            array(
                "TST_ENUMS",
                "3\n4\n6",
                "{3,4,6}"
            )
        );
    }
    
    public function dataRemoveValue()
    {
        
        return array(
            array(
                "TST_INTS",
                "3",
                3
            ) ,
            array(
                "TST_DOUBLES",
                "3.1",
                1
            ) ,
            array(
                "TST_DOUBLES",
                "334.64",
                9
            ) ,
            array(
                "TST_TEXTS",
                "Deux lignes",
                9
            ) ,
            array(
                "TST_RELMUL",
                "TST_DUPTATTR2",
                2
            ) ,
            array(
                "TST_RELMULS",
                "TST_DUPTATTR7",
                7
            ) ,
            array(
                "TST_INTS",
                array(
                    "4",
                    "5",
                    "10"
                ) ,
                13
            )
        );
    }
    public function dataAddValue()
    {
        
        return array(
            array(
                "TST_INTS",
                "3",
                false,
                28
            ) ,
            array(
                "TST_DOUBLES",
                "3.14159",
                false,
                28
            ) ,
            array(
                "TST_TEXTS",
                "C'est un test",
                false,
                28
            ) ,
            array(
                "TST_ENUMS2",
                "3",
                false,
                28
            ) ,
            array(
                "TST_ENUMS",
                "3",
                true,
                25
            ) ,
            array(
                "TST_RELMUL",
                "TST_DUPTATTR1",
                true,
                26
            ) ,
            array(
                "TST_RELLATESTS",
                array(
                    "TST_DUPTATTR2",
                    "TST_DUPTATTR3"
                ) ,
                false,
                28
            )
        );
    }
    public function dataReplaceValue()
    {
        
        return array(
            array(
                "TST_ENUM",
                "5",
                "10",
                2
            ) ,
            array(
                "TST_ENUMS",
                "3",
                "6",
                3
            ) ,
            array(
                "TST_ENUM",
                "1",
                "11",
                3
            ) ,
            
            array(
                "TST_INTS",
                "4",
                "115",
                3
            ) ,
            array(
                "TST_ENUMS",
                "1",
                "11",
                16
            ) ,
            array(
                "TST_TITLE",
                "Titre 3",
                "C'est le n°3",
                1
            )
        );
    }
    
    public function dataSetValueWithRevision()
    {
        
        return array(
            array(
                "TST_ENUM",
                "6",
                "6",
                18,
                2
            ) ,
            array(
                "TST_ENUMS",
                "1\n6",
                "{1,6}",
                20,
                0
            ) ,
            array(
                "TST_ENUMS",
                "{1,6}",
                "{1,6}",
                19,
                1
            )
        );
    }
}
?>