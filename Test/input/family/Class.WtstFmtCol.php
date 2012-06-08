<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

include_once ("FDL/Class.WDoc.php");

Class WtstFmtCol extends WDoc
{
    public $attrPrefix = "WAN";
    
    const E1 = "E1";
    const E2 = "E2";
    const E3 = "E3";
    const T1 = "T1";
    public $firstState = self::E1;
    public $transitions = array(
        self::T1 => array()
    );
    
    public $cycle = array(
        array(
            "e1" => self::E1,
            "e2" => self::E2,
            "t" => self::T1
        ) ,
        array(
            "e1" => self::E2,
            "e2" => self::E3,
            "t" => self::T1
        )
    );
    public $stateactivity = array(
        self::E1 => "Activity E1",
        self::E2 => "Activity E2"
    );
}
?>
