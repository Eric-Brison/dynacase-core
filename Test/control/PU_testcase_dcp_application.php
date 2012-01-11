<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package DCP
*/

namespace PU;

require_once 'PU_testcase_dcp.php';

class TestCaseDcpApplication extends TestCaseDcp
{
    protected static $app;
    
    protected function tearDown()
    {
        if (!self::$odb) {
            self::$odb = new \DbObj(self::$dbaccess);
        }
        self::$odb->rollbackPoint('testunit');
    }
    
    protected function setUp()
    {
        self::$odb->savePoint('testunit');
    }
    
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        
        self::connectUser();
        self::beginTransaction();
        
        $config = static ::appConfig();
        if (!is_array($config)) {
            throw new \Exception(sprintf("::appConfig() did not returned an array (returned type is %s).", gettype($config)));
        }
        if (!isset($config['appRoot']) || !isset($config['appName'])) {
            throw new \Exception(sprintf("Missing 'appRoot' or 'appName'."));
        }

        self::setIncludePath(ini_get('include_path') . ':' . $config['appRoot']);

        self::setUpTestApplication($config['appRoot'], $config['appName']);
        
        if (isset($config['import'])) {
            if (is_scalar($config['import'])) {
                $config['import'] = array(
                    $config['import']
                );
            }
            if (is_array($config['import'])) {
                foreach ($config['import'] as $import) {
                    self::importDocument($import);
                }
            }
        }
    }
    
    public static function tearDownAfterClass()
    {
        self::rollbackTransaction();
        self::resetIncludePath();
    }
    /**
     * Set up a false Action object can be used to execute action
     *
     * @param string $appRoot root path to application folder
     * @param string $appName application name
     *
     * @return void
     */
    protected static function setUpTestApplication($appRoot, $appName)
    {
        if (!is_dir($appRoot)) {
            throw new \Exception(sprintf("appRoot '%s' is not a valid directory.", $appRoot));
        }
        
        $fileDotApp = join(DIRECTORY_SEPARATOR, array(
            $appRoot,
            $appName,
            sprintf("%s.app", $appName)
        ));
        if (!is_file($fileDotApp)) {
            throw new \Exception(sprintf(".app file '%s' not found.", $fileDotApp));
        }
        
        $myAction = self::getAction();
        $myAction->parent->setVolatileParam('CORE_PUBDIR', $appRoot);
        
        self::$app = new \Application();
        self::$app->param = new \Param(self::$dbaccess);
        self::$app->parent = $myAction->parent;
        self::$app->set($appName, $myAction->parent, $myAction->parent->session, true);
        
        if (self::$app->id <= 0) {
            throw new \Exception(sprintf("Error initializing application from '%s'.", $fileDotApp));
        }
        
        $myAction->parent->setVolatileParam('CORE_PUBDIR', DEFAULT_PUBDIR);
    }
}
?>