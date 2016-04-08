<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Edit Import Archive
 *
 * @author Anakeen
 * @version $Id: freedom_editimporttar.php,v 1.1 2004/03/16 14:12:46 eric Exp $
 * @package FDL
 * @subpackage GED
 */
/**
 */

include_once ("FDL/import_file.php");
include_once ("FDL/Lib.Dir.php");
// -----------------------------------
function freedom_editimporttar(Action &$action)
{
    // -----------------------------------
    // Get all the params
    $dirid = GetHttpVars("dirid", 0); // directory to place imported doc (default unclassed folder)
    
    $action->lay->eset("maxsize", sprintf("max %s bytes", ini_get('upload_max_filesize')));
    
    $action->lay->eSet("dirid", $dirid);
}
?>
