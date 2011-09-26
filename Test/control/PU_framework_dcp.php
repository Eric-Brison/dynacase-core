<?php

namespace PU;
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package DCP
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