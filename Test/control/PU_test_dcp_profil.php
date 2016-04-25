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

require_once 'PU_testcase_dcp_commonfamily.php';

class TestProfil extends TestCaseDcpCommonFamily
{
    protected static $outputDir;
    /**
     * import TST_FAMSETVALUE family
     * @static
     * @return string
     */
    protected static function getCommonImportFile()
    {
        return array(
            "PU_data_dcp_profil_family.ods",
            "PU_data_dcp_profildata.ods"
        );
    }
    /**
     * @dataProvider dataProfilComputing
     */
    public function testProfilComputing($docName, $prfName, $login, $aclAttendees)
    {
        
        $this->sudo($login);
        $this->resetDocumentCache();
        $df = \new_Doc(self::$dbaccess, $docName);
        $this->assertTrue($df->isAlive() , "document $docName is not alive");
        $df->setProfil($prfName);
        foreach ($aclAttendees as $acl => $expect) {
            $result = ($df->Control($acl) == "");
            if ($expect) {
                $this->assertTrue($result, "acl $acl is not granted");
            } else {
                $this->assertFalse($result, "acl $acl is  granted");
            }
        }
    }
    /**
     * @dataProvider dataProfilChange
     * @depends testProfilComputing
     */
    public function testProfilDynamicChange($docName, $prfName, $login, $newPrfAcl, $aclAttendees)
    {
        
        $df = \new_Doc(self::$dbaccess, $docName);
        $this->assertTrue($df->isAlive() , "document $docName is not alive");
        $df->setProfil($prfName);
        
        $this->importDocument($newPrfAcl);
        $this->sudo($login);
        
        $this->resetDocumentCache();
        $df = \new_Doc(self::$dbaccess, $docName);
        
        foreach ($aclAttendees as $acl => $expect) {
            $result = ($df->Control($acl) == "");
            if ($expect) {
                $this->assertTrue($result, "acl $acl is not granted");
            } else {
                $this->assertFalse($result, "acl $acl is  granted");
            }
        }
    }
    /**
     * @dataProvider dataProfilChange
     * @depends testProfilComputing
     */
    public function testSearchControlled($docName, $prfName, $login, $newPrfAcl, $aclAttendees)
    {
        
        $df = \new_Doc(self::$dbaccess, $docName);
        $this->assertTrue($df->isAlive() , "document $docName is not alive");
        $df->setProfil($prfName);
        
        $this->importDocument($newPrfAcl);
        $this->sudo($login);
        
        $this->resetDocumentCache();
        $df = \new_Doc(self::$dbaccess, $docName);
        
        foreach ($aclAttendees as $acl => $expect) {
            $result = ($df->Control($acl) == "");
            if ($expect) {
                $this->assertTrue($result, "acl $acl is not granted");
            } else {
                $this->assertFalse($result, "acl $acl is  granted");
            }
        }
    }
    /**
     * @dataProvider dataSearchDocument
     * @depends testProfilComputing
     */
    public function testSearchView($profFile, $famName, $login, $expectNumber)
    {
        
        $this->importDocument($profFile);
        $this->sudo($login);
        
        $this->resetDocumentCache();
        $s = new \SearchDoc(self::$dbaccess, $famName);
        $s->search();
        
        $this->assertEquals($expectNumber, $s->count() , sprintf("query:%s: %s", print_r($s->getSearchInfo() , true) , print_r($this->getViews($famName) , true)));
    }
    /**
     * @dataProvider dataProfilRecomputeProfiledDocuments
     */
    public function testProfilRecomputeProfiledDocuments($data)
    {
        $profile = \new_Doc('', $data['profile']);
        simpleQuery('', sprintf('DELETE FROM dochisto WHERE id = %d', $profile->initid));
        $this->importCsvData($data['import']);
        $this->resetDocumentCache();
        $profile = \new_Doc('', $data['profile']);
        $histo = $profile->getHisto();
        $hasRecomputedProfileedDocuments = false;
        foreach ($histo as $entry) {
            if ($entry['code'] == 'RECOMPUTE_PROFILED_DOCUMENT') {
                $hasRecomputedProfileedDocuments = true;
                break;
            }
        }
        $msg = sprintf("Import of profile '%s' has wrongfully %srecomputed profiled documents.", $data['profile'], $data['recomputed'] ? 'not ' : '');
        $this->assertEquals($data['recomputed'], $hasRecomputedProfileedDocuments, $msg);
    }
    public function dataProfilRecomputeProfiledDocuments()
    {
        return array(
            //
            // -- Static profiles --
            //
            // Check that RESET always recomputes profiled documents
            array(
                array(
                    "import" => "PROFIL;TST_PRF_RESET;;RESET;view=TST_GRPALL;edit=TST_GRPSUBONE",
                    "profile" => "TST_PRF_RESET",
                    "recomputed" => true
                )
            ) ,
            array(
                array(
                    "import" => "PROFIL;TST_PRF_RESET;;RESET;view=TST_GRPALL;delete=TST_GRPSUBONE",
                    "profile" => "TST_PRF_RESET",
                    "recomputed" => true
                )
            ) ,
            // Check that SET recomputes profiled documents only if changed
            array(
                array(
                    "import" => "PROFIL;TST_PRF_SET;;SET;view=TST_GRPALL;edit=TST_GRPSUBONE",
                    "profile" => "TST_PRF_SET",
                    "recomputed" => false
                )
            ) ,
            array(
                array(
                    "import" => "PROFIL;TST_PRF_SET;;SET;view=TST_GRPALL;delete=TST_GRPSUBONE",
                    "profile" => "TST_PRF_SET",
                    "recomputed" => true
                )
            ) ,
            //
            // -- Dynamic user profiles --
            //
            // Check that RESET always recomputes profiled documents
            array(
                array(
                    "import" => "PROFIL;TST_PRF_RESET_DYNUSER;;RESET;view=tst_user;edit=tst_user",
                    "profile" => "TST_PRF_RESET_DYNUSER",
                    "recomputed" => true
                )
            ) ,
            array(
                array(
                    "import" => "PROFIL;TST_PRF_RESET_DYNUSER;;RESET;view=tst_user;delete=tst_user",
                    "profile" => "TST_PRF_RESET_DYNUSER",
                    "recomputed" => true
                )
            ) ,
            // Check that SET recomputes profiled documents only if changed
            array(
                array(
                    "import" => "PROFIL;TST_PRF_SET_DYNUSER;;SET;view=tst_user;edit=tst_user",
                    "profile" => "TST_PRF_SET_DYNUSER",
                    "recomputed" => false
                )
            ) ,
            array(
                array(
                    "import" => "PROFIL;TST_PRF_SET_DYNUSER;;SET;view=tst_user;delete=tst_user",
                    "profile" => "TST_PRF_SET_DYNUSER",
                    "recomputed" => true
                )
            )
        );
    }
    private function getViews($famid)
    {
        $famid = getFamIdFromName(self::$dbaccess, $famid);
        simpleQuery(self::$dbaccess, "select id, name, profid, views from doc$famid", $r);
        return $r;
    }
    public function dataSearchDocument()
    {
        return array(
            
            array(
                "profil" => "PU_data_dcp_setprofildata.ods",
                "famName" => "TST_PROFIL",
                "admin",
                7
            ) ,
            array(
                "profil" => "PU_data_dcp_setprofildata.ods",
                "famName" => "TST_PROFIL",
                "john",
                6
            ) ,
            array(
                "profil" => "PU_data_dcp_setprofildata.ods",
                "famName" => "TST_PROFIL",
                "jane",
                6
            )
        );
    }
    public function dataProfilChange()
    {
        return array(
            array(
                "TST_DOCPRFBASIC",
                "TST_PRFJOHN",
                "john",
                "PU_data_dcp_profilJohn.ods",
                array(
                    "view" => true,
                    "edit" => false,
                    "delete" => true
                )
            ) ,
            array(
                "TST_DOCPRFBASIC",
                "TST_PRFJOHN_BIS",
                "john",
                "PU_data_dcp_profilJohn.ods",
                array(
                    "view" => true,
                    "edit" => false,
                    "delete" => true
                )
            ) ,
            array(
                "TST_DOCPRFBASIC",
                "TST_PRFJOHN",
                "jane",
                "PU_data_dcp_profilJohn.ods",
                array(
                    "view" => true,
                    "edit" => false,
                    "delete" => false
                )
            ) ,
            array(
                "TST_DOCPRFBASIC",
                "TST_PRFJOHN_BIS",
                "jane",
                "PU_data_dcp_profilJohn.ods",
                array(
                    "view" => true,
                    "edit" => false,
                    "delete" => false
                )
            ) ,
            array(
                "TST_DOCPRFUSERJANE",
                "TST_PRFDYNUSER",
                "jane",
                "PU_data_dcp_profilUser.ods",
                array(
                    "view" => true,
                    "edit" => false,
                    "delete" => true
                )
            ) ,
            array(
                "TST_DOCPRFUSERJANE",
                "TST_PRFDYNUSER",
                "john",
                "PU_data_dcp_profilUser.ods",
                array(
                    "view" => false,
                    "edit" => false,
                    "delete" => false
                )
            ) ,
            array(
                "TST_DOCPRFUSERJOHN",
                "TST_PRFDYNUSER",
                "john",
                "PU_data_dcp_profilUser.ods",
                array(
                    "view" => true,
                    "edit" => false,
                    "delete" => true
                )
            ) ,
            array(
                "TST_DOCPRFUSERJOHN",
                "TST_PRFDYNUSER",
                "jane",
                "PU_data_dcp_profilUser.ods",
                array(
                    "view" => false,
                    "edit" => false,
                    "delete" => false
                )
            ) ,
            array(
                "TST_DOCPRFUSERJOHN",
                "TST_PRFDYNUSER",
                "john",
                "PU_data_dcp_profilUserStatic.ods",
                array(
                    "view" => true,
                    "edit" => false,
                    "delete" => false
                )
            ) ,
            array(
                "TST_DOCPRFUSERJOHN",
                "TST_PRFDYNUSER",
                "jane",
                "PU_data_dcp_profilUserStatic.ods",
                array(
                    "view" => true,
                    "edit" => false,
                    "delete" => false
                )
            )
        );
    }
    
    public function dataProfilComputing()
    {
        return array(
            array(
                
                "TST_DOCPRFBASIC",
                "TST_PRFJOHN",
                "jane",
                array(
                    "view" => true,
                    "edit" => false,
                    "delete" => false
                )
            ) ,
            array(
                
                "TST_DOCPRFBASIC",
                "TST_PRFJOHN_BIS",
                "jane",
                array(
                    "view" => true,
                    "edit" => false,
                    "delete" => false
                )
            ) ,
            
            array(
                
                "TST_DOCPRFBASIC",
                "TST_PRFJOHN",
                "john",
                array(
                    "view" => true,
                    "edit" => true,
                    "delete" => false
                )
            ) ,
            array(
                
                "TST_DOCPRFBASIC",
                "TST_PRFJOHN_BIS",
                "john",
                array(
                    "view" => true,
                    "edit" => true,
                    "delete" => false
                )
            ) ,
            
            array(
                
                "TST_DOCPRFBASIC",
                "TST_PRFJANE",
                "john",
                array(
                    "view" => true,
                    "edit" => false,
                    "delete" => false
                )
            ) ,
            array(
                
                "TST_DOCPRFBASIC",
                "TST_PRFJANE_ACCOUNT",
                "john",
                array(
                    "view" => true,
                    "edit" => false,
                    "delete" => false
                )
            ) ,
            array(
                
                "TST_DOCPRFBASIC",
                "TST_PRFJANE_DOCUMENT",
                "john",
                array(
                    "view" => true,
                    "edit" => false,
                    "delete" => false
                )
            ) ,
            array(
                "TST_DOCPRFBASIC",
                "TST_PRFJANE_MIXED",
                "john",
                array(
                    "view" => true,
                    "edit" => false,
                    "delete" => false
                )
            ) ,
            array(
                "TST_DOCPRFBASIC",
                "TST_PRFJANE_MIXEDNOTHING",
                "john",
                array(
                    "view" => true,
                    "edit" => false,
                    "delete" => false
                )
            ) ,
            array(
                
                "TST_DOCPRFBASIC",
                "TST_PRFJANE",
                "jane",
                array(
                    "view" => true,
                    "edit" => true,
                    "delete" => false
                )
            ) ,
            array(
                "TST_DOCPRFUSERJANE",
                "TST_PRFDYNUSER",
                "jane",
                array(
                    "view" => true,
                    "edit" => true,
                    "delete" => false
                )
            ) ,
            array(
                "TST_DOCPRFUSERJANE",
                "TST_PRFDYNUSER_ATTR",
                "jane",
                array(
                    "view" => true,
                    "edit" => true,
                    "delete" => false,
                    "send" => true
                )
            ) ,
            array(
                "TST_DOCPRFUSERJANE",
                "TST_PRFDYNUSER_ACCOUNT",
                "jane",
                array(
                    "view" => true,
                    "edit" => true,
                    "delete" => false,
                    "send" => true,
                    "unlock" => false
                )
            ) ,
            array(
                "TST_DOCPRFUSERJANE",
                "TST_PRFDYNUSER_ACCOUNT",
                "john",
                array(
                    "view" => false,
                    "edit" => true,
                    "delete" => false,
                    "send" => true,
                    "unlock" => true
                )
            ) ,
            array(
                
                "TST_DOCPRFUSERJANE",
                "TST_PRFDYNUSER",
                "john",
                array(
                    "view" => false,
                    "edit" => false,
                    "delete" => false
                )
            ) ,
            array(
                
                "TST_DOCPRFUSERJOHN",
                "TST_PRFDYNUSER",
                "jane",
                array(
                    "view" => false,
                    "edit" => false,
                    "delete" => false
                )
            ) ,
            array(
                
                "TST_DOCPRFUSERJOHN",
                "TST_PRFDYNUSER",
                "john",
                array(
                    "view" => true,
                    "edit" => true,
                    "delete" => false
                )
            ) ,
            array(
                "TST_DOCPRFUSERALL",
                "TST_PRFDYNGROUP",
                "jane",
                array(
                    "view" => true,
                    "edit" => true,
                    "delete" => false
                )
            ) ,
            array(
                "TST_DOCPRFUSERALL",
                "TST_PRFDYNGROUP",
                "john",
                array(
                    "view" => true,
                    "edit" => true,
                    "delete" => false
                )
            ) ,
            array(
                "TST_DOCPRFMGROUP",
                "TST_PRFDYNMGROUP",
                "jane",
                array(
                    "view" => true,
                    "edit" => false,
                    "delete" => true
                )
            ) ,
            array(
                "TST_DOCPRFMGROUP",
                "TST_PRFDYNMGROUP",
                "john",
                array(
                    "view" => true,
                    "edit" => false,
                    "delete" => true
                )
            ) ,
            array(
                "TST_DOCPRFMUSER",
                "TST_PRFDYNMUSER",
                "jane",
                array(
                    "view" => true,
                    "edit" => true,
                    "delete" => false
                )
            ) ,
            array(
                "TST_DOCPRFMUSER",
                "TST_PRFDYNMUSER",
                "john",
                array(
                    "view" => true,
                    "edit" => true,
                    "delete" => false
                )
            ) ,
            array(
                "TST_DOCPRFUSERS",
                "TST_PRFDYNUSERS",
                "jane",
                array(
                    "view" => true,
                    "edit" => true,
                    "delete" => false
                )
            ) ,
            array(
                "TST_DOCPRFUSERS",
                "TST_PRFDYNUSERS",
                "john",
                array(
                    "view" => true,
                    "edit" => true,
                    "delete" => false
                )
            )
        );
    }
}
