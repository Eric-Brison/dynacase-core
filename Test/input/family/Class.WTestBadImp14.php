<?php
/*
 * @author Anakeen
 * @package FDL
*/

include_once ("FDL/Class.WDoc.php");

Class WTestBadImp14 extends WDoc
{
    var $attrPrefix = "WTST";
    public $transitions = "must be an array";
    
    var $cycle = "must be an array";
    
    public $stateactivity = "must be an array";
}
?>
