<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

include_once ("FDL/Class.WDoc.php");

Class WTestExtend extends WDoc
{
    var $attrPrefix = "WAN";
    
    const N1 = "N1";
    const N2 = "N2";
    const SA = "SA";
    const SB = "SB";
    const SC = "SC";
    const SD = "SD";
    const SE = "SE";
    const SF = "SF";
    const SG = "SG";
    const SH = "SH";
    const SI = "SI";
    const SJ = "SJ";
    const SK = "SK";
    const SL = "SL";
    const SM = "SM";
    const SN = "SN";
    const SO = "SO";
    const SP = "SP";
    const SQ = "SQ";
    const SR = "SR";
    const SS = "SS";
    const ST = "ST";
    const SU = "SU";
    const SV = "SV";
    const SW = "SW";
    const SX = "SX";
    const SY = "SY";
    const SZ = "SZ";
    
    const T1 = "T1";
    const T2 = "T2";
    const T3 = "T3";
    const T4 = "T4";
    const T5 = "T5";
    const T6 = "T6";
    const T7 = "T7";
    const T8 = "T8";
    const T9 = "T9";
    const T10 = "T10";
    const T11 = "T11";
    const T12 = "T12";
    const T13 = "T13";
    const T14 = "T14";
    const T15 = "T15";
    const T16 = "T16";
    const T17 = "T17";
    const T18 = "T18";
    const T19 = "T19";
    const T20 = "T20";
    const T21 = "T21";
    const T22 = "T22";
    const T23 = "T23";
    const T24 = "T24";
    const T25 = "T25";
    const T26 = "T26";
    const T27 = "T27";
    const T28 = "T28";
    const T29 = "T29";
    const T30 = "T30";
    const T31 = "T31";
    const T32 = "T32";
    const T33 = "T33";
    const T34 = "T34";
    const T35 = "T35";
    const T36 = "T36";
    const T37 = "T37";
    const T38 = "T38";
    const T39 = "T39";
    var $firstState = self::SA;
    var $transitions = array(
        self::T1 => array(
            
            "m0" => "t5m0",
            "m1" => "t5m1",
            "m2" => "t5m2",
            "m3" => "t5m3"
        ) ,
        self::T2 => array(
            
            "m0" => "t5m0",
            "m1" => "t5m1",
            "m2" => "t5m2",
            "m3" => "t5m3"
        ) ,
        self::T3 => array(
            
            "m0" => "t5m0",
            "m1" => "t5m1",
            "m2" => "t5m2",
            "m3" => "t5m3"
        ) ,
        self::T4 => array(
            
            "m0" => "t5m0",
            "m1" => "t5m1",
            "m2" => "t5m2",
            "m3" => "t5m3"
        ) ,
        self::T5 => array(
            "m0" => "t5m0",
            "m1" => "t5m1",
            "m2" => "t5m2",
            "m3" => "t5m3"
        ) ,
        self::T6 => array(
            "m0" => "t5m0",
            "m1" => "t5m1",
            "m2" => "t5m2",
            "m3" => "t5m3"
        ) ,
        self::T7 => array(
            "m0" => "t5m0",
            "m1" => "t5m1",
            "m2" => "t5m2",
            "m3" => "t5m3"
        ) ,
        self::T8 => array(
            "m0" => "t5m0",
            "m1" => "t5m1",
            "m2" => "t5m2",
            "m3" => "t5m3"
        ) ,
        self::T9 => array(
            "m0" => "t5m0",
            "m1" => "t5m1",
            "m2" => "t5m2",
            "m3" => "t5m3"
        ) ,
        self::T10 => array(
            "m0" => "t5m0",
            "m1" => "t5m1",
            "m2" => "t5m2",
            "m3" => "t5m3"
        ) ,
        self::T11 => array(
            "m0" => "t5m0",
            "m1" => "t5m1",
            "m2" => "t5m2",
            "m3" => "t5m3"
        ) ,
        self::T12 => array(
            "m0" => "t5m0",
            "m1" => "t5m1",
            "m2" => "t5m2",
            "m3" => "t5m3"
        ) ,
        self::T13 => array(
            "m0" => "t5m0",
            "m1" => "t5m1",
            "m2" => "t5m2",
            "m3" => "t5m3"
        ) ,
        self::T14 => array(
            "m0" => "t5m0",
            "m1" => "t5m1",
            "m2" => "t5m2",
            "m3" => "t5m3"
        ) ,
        self::T15 => array(
            "m0" => "t5m0",
            "m1" => "t5m1",
            "m2" => "t5m2",
            "m3" => "t5m3"
        ) ,
        self::T16 => array(
            "m0" => "t5m0",
            "m1" => "t5m1",
            "m2" => "t5m2",
            "m3" => "t5m3"
        ) ,
        self::T17 => array(
            "m0" => "t5m0",
            "m1" => "t5m1",
            "m2" => "t5m2",
            "m3" => "t5m3"
        ) ,
        self::T18 => array(
            "m0" => "t5m0",
            "m1" => "t5m1",
            "m2" => "t5m2",
            "m3" => "t5m3"
        ) ,
        self::T19 => array(
            "m0" => "t5m0",
            "m1" => "t5m1",
            "m2" => "t5m2",
            "m3" => "t5m3"
        ) ,
        self::T20 => array(
            "m0" => "t5m0",
            "m1" => "t5m1",
            "m2" => "t5m2",
            "m3" => "t5m3"
        ) ,
        self::T21 => array(
            "m0" => "t5m0",
            "m1" => "t5m1",
            "m2" => "t5m2",
            "m3" => "t5m3"
        ) ,
        self::T22 => array(
            "m0" => "t5m0",
            "m1" => "t5m1",
            "m2" => "t5m2",
            "m3" => "t5m3"
        ) ,
        self::T23 => array(
            "m0" => "t5m0",
            "m1" => "t5m1",
            "m2" => "t5m2",
            "m3" => "t5m3"
        ) ,
        self::T24 => array(
            "m0" => "t5m0",
            "m1" => "t5m1",
            "m2" => "t5m2",
            "m3" => "t5m3"
        ) ,
        self::T25 => array(
            "m0" => "t5m0",
            "m1" => "t5m1",
            "m2" => "t5m2",
            "m3" => "t5m3"
        ) ,
        self::T26 => array(
            "m0" => "t5m0",
            "m1" => "t5m1",
            "m2" => "t5m2",
            "m3" => "t5m3"
        ) ,
        self::T27 => array(
            "m0" => "t5m0",
            "m1" => "t5m1",
            "m2" => "t5m2",
            "m3" => "t5m3"
        ) ,
        self::T28 => array(
            "m0" => "t5m0",
            "m1" => "t5m1",
            "m2" => "t5m2",
            "m3" => "t5m3"
        ) ,
        self::T29 => array(
            "m0" => "t5m0",
            "m1" => "t5m1",
            "m2" => "t5m2",
            "m3" => "t5m3"
        ) ,
        self::T30 => array(
            "m0" => "t5m0",
            "m1" => "t5m1",
            "m2" => "t5m2",
            "m3" => "t5m3"
        ) ,
        self::T31 => array(
            "m0" => "t5m0",
            "m1" => "t5m1",
            "m2" => "t5m2",
            "m3" => "t5m3"
        ) ,
        self::T32 => array(
            "m0" => "t5m0",
            "m1" => "t5m1",
            "m2" => "t5m2",
            "m3" => "t5m3"
        ) ,
        self::T33 => array(
            "m0" => "t5m0",
            "m1" => "t5m1",
            "m2" => "t5m2",
            "m3" => "t5m3"
        ) ,
        self::T34 => array(
            "m0" => "t5m0",
            "m1" => "t5m1",
            "m2" => "t5m2",
            "m3" => "t5m3"
        ) ,
        self::T35 => array(
            "m0" => "t5m0",
            "m1" => "t5m1",
            "m2" => "t5m2",
            "m3" => "t5m3"
        ) ,
        self::T36 => array(
            "m0" => "t5m0",
            "m1" => "t5m1",
            "m2" => "t5m2",
            "m3" => "t5m3"
        ) ,
        self::T37 => array(
            "m0" => "t5m0",
            "m1" => "t5m1",
            "m2" => "t5m2",
            "m3" => "t5m3"
        ) ,
        self::T38 => array(
            "m0" => "t5m0",
            "m1" => "t5m1",
            "m2" => "t5m2",
            "m3" => "t5m3"
        ) ,
        self::T39 => array(
            "m0" => "t5m0",
            "m1" => "t5m1",
            "m2" => "t5m2",
            "m3" => "t5m3"
        )
    );
    
    var $cycle = array(
        array(
            "e1" => self::N1,
            "e2" => self::SA,
            "t" => self::T1
        ) ,
        array(
            "e1" => self::N1,
            "e2" => self::SB,
            "t" => self::T2
        ) ,
        array(
            "e1" => self::N1,
            "e2" => self::SC,
            "t" => self::T3
        ) ,
        array(
            "e1" => self::N1,
            "e2" => self::SD,
            "t" => self::T4
        ) ,
        array(
            "e1" => self::N1,
            "e2" => self::SE,
            "t" => self::T5
        ) ,
        array(
            "e1" => self::N1,
            "e2" => self::SF,
            "t" => self::T6
        ) ,
        array(
            "e1" => self::N1,
            "e2" => self::SG,
            "t" => self::T7
        ) ,
        array(
            "e1" => self::N1,
            "e2" => self::SH,
            "t" => self::T8
        ) ,
        array(
            "e1" => self::SA,
            "e2" => self::SB,
            "t" => self::T9
        ) ,
        array(
            "e1" => self::SB,
            "e2" => self::SC,
            "t" => self::T10
        ) ,
        array(
            "e1" => self::SC,
            "e2" => self::SD,
            "t" => self::T11
        ) ,
        array(
            "e1" => self::SD,
            "e2" => self::SE,
            "t" => self::T12
        ) ,
        array(
            "e1" => self::SE,
            "e2" => self::SF,
            "t" => self::T13
        ) ,
        array(
            "e1" => self::SF,
            "e2" => self::SG,
            "t" => self::T14
        ) ,
        array(
            "e1" => self::SG,
            "e2" => self::SH,
            "t" => self::T15
        ) ,
        array(
            "e1" => self::SH,
            "e2" => self::SA,
            "t" => self::T16
        ) ,
        
        array(
            "e1" => self::N1,
            "e2" => self::N2,
            "t" => self::T17
        ) ,
        array(
            "e1" => self::N2,
            "e2" => self::N1,
            "t" => self::T18
        ) ,
        
        array(
            "e1" => self::N2,
            "e2" => self::SI,
            "t" => self::T19
        ) ,
        array(
            "e1" => self::N2,
            "e2" => self::SJ,
            "t" => self::T20
        ) ,
        array(
            "e1" => self::N2,
            "e2" => self::SK,
            "t" => self::T21
        ) ,
        array(
            "e1" => self::N2,
            "e2" => self::SL,
            "t" => self::T22
        ) ,
        array(
            "e1" => self::N2,
            "e2" => self::SM,
            "t" => self::T23
        ) ,
        array(
            "e1" => self::N2,
            "e2" => self::SN,
            "t" => self::T24
        ) ,
        array(
            "e1" => self::N2,
            "e2" => self::SO,
            "t" => self::T25
        ) ,
        array(
            "e1" => self::N2,
            "e2" => self::SP,
            "t" => self::T26
        ) ,
        array(
            "e1" => self::SI,
            "e2" => self::SJ,
            "t" => self::T27
        ) ,
        array(
            "e1" => self::SJ,
            "e2" => self::SK,
            "t" => self::T28
        ) ,
        array(
            "e1" => self::SK,
            "e2" => self::SL,
            "t" => self::T29
        ) ,
        array(
            "e1" => self::SL,
            "e2" => self::SM,
            "t" => self::T30
        ) ,
        array(
            "e1" => self::SM,
            "e2" => self::SN,
            "t" => self::T31
        ) ,
        array(
            "e1" => self::SN,
            "e2" => self::SO,
            "t" => self::T32
        ) ,
        array(
            "e1" => self::SO,
            "e2" => self::SP,
            "t" => self::T33
        ) ,
        array(
            "e1" => self::SP,
            "e2" => self::SI,
            "t" => self::T34
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
