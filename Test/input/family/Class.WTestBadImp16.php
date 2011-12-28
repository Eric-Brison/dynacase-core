<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

include_once ("FDL/Class.WDoc.php");

Class WTestBadImp16 extends WDoc
{
    var $attrPrefix = "WTST";
    const alive = "alive"; # N_("alive")
    const dead = "dead"; # N_("dead")
    const transfered = "transfered"; # N_("transfered")
    const sick = "sick"; # N_("sick")
    const convalescent = "convalescent"; # N_("convalescent")
    const T1 = "T1"; # N_("T1")
    const Tsick = "T sick"; # N_("Tsick")// <-- TRANSITION SYNTAX NAME ERROR
    const Tconvalescent = "Tconvalescent"; # N_("Tconvalescent")
    const T3 = "T3"; # N_("T3")
    var $firstState = self::alive;
    var $transitions = array(
        "T01" => array() ,
        "T02" => array() ,
        "T03" => array() ,
        "T04" => array() ,
        "T05" => array() ,
        "T06" => array() ,
        "T07" => array() ,
        "T08" => array() ,
        "T09" => array() ,
        "T10" => array() ,
        "T11" => array() ,
        "T12" => array() ,
        "T13" => array() ,
        "T14" => array() ,
        "T15" => array() ,
        "T16" => array() ,
        "T17" => array() ,
        "T18" => array() ,
        "T19" => array() ,
        "T20" => array() ,
        "T21" => array()
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
}
?>
