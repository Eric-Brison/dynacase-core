<?php
namespace PU;

/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package DCP
 */

require_once 'PU_testcase_dcp_document.php';



class TestOooLayout extends TestCaseDcpDocument
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
        self::$outputDir = uniqid(getTmpDir() . "/oootest-");
        mkdir(self::$outputDir);
        self::connectUser();
        self::beginTransaction();
        
        self::importDocument("PU_data_dcp_oooLayout.ods");
    }
    public static function tearDownAfterClass()
    {
       self::rollbackTransaction();
        
        //$err = simpleQuery(self::$dbaccess, "commit", $r);
        printf("\nResults for %s in file://%s\n", __CLASS__, self::$outputDir);
    }
    
    protected function saveFileResult($file, $name)
    {
        copy($file, self::$outputDir . '/' . $name);
    }
    /**
     * @dataProvider dataSimpleLayout
     */
    public function testSimpleLayout($family, $instances, $lname, $templates)
    {
        
        //$this->importDocument($family);
        $df = new_doc(self::$dbaccess, "TST_OOOLAYOUT");
        $this->assertTrue($df->isAlive(), "family TST_OOOLAYOUT is not alive");
        $this->importDocument($instances);
        
        $instance = new_doc(self::$dbaccess, $lname);
        $this->assertTrue($instance->isAlive(), "document $lname is not alive");
        foreach ( $templates as $template ) {
            $file = $instance->viewDoc("DCPTEST:" . $template . ":B");
            $this->assertTrue(file_exists($file), "fail layout $template");
            // print "ooffice $file\n";
            $this->saveFileResult($file, sprintf("%s_%s.odt", str_replace('.odt', '', $template), $lname));
        }
    
     //print_r($instance->getValues());
    }
    
    public function dataSimpleLayout()
    {
        return array(
            array(
                "PU_data_dcp_oooLayout.ods",
                "PU_dcp_data_simpleOooLayout.xml",
                "TST_SIMPLEODT",
                array(
                    "PU_dcp_data_simpleOooLayout.odt",
                    "PU_dcp_data_puceOooLayout.odt",
                    "PU_dcp_data_repeatMulti.odt",
                    "PU_dcp_data_repeatOooLayout.odt",
                    "PU_dcp_data_rowOooLayout.odt",
                    "PU_dcp_data_ifOooLayout.odt"
                )
            ),

            array(
                "PU_data_dcp_oooLayout.ods",
                "PU_dcp_data_multipleOooLayout.xml",
                "TST_MULTIPLEODT",
                array(
                    "PU_dcp_data_multipleOooLayout.odt"
                )
            ),
            array(
                "PU_data_dcp_oooLayout.ods",
                "PU_dcp_data_simpleOooLayout.xml",
                "TST_TABLEODT",
                array(
                    "PU_dcp_data_simpleOooLayout.odt",
                    "PU_dcp_data_puceOooLayout.odt"
                )
            ),
            array(
                "PU_data_dcp_oooLayout.ods",
                "PU_dcp_data_simpleOooLayout.xml",
                "TST_PUCEODT",
                array(
                    "PU_dcp_data_simpleOooLayout.odt",
                    "PU_dcp_data_puceOooLayout.odt"
                )
            )
        );
    }

}
?>