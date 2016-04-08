<?php

namespace Dcp\Pu;
/**
 * @author Anakeen
 * @package Dcp\Pu
 */

//require_once 'PHPUnit/Framework.php';


$pubdir = ".";
set_include_path(get_include_path() . PATH_SEPARATOR . "$pubdir/DCPTEST:$pubdir/WHAT");
include_once ("FDL/Class.Doc.php");
class FrameworkDcp extends \PHPUnit_Framework_TestSuite
{
    protected function setUp()
    {
        
        global $action;

        if (!$action) {
            WhatInitialisation();
            setSystemLogin("admin");
        }
    }
    
    protected function tearDown()
    {
    
    }
    

}
?>