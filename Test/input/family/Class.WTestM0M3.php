<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

include_once ("FDL/Class.WDoc.php");

Class WTestM0M3 extends WDoc
{
    var $attrPrefix = "WAN";
    
    const SA = "SA";
    const SB = "SB";
    const SC = "SC";
    const SD = "SD";
    const T1 = "T1";
    const T2 = "T2";
    const T3 = "T3";
    const T4 = "T4";
    const T5 = "T5";
    var $firstState = self::SA;
    var $transitions = array(
        self::T1 => array(
            "m0" => "t1m0",
        ) ,
        self::T2 => array(
            "m1" => "t2m1",
            "nr" => true
        ) ,
        self::T3 => array(
            "m3" => "t3m3"
        ) ,
        self::T4 => array(
            "m0" => "t4m0",
            "m1" => "t4m1"
        ) ,
        self::T5 => array(
            "m0" => "t5m0",
            "m1" => "t5m1",
            "m2" => "t5m2",
            "m3" => "t5m3"
        )
    );
    
    var $cycle = array(
        array(
            "e1" => self::SA,
            "e2" => self::SB,
            "t" => self::T1
        ) ,
        
        array(
            "e1" => self::SA,
            "e2" => self::SC,
            "t" => self::T2
        ) ,
        
        array(
            "e1" => self::SA,
            "e2" => self::SD,
            "t" => self::T3
        ) ,
        
        array(
            "e1" => self::SB,
            "e2" => self::SC,
            "t" => self::T4
        ) ,
        array(
            "e1" => self::SB,
            "e2" => self::SD,
            "t" => self::T5
        )
    );
    
    function t2m1($newstate)
    {
        return '';
    }
    
    function t2m0($newstate)
    {
        return '';
    }
    function t1m0($newstate)
    {
        return "m0 forbidden";
    }
    function t3m3($newstate)
    {
        return "m3 pass";
    }
    function t4m0($newstate)
    {
        return "";
    }
    function t4m1($newstate)
    {
        return "m1 forbidden";
    }
    function t5m0($newstate)
    {
        return "";
    }
    function t5m1($newstate)
    {
        return "";
    }
    function t5m2($newstate)
    {
        return "T5 m2 pass";
    }
    function t5m3($newstate)
    {
        return "T5 m3 pass";
    }
}
?>
