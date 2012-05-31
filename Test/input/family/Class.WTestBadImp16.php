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
    const alive = "alive";
    const dead = "dead";
    const transfered = "transfered";
    const sick = "sick";
    const test = "test";
    const convalescent = "convalescent"; #
    const T1 = "T1"; #
    const Tsick = "Tsick";
    const Tconvalescent = "Tconvalescent"; #
    const T3 = "T3"; #
    const T4 = "T4"; #
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
    function __construct($dbaccess = '', $id = '', $res = '', $dbid = 0)
       {
           for ($i = 40; $i < 400; $i++) {
               $this->transitions["T$i"] = array();
           }
           parent::__construct($dbaccess, $id, $res, $dbid);
       }
}
?>
