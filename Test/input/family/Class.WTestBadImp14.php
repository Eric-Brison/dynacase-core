<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
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
