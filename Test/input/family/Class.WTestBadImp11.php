<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

include_once ("FDL/Class.WDoc.php");

Class WTestBadImp11 extends WDoc
{
    var $attrPrefix = "WTST";
    const alive = "alive"; #
    const dead = "dead"; #
    const transfered = "transfered"; #
    const sick = "sick"; #
    const convalescent = "convalescent"; #
    const T1 = "T1"; # N_("T1")
    const Tsick = "T sick"; # // <-- TRANSITION SYNTAX NAME ERROR
    const Tconvalescent = "Tconvalescent"; #
    const T3 = "T3";
    const T4 = "T4";
    var $firstState = self::alive;
    var $transitions = array(
        self::T1 => array() ,
        self::Tsick => array(
            "m1" => "SendMailToVeto",
            "ask" => array(
                "wan_idveto",
                "wan_veto"
            ) ,
            "nr" => true
        ) ,
        self::Tconvalescent => array(
            "m1" => "toHealthCard",
            "ask" => "wan_veto"
            // <-- TRANSITION ASK ERROR
            
        ) ,
        
        self::T3 => array(
            "m1" => "unknowM1",
            "m5" => "Z", // <-- TRANSITION PROP ERROR
            "m2" => "unknowM2"
            // <-- TRANSITION PROP ERROR
            
        ) ,
        
        self::T4 => array(
            "m0" => "unknowM0",
            "m3" => "unknowM3"
        )
    );
    
    var $cycle = array(
        array(
            "e1" => self::alive,
            "e2" => self::sick,
            "t" => self::Tsick
        ) ,
        
        array(
            "e1" => self::alive,
            "e2" => self::transfered,
            "t" => self::T1
        ) ,
        
        array(
            "e1" => self::convalescent,
            "e2" => self::dead,
            "t" => self::T1
        ) ,
        
        array(
            "e1" => self::sick,
            "e2" => self::convalescent,
            "t" => self::Tconvalescent
        ) ,
        
        array(
            "e1" => self::convalescent,
            "e2" => self::alive,
            "t" => self::T1
        ) ,
        
        array(
            "e1" => self::sick,
            "e2" => self::dead,
            "t" => "T3"
        )
    );
    
    function SendMailToVeto($newstate)
    {
    }
    
    function toHealthCard($newstate)
    {
    }
}
?>
