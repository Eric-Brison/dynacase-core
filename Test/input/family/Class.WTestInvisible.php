<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

include_once ("FDL/Class.WDoc.php");

Class WTestInvisible extends WDoc
{
    var $attrPrefix = "WINV";
    
    const E1 = "E1";
    const E2 = "E2";
    const E3 = "E3";
    const E4 = "E4";
    const T1 = "T1";
    var $firstState = self::E1;
    var $transitions = array(
        self::T1 => array()
    );
    
    var $cycle = array(
        array(
            "e1" => self::E1,
            "e2" => self::E2,
            "t" => self::T1
        ) ,
        
        array(
            "e1" => self::E2,
            "e2" => self::E3,
            "t" => self::T1
        ) ,
        
        array(
            "e1" => self::E3,
            "e2" => self::E4,
            "t" => self::T1
        )
    );
}
?>
