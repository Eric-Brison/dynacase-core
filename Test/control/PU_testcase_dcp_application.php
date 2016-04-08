<?php
/*
 * @author Anakeen
 * @package Dcp\Pu
*/

namespace Dcp\Pu;

require_once 'PU_testcase_dcp.php';

abstract class TestCaseDcpApplication extends TestCaseDcp
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
        /* configList can be a single app or a list of apps */
        $configList = static::appConfig();
        if (!is_array($configList) && !empty($configList)) {
            throw new \Exception(sprintf("::appConfig() did not returned an array (returned type is %s).", gettype($configList)));
        }
        /* If configList is a single app, then wrap it in an array of a single app */
        if (isset($configList['appRoot']) || isset($configList['appName'])) {
            $configList = array(
                $configList
            );
        }
        foreach ($configList as $i => $config) {
            if (!is_array($config) && !empty($config)) {
                throw new \Exception(sprintf("::appConfig() at index %d is not an array (returned type is %s).", $i, gettype($config)));
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
     * @throws \Exception
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
        
        self::$app = new \Application();
        self::$app->rootdir = $appRoot;
        self::$app->param = new \Param(self::$dbaccess);
        self::$app->parent = $myAction->parent;
        self::$app->set($appName, $myAction->parent, $myAction->parent->session, true);
        
        if (self::$app->id <= 0) {
            throw new \Exception(sprintf("Error initializing application from '%s'.", $fileDotApp));
        }
    }
    /**
     * Config of the application
     *
     * Need to have appRoot, and appName keys
     *
     * @return array
     */
    protected static function appConfig()
    {
        return array();
    }
}
