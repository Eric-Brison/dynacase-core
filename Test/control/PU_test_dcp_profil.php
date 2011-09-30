<?php

namespace PU;
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package DCP
 */

require_once 'PU_testcase_dcp_document.php';

class TestProfil extends TestCaseDcpDocument
{
    protected static $outputDir;
    
    protected function tearDown()
    {
    }
    
    protected function setUp()
    {
    
    }
    
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        
        self::connectUser();
        self::beginTransaction();
        
        self::importDocument("PU_data_dcp_profil_family.ods");
    }
    public static function tearDownAfterClass()
    {
        self::rollbackTransaction();
    
    }
    
    /**
     * @dataProvider dataProfilComputing
     */
    public function testProfilComputing($docName, $prfName, $login, $aclAttendees)
    {
        static $first = true;
        if ($first) {
            $this->importDocument("PU_data_dcp_profildata.ods");
            $first = false;
        }
        $this->sudo($login);
        $this->resetDocumentCache();
        $df = new_doc(self::$dbaccess, $docName);
        $this->assertTrue($df->isAlive(), "document $docName is not alive");
        $df->setProfil($prfName);
        foreach ( $aclAttendees as $acl => $expect ) {
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
     * @---depends dataProfilComputing
     */
    public function testProfilDynamicChange($docName, $prfName, $login, $newPrfAcl, $aclAttendees)
    {
        static $first = true;
        if ($first) {
            $this->importDocument("PU_data_dcp_profildata.ods");
            $first = false;
        }
        $df = new_doc(self::$dbaccess, $docName);
        $this->assertTrue($df->isAlive(), "document $docName is not alive");
        $df->setProfil($prfName);
        
        $this->sudo($login);
        
        $this->resetDocumentCache();
        $this->importDocument($newPrfAcl);
        $df = new_doc(self::$dbaccess, $docName);
        
        
      
        foreach ( $aclAttendees as $acl => $expect ) {
            $result = ($df->Control($acl) == "");
            if ($expect) {
                $this->assertTrue($result, "acl $acl is not granted");
            } else {
                $this->assertFalse($result, "acl $acl is  granted");
            }
        }
        
    
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
            ),
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
            ),
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
            ),
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
            ),
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
            ),
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
            ),
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
            ),
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
            ),
            
            array(
                
                "TST_DOCPRFBASIC",
                "TST_PRFJOHN",
                "john",
                array(
                    "view" => true,
                    "edit" => true,
                    "delete" => false
                )
            ),
            array(
                
                "TST_DOCPRFBASIC",
                "TST_PRFJANE",
                "john",
                array(
                    "view" => true,
                    "edit" => false,
                    "delete" => false
                )
            ),
            array(
                
                "TST_DOCPRFBASIC",
                "TST_PRFJANE",
                "jane",
                array(
                    "view" => true,
                    "edit" => true,
                    "delete" => false
                )
            ),
            array(
                
                "TST_DOCPRFUSERJANE",
                "TST_PRFDYNUSER",
                "jane",
                array(
                    "view" => true,
                    "edit" => true,
                    "delete" => false
                )
            ),
            array(
                
                "TST_DOCPRFUSERJANE",
                "TST_PRFDYNUSER",
                "john",
                array(
                    "view" => false,
                    "edit" => false,
                    "delete" => false
                )
            ),
            array(
                
                "TST_DOCPRFUSERJOHN",
                "TST_PRFDYNUSER",
                "jane",
                array(
                    "view" => false,
                    "edit" => false,
                    "delete" => false
                )
            ),
            array(
                
                "TST_DOCPRFUSERJOHN",
                "TST_PRFDYNUSER",
                "john",
                array(
                    "view" => true,
                    "edit" => true,
                    "delete" => false
                )
            ),
            array(
                "TST_DOCPRFUSERALL",
                "TST_PRFDYNGROUP",
                "jane",
                array(
                    "view" => true,
                    "edit" => true,
                    "delete" => false
                )
            ),
            array(
                "TST_DOCPRFUSERALL",
                "TST_PRFDYNGROUP",
                "john",
                array(
                    "view" => true,
                    "edit" => true,
                    "delete" => false
                )
            ),
            array(
                "TST_DOCPRFMGROUP",
                "TST_PRFDYNMGROUP",
                "jane",
                array(
                    "view" => true,
                    "edit" => false,
                    "delete" => true
                )
            ),
            array(
                "TST_DOCPRFMGROUP",
                "TST_PRFDYNMGROUP",
                "john",
                array(
                    "view" => true,
                    "edit" => false,
                    "delete" => true
                )
            ),
            array(
                "TST_DOCPRFMUSER",
                "TST_PRFDYNMUSER",
                "jane",
                array(
                    "view" => true,
                    "edit" => true,
                    "delete" => false
                )
            ),
            array(
                "TST_DOCPRFMUSER",
                "TST_PRFDYNMUSER",
                "john",
                array(
                    "view" => true,
                    "edit" => true,
                    "delete" => false
                )
            ),
            array(
                "TST_DOCPRFUSERS",
                "TST_PRFDYNUSERS",
                "jane",
                array(
                    "view" => true,
                    "edit" => true,
                    "delete" => false
                )
            ),
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
?>