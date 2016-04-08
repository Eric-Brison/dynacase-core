<?php
/*
 * @author Anakeen
 * @package FDL
*/

include_once ("FDL/Class.WDoc.php");

Class WTestBadImp18 extends WDoc
{
    var $attrPrefix = "W18";
    
    const e_0 = "e_0";
    const e_1 = "e_1";
    const t_0 = "t_0";
    const t_1 = "t_1";
    
    var $firstState = self::e_0;
    
    var $transitions = array(
        self::t_0,
    );
    
    var $cycle = array(
        "a scalar..."
    );
}
