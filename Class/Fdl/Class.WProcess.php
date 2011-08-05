<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Process Workflow
 *
 * @author Anakeen 2002
 * @version \$Id: Class.WProcess.php,v 1.2 2006/04/03 14:56:26 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 */
include_once ("FDL/Class.WDoc.php");

define("wprocess_private", "wprocess_private"); # N_("wprocess_private")
define("wprocess_public", "wprocess_public"); # N_("wprocess_qualified")
define("Twprocess_private", "Twprocess_private"); # N_("Twprocess_private")
define("Twprocess_public", "Twprocess_public"); # N_("Twprocess_public")

/**
 * Process Workflow
 *
 */
class WProcess extends WDoc
{
    
    var $attrPrefix = "PROWF"; // prefix attribute
    var $firstState = wprocess_private;
    
    var $transitions = array(
        "Twprocess_private",
        "Twprocess_public"
    );
    
    var $cycle = array(
        array(
            "e1" => wprocess_private,
            "e2" => wprocess_public,
            "t" => Twprocess_public
        ) ,
        
        array(
            "e1" => wprocess_public,
            "e2" => wprocess_private,
            "t" => Twprocess_private
        )
    );
}
?>
