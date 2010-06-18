<?php

/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 */

require_once 'PHPUnit/Framework.php';

$pubdir="/usr/share/what";
set_include_path(get_include_path() . PATH_SEPARATOR . "$pubdir:$pubdir/WHAT");
include_once("FDL/Class.Doc.php");

class PHPUnit_Framework_TestDocument extends PHPUnit_Framework_TestCase {
	protected function setUp()    {
		global $action;
		if (! $action) {
			WhatInitialisation();
			setSystemLogin("admin");
		}
		$this->dbaccess=$action->getParam("FREEDOM_DB");
		$err=simpleQuery($this->dbaccess,"begin",$r);
			
	}
	public static function setUpBeforeClass()
	{
		global $action;
		// print __METHOD__ . $action->getParam("FREEDOM_DB")."\n";
			
		//$err=simpleQuery($action->getParam("FREEDOM_DB"),"begin",$r);
	}
	/**
	 * return a single value from DB
	 * @param string $sql a query with a single fields in from part
	 * @return string
	 */
	protected function _DBGetValue($sql) {
		$err=simpleQuery($this->dbaccess,$sql,$sval,true,true);
		$this->assertEquals("",$err,sprintf("database select error",$sql));
		return $sval;
	}

	protected function assertPreConditions()
	{
		// print __METHOD__ . "\n";
	}





	protected function assertPostConditions()
	{
		// print __METHOD__ . "\n";
	}

	protected function tearDown()
	{

		$err=simpleQuery($this->dbaccess,"rollback",$r);
		// print __METHOD__ . "\n";
	}

	public static function tearDownAfterClass()
	{
		global $action;
		//    print __METHOD__ . "\n";
		//$err=simpleQuery($action->getParam("FREEDOM_DB"),"rollback",$r);
	}

	protected function onNotSuccessfulTest(Exception $e)
	{
		//  print __METHOD__ . "\n";
		throw $e;
	}

}
?>