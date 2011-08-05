<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Import documents description with the file FDL/init.freedom
 *
 * @author Anakeen 2000
 * @version $Id: freedom_init.php,v 1.2 2003/08/18 15:47:03 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/import_file.php");
// -----------------------------------
function freedom_init(&$action)
{
    // -----------------------------------
    add_import_file($action, $action->GetParam("CORE_PUBDIR") . "/FDL/init.freedom");
}
?>
