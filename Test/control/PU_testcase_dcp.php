<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace PU;
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package DCP
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
    protected function getAction()
    {
        global $action;
        
        if (!$action->dbid) $action->init_dbid();
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
     * @return void
     */
    protected function sudo($login)
    {
        $u = new \User(self::$dbaccess);
        if (!$u->setLoginName($login)) {
            throw new \Exception("login $login not exist");
        }
        
        global $action;
        self::$user = $action->user;
        $action->user = $u;
    }
    /**
     * exit sudo
     *
     * @param string $login
     *
     * @return void
     */
    protected function exitSudo($login)
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
        $cr = array();
        global $action;
        
        $ext = substr($file, strrpos($file, '.') + 1);
        if ($ext == "ods" || $ext == "csv") {
            $realfile = "DCPTEST/" . $file;
        } else {
            $realfile = "DCPTEST/Layout/" . $file;
        }
        $oImport = new \ImportDocument();
        //error_log(__METHOD__."import $realfile");
        if (!$action->dbid) $action->init_dbid();
        if (!$action->dbid) error_log(__METHOD__ . "lost action dbid");
        $oImport->importDocuments($action, $realfile);
        $err=$oImport->getErrorMessage();
        if ($err) throw new \Exception($err);
        return;
    }
}
?>