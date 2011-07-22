<?php

/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */

require_once 'PHPUnit/Framework.php';
 
$pubdir="/usr/share/what";
set_include_path(get_include_path() . PATH_SEPARATOR . "$pubdir:$pubdir/WHAT");
include_once("FDL/Class.Doc.php");
class PHPUnit_Framework_TestSuiteDocument  extends PHPUnit_Framework_TestSuite
{
    protected function setUp(){
      
      global $action;
      if (! $action  ) {
	WhatInitialisation();
	setSystemLogin("admin");
      }
    }
    
 
    protected function tearDown()
    {
      
    }
}
?>