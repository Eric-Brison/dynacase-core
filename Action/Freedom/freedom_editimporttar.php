<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Edit Import Archive
 *
 * @author Anakeen
 * @version $Id: freedom_editimporttar.php,v 1.1 2004/03/16 14:12:46 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage GED
 */
/**
 */

include_once ("FDL/import_file.php");
include_once ("FDL/Lib.Dir.php");
// -----------------------------------
function freedom_editimporttar(&$action)
{
    // -----------------------------------
    // Get all the params
    $classid = GetHttpVars("classid", 7); // doc familly
    $dirid = GetHttpVars("dirid", 10); // directory to place imported doc (default unclassed folder)
    $dbaccess = $action->GetParam("FREEDOM_DB");
    
    $action->lay->set("maxsize", sprintf("max %s bytes", ini_get('upload_max_filesize')));
    
    $action->lay->Set("dirid", $dirid);
}
?>
