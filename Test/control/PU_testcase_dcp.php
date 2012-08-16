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

$pubdir = ".";
set_include_path(get_include_path() . PATH_SEPARATOR . "$pubdir/DCPTEST:$pubdir/WHAT");
require_once 'WHAT/autoload.php';
include_once ("FDL/Class.Doc.php"); // to include some libraries
class TestCaseDcp extends \PHPUnit_Framework_TestCase
{
    /**
     * DbAccess string
     *
     * @var string
     */
    protected static $dbaccess;
    /**
     * Dbobj use for transaction
     *
     * @var \DbObj
     */
    protected static $odb;
    /**
     * User keep in cache during the sudo
     *
     * @var string
     */
    protected static $user = null;
    /**
     * Store original include_path
     */
    protected static $include_path = null;
    
    protected function setUp()
    {
        $this->connectUser("admin");
        $this->beginTransaction();
    }
    protected function tearDown()
    {
        $this->rollbackTransaction();
    }
    
    public static function setUpBeforeClass()
    {
        global $action;
    }
    /**
     * Make a begin in the db
     *
     * @return void
     */
    protected static function beginTransaction()
    {
        self::$dbaccess = getParam("FREEDOM_DB");
        if (!self::$odb) {
            self::$odb = new \DbObj(self::$dbaccess);
        }
        self::$odb->savePoint('putransaction');
        // $err = simpleQuery(self::$dbaccess, "begin", $r);
        
    }
    /**
     *  Make a rollback in the db
     *
     * @return void
     */
    protected static function rollbackTransaction()
    {
        
        self::$odb->rollbackPoint('putransaction');
        //$err = simpleQuery(self::$dbaccess, "rollback", $r);
        
    }
    /**
     * Connect as a user
     *
     * @param string $login login of the user
     *
     * @return void
     */
    protected static function connectUser($login = "admin")
    {
        global $action;
        if (!$action) {
            WhatInitialisation();
            setSystemLogin("admin");
        }
    }
    /**
     * Current action
     * @return \Action
     */
    protected static function getAction()
    {
        global $action;
        if (!$action) {
            self::connectUser();
        }
        
        if (!$action->dbid) {
            if (!$action->dbid) $action->init_dbid();
            if (!$action->dbid) error_log(__METHOD__ . "lost action dbid");
            $action->init_dbid();
        }
        return $action;
    }
    /**
     * Current application
     * @return \Application
     */
    protected function getApplication()
    {
        global $action;
        if ($action) return $action->parent;
        return null;
    }
    /**
     * return a single value from DB
     *
     * @param string $sql a query with a single fields in from part
     *
     * @return string
     */
    protected function _DBGetValue($sql)
    {
        $err = simpleQuery(self::$dbaccess, $sql, $sval, true, true);
        $this->assertEquals("", $err, sprintf("database select error", $sql));
        return $sval;
    }
    /**
     * use another user
     *
     * @param string $login
     *
     * @return void
     */
    protected function resetDocumentCache()
    {
        global $gdocs;
        $gdocs = array();
    }
    /**
     * use another user
     *
     * @param string $login
     *
     * @return \Account
     */
    protected function sudo($login)
    {
        $u = new \Account(self::$dbaccess);
        if (!$u->setLoginName($login)) {
            throw new \Dcp\Exception("login $login not exist");
        }
        
        global $action;
        self::$user = $action->user;
        $action->user = $u;
        self::resetDocumentCache();
        return $u;
    }
    /**
     * exit sudo
     *
     * @param string $login
     *
     * @return void
     */
    protected function exitSudo($login = '')
    {
        global $action;
        if (self::$user) {
            $action->user = self::$user;
            self::$user = null;
        }
    }
    /**
     * Import a file document description
     *
     * @param string $file file path
     *
     * @return void
     */
    protected static function importDocument($file)
    {
        if (is_array($file)) {
            self::importDocuments($file);
            return;
        }
        
        $cr = array();
        global $action;
        
        $realfile = $file;
        if (!file_exists($realfile)) {
            $ext = substr($file, strrpos($file, '.') + 1);
            if ($ext == "ods" || $ext == "csv") {
                $realfile = "DCPTEST/" . $file;
            } else {
                $realfile = "DCPTEST/Layout/" . $file;
            }
        }
        if (!file_exists($realfile)) {
            throw new \Dcp\Exception(sprintf("File '%s' not found in '%s'.", $file, $realfile));
        }
        $oImport = new \ImportDocument();
        //error_log(__METHOD__."import $realfile");
        $oImport->importDocuments(self::getAction() , $realfile);
        $err = $oImport->getErrorMessage();
        if ($err) throw new \Dcp\Exception($err);
        return;
    }
    /**
     * Import multiple files specified as a array list
     * @param array $fileList list of files to import
     * @return void
     */
    protected static function importDocuments($fileList)
    {
        if (!is_array($fileList)) {
            self::importDocument($fileList);
            return;
        }
        
        foreach ($fileList as $file) {
            self::importDocument($file);
        }
    }
    /**
     * Import CSV data
     * @param string $data CSV data
     * @return void
     */
    public function importCsvData($data)
    {
        $tmpFile = tempnam(getTmpDir() , "importData");
        if ($tmpFile === false) {
            throw new \Dcp\Exception(sprintf("Error creating temporary file in '%s'.", getTmpDir()));
        }
        $ret = rename($tmpFile, $tmpFile . '.csv');
        if ($ret === false) {
            throw new \Dcp\Exception(sprintf("Error renaming '%s' to '%s'.", $tmpFile, $tmpFile . '.csv'));
        }
        $tmpFile = $tmpFile . '.csv';
        $ret = file_put_contents($tmpFile, $data);
        if ($ret === false) {
            throw new \Dcp\Exception(sprintf("Error writing to file '%s'.", $tmpFile));
        }
        $this->importDocument($tmpFile);
        unlink($tmpFile);
    }
    /**
     * Set the include_path INI parameter
     * @param string $include_path the new include_path to use
     */
    public function setIncludePath($include_path)
    {
        if (self::$include_path == null) {
            self::$include_path = ini_get('include_path');
        }
        ini_set('include_path', $include_path);
    }
    /**
     * Set back the original include_path INI parameter
     */
    public function resetIncludePath()
    {
        if (self::$include_path !== null) {
            ini_set('include_path', self::$include_path);
        }
    }
}
?>